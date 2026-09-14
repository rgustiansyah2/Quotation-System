let isFormDirty = false;
let currentMmpDetails = [];
let currentTonnageOptions = [];

function fetchTonnageOptions(draftId, callback) {
    if (!draftId || draftId === "0") {
        currentTonnageOptions = [];
        updateAllTonnageSelects();
        if (typeof callback === 'function') callback();
        return;
    }

    $.ajax({
        url: 'get_tonnage_options.php',
        type: 'GET',
        data: { draft_id: draftId },
        dataType: 'json',
        success: function(data) {
            currentTonnageOptions = data || [];
            updateAllTonnageSelects();
            if (typeof callback === 'function') callback();
        },
        error: function() {
            console.error("Gagal mengambil data tonase mesin.");
            currentTonnageOptions = [];
            updateAllTonnageSelects();
        }
    });
}

function populateTonnageOptions(selectElement, selectedValue = "") {
    selectElement.innerHTML = '<option value="">- Pilih MC Ton -</option>';
    currentTonnageOptions.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.mc_tonnage;
        opt.textContent = item.mc_label || (item.mc_tonnage + ' Ton');
        
        if (selectedValue && String(selectedValue) === String(item.mc_tonnage)) {
            opt.selected = true;
        }
        selectElement.appendChild(opt);
    });
}

function updateAllTonnageSelects() {
    document.querySelectorAll('select[name="mc_ton"]').forEach(select => {
        const currentVal = select.value || select.getAttribute('data-saved-val') || "";
        populateTonnageOptions(select, currentVal);
    });
}

function refreshQuotationRateDrafts() {
    fetch('get_rate_drafts_ajax.php', { cache: 'no-store' })
        .then(response => response.json())
        .then(result => {
            if (!result.success || !Array.isArray(result.data)) return;

            window.allDraftsData = {};
            result.data.forEach(draft => {
                window.allDraftsData[draft.id] = draft;
            });

            const draftSelect = document.getElementById('rate_draft_id');
            if (!draftSelect) return;

            const selectedValue = draftSelect.value;
            draftSelect.querySelectorAll('option:not(:first-child)').forEach(option => option.remove());
            result.data.forEach(draft => {
                const option = document.createElement('option');
                option.value = draft.id;
                option.textContent = draft.draft_title;
                option.dataset.ref = draft.base_reference || '';
                option.dataset.mp = draft.manpower_rate_sec || 0;
                option.selected = String(draft.id) === String(selectedValue);
                draftSelect.appendChild(option);
            });

            if (selectedValue && result.data.some(draft => String(draft.id) === String(selectedValue))) {
                draftSelect.value = selectedValue;
                updateAllProcessRates();
            } else if (selectedValue) {
                draftSelect.value = '';
                updateAllProcessRates();
            }
        })
        .catch(error => console.error('Gagal memperbarui data rate:', error));
}

const quotationRateChannel = new BroadcastChannel('rate_matrix_update');
quotationRateChannel.onmessage = (event) => {
    const payload = event.data || {};
    if (payload.event === 'new_rate_draft_created' || payload.event === 'rate_matrix_updated') {
        refreshQuotationRateDrafts();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const initialCrMode = window.savedCrMode || "<?= $savedCrMode ?? 'with_bl' ?>";

    if (initialCrMode && initialCrMode !== 'none') {
        selectCrMode(initialCrMode);
    } else {
        selectCrMode('with_bl');
    }

    const reminderModal = document.getElementById('quotationReminderModal');
    const closeReminderBtn = document.getElementById('closeQuotationReminder');
    const confirmReminderBtn = document.getElementById('confirmQuotationReminder');

    const showReminderModal = () => {
        if (!reminderModal) return;
        reminderModal.classList.remove('hidden-element');
        reminderModal.setAttribute('aria-hidden', 'false');
    };

    if (closeReminderBtn) {
        closeReminderBtn.addEventListener('click', () => {
            reminderModal?.classList.add('hidden-element');
            reminderModal?.setAttribute('aria-hidden', 'true');
        });
    }

    if (confirmReminderBtn) {
        confirmReminderBtn.addEventListener('click', () => {
            reminderModal?.classList.add('hidden-element');
            reminderModal?.setAttribute('aria-hidden', 'true');
        });
    }

    const quotationReminderKey = 'quotation_reminder_seen';
    if (sessionStorage.getItem(quotationReminderKey) !== '1') {
        showReminderModal();
        sessionStorage.setItem(quotationReminderKey, '1');
    }

    window.addEventListener('pagehide', () => {
        const saveFlag = document.getElementById('saveQuotationFlag');
        if (!saveFlag || saveFlag.value !== '1') {
            sessionStorage.removeItem(quotationReminderKey);
        }
    });

    document.querySelectorAll('input, select, textarea').forEach(element => {
        element.addEventListener('change', () => { isFormDirty = true; });
    });

    const tbody = document.getElementById('calcRows');
    const notesTextarea = document.getElementById('quotation_notes');
    
    const initialMmp = document.getElementById('acuan_mmp')?.value;
    if (initialMmp) {
        onAcuanMmpChange(initialMmp);
    }

    if (typeof existingRows !== 'undefined' && existingRows && existingRows.length > 0) {
        if (tbody) tbody.innerHTML = "";
        existingRows.forEach(row => appendSavedRow(row));
    } else {
        addRow();
    }
    
    updateCounts();

    const notePeriode = document.getElementById('note_input_periode');
    const spanRate = document.getElementById('note_span_rate');
    const noteQty = document.getElementById('note_input_qty');
    const noteDay = document.getElementById('note_input_day');
    const notePack1 = document.getElementById('note_input_pack1');
    const notePack2 = document.getElementById('note_input_pack2');
    const noteMasspro = document.getElementById('note_input_masspro');
    const dynamicNotesContainer = document.getElementById('dynamicNotesContainer');
    const addNoteButton = document.getElementById('addNoteButton');
    
    $('#rate_draft_id').on('change', function() {
        const draftId = $(this).val();
        fetchTonnageOptions(draftId, function() {
            updateAllProcessRates();
        });
    });

    const initialDraftId = $('#rate_draft_id').val();
    if (initialDraftId) {
        fetchTonnageOptions(initialDraftId);
    }

    function createDynamicNoteRow(number, value = '') {
        const row = document.createElement('div');
        row.className = 'note-row note-extra-row';
        row.innerHTML = `
            <span class="note-number">${number}.</span>
            <input type="text" class="quote-input note-extra-input" data-note-no="${number}" value="${value.replace(/"/g, '&quot;')}" placeholder="Tambahkan catatan...">
        `;
        return row;
    }

    function buildDynamicNotesFromInputs() {
        const dynamicRows = [];
        if (dynamicNotesContainer) {
            dynamicNotesContainer.querySelectorAll('.note-extra-input').forEach(input => {
                const raw = (input.value || '').trim();
                if (raw !== '') {
                    dynamicRows.push({
                        number: Number(input.dataset.noteNo || 10),
                        value: raw
                    });
                }
            });
        }
        return dynamicRows;
    }

    function compileNotesToTextarea() {
        if (!notesTextarea) return;
        const valPeriode = notePeriode && notePeriode.value.trim() !== "" ? notePeriode.value : '[Isi Periode]';
        const valRate = spanRate && spanRate.textContent !== ".........." ? spanRate.textContent : '..........';
        const valQty = noteQty && noteQty.value.trim() !== "" ? noteQty.value : '[Isi Qty Manual]';
        const valDay = noteDay && noteDay.value.trim() !== "" ? noteDay.value : '[Isi Hari]';
        const valPack1 = notePack1 && notePack1.value.trim() !== "" ? notePack1.value : '[Isi Return]';
        const valPack2 = notePack2 && notePack2.value.trim() !== "" ? notePack2.value : '[Isi Non-return]';
        const valMasspro = noteMasspro && noteMasspro.value.trim() !== "" ? noteMasspro.value : '[Isi Tahun]';

        const baseNotes = [
            `1. Model.`,
            `2. Exchange Rate periode ${valPeriode} Rp. ${valRate} / USD.`,
            `3. Harga belum termasuk PPN 11%, biaya pengetesan, jig, checking fixture, dan biaya finishing (jika ada).`,
            `4. Qty forecast: ${valQty}.`,
            `5. Pembayaran ${valDay} hari setelah penerimaan invoice.`,
            `6. Berat part, cycle time, cavity & tonase mesin akan diperbaharui kembali setelah hasil trial dinyatakan OK.`,
            `7. Packing menggunakan (${valPack1}) returnable dan (${valPack2}) non-returnable.`,
            `8. Validasi penawaran harga: 30 hari.`,
            `9. Tahun Masspro ${valMasspro};`
        ];

        const extraNotes = buildDynamicNotesFromInputs();
        const allNotes = [...baseNotes];
        extraNotes.forEach(item => {
            allNotes.push(`${item.number}. ${item.value}`);
        });

        notesTextarea.value = allNotes.join('\n');
    }

    function syncTabelKeNote() {
        const firstRateInput = document.querySelector('#calcRows tr:first-child [name="exchange_rate"]');
        if (spanRate && firstRateInput && firstRateInput.value.trim() !== "") {
            spanRate.textContent = Number(firstRateInput.value).toLocaleString('id-ID');
        } else if (spanRate) {
            spanRate.textContent = '..........';
        }
        compileNotesToTextarea();
    }

    function addDynamicNoteRow() {
        if (!dynamicNotesContainer) return;
        const existing = dynamicNotesContainer.querySelectorAll('.note-extra-input');
        const lastNo = existing.length ? Math.max(...Array.from(existing).map(el => Number(el.dataset.noteNo || 10))) : 9;
        const nextNo = lastNo + 1;
        dynamicNotesContainer.appendChild(createDynamicNoteRow(nextNo));
        compileNotesToTextarea();

        const newInput = dynamicNotesContainer.querySelector(`.note-extra-input[data-note-no="${nextNo}"]`);
        if (newInput) {
            newInput.focus();
        }
    }

    if (addNoteButton) {
        addNoteButton.addEventListener('click', function() {
            addDynamicNoteRow();
        });
    }

    if (dynamicNotesContainer) {
        dynamicNotesContainer.addEventListener('keydown', function(e) {
            const input = e.target;
            if (input && input.classList.contains('note-extra-input') && e.key === 'Enter') {
                e.preventDefault();
                const currentNo = Number(input.dataset.noteNo || 10);
                const value = (input.value || '').trim();
                if (value !== '') {
                    input.setAttribute('value', value);
                }
                addDynamicNoteRow();
                const nextInput = dynamicNotesContainer.querySelector(`.note-extra-input[data-note-no="${currentNo + 1}"]`);
                if (nextInput) {
                    nextInput.focus();
                }
            }
        });

        dynamicNotesContainer.addEventListener('input', function(e) {
            const input = e.target;
            if (input && input.classList.contains('note-extra-input')) {
                compileNotesToTextarea();
            }
        });
    }

    setTimeout(syncTabelKeNote, 500);

    [notePeriode, noteQty, noteDay, notePack1, notePack2, noteMasspro].forEach(input => {
        if(input) input.addEventListener('input', compileNotesToTextarea);
    });

    document.getElementById('calcRows')?.addEventListener('input', function(e) {
        const target = e.target;
        if (target.matches('tr:first-child [name="exchange_rate"]')) {
            if (spanRate) {
                spanRate.textContent = target.value.trim() !== "" 
                    ? Number(target.value).toLocaleString('id-ID') 
                    : '..........';
            }
            compileNotesToTextarea();
        }
    });
});

function toggleCrPopover(event) {
    if (event) event.stopPropagation();
    const popover = document.getElementById('crPopover');
    if (!popover) return;
    popover.style.display = (popover.style.display === 'none' || popover.style.display === '') ? 'flex' : 'none';
}

document.addEventListener('click', function(e) {
    const popover = document.getElementById('crPopover');
    const container = document.querySelector('.cr-btn-container');
    if (popover && container && !container.contains(e.target)) {
        popover.style.display = 'none';
    }
});

function onLtaYearsChange(years) {
    const ltaYears = parseInt(years) || 3;
    const ltaInput = document.getElementById('ltaYearsCount');
    if (ltaInput) ltaInput.value = ltaYears;
    
    renderLtaHeadersAndCells(ltaYears);
    recalculateAllRows();
}

function renderLtaHeadersAndCells(yearsCount) {
    const years = parseInt(yearsCount) || 3;
    const headersMarker = document.getElementById('ltaHeadersMarker');
    
    if (headersMarker) {
        document.querySelectorAll('.dynamic-lta-header').forEach(el => el.remove());
        let headersHtml = '';
        for (let i = 1; i <= years; i++) {
            headersHtml += `
                <th class="cr-col cr-wob-only col-sm col-cr-header dynamic-lta-header">% Thn ${i}</th>
                <th class="cr-col cr-wob-only col-md col-cr-header dynamic-lta-header">LTA Thn ${i}</th>
            `;
        }
        headersMarker.insertAdjacentHTML('beforebegin', headersHtml);
    }

    document.querySelectorAll('#calcRows tr').forEach(row => {
        const cellsMarker = row.querySelector('.lta-cells-marker');
        if (cellsMarker) {
            row.querySelectorAll('.dynamic-lta-cell').forEach(el => el.remove());
            let cellsHtml = '';
                for (let i = 1; i <= years; i++) {
                    cellsHtml += `
                        <td class="cr-col cr-wob-only col-sm dynamic-lta-cell">
                            <input class="quote-input input-num input-cr-lta-pct" type="number" step="0.01" data-year="${i}" value="0" style="font-weight:600;" placeholder="%" oninput="calculateRow(this.closest('tr'), this)">
                        </td>
                        <td class="cr-col cr-wob-only col-md dynamic-lta-cell">
                            <input class="quote-input input-num input-cr-lta-res" type="number" step="0.01" data-year="${i}" placeholder="Nilai LTA" readonly style="background:#f1f5f9;">
                        </td>
                    `;
                }
            cellsMarker.insertAdjacentHTML('afterend', cellsHtml);
        }
    });

    const activeMode = typeof getCurrentCrMode === 'function' ? getCurrentCrMode() : 'none';
    selectCrMode(activeMode);
}

function selectCrMode(mode) {
    const crLabel = document.getElementById('crStatusLabel');
    const popover = document.getElementById('crPopover');
    const crButton = document.getElementById('btnCrAllowance'); 
    const globalCrMode = document.getElementById('globalCrMode');
    
    if (globalCrMode) globalCrMode.value = mode;
    if (popover) popover.style.display = 'none';

    const popItems = document.querySelectorAll('.cr-pop-item');
    popItems.forEach(item => item.classList.remove('active'));

    if (mode === 'with_bl') {
        document.querySelector('.cr-pop-item[onclick*="with_bl"]')?.classList.add('active');
    } else if (mode === 'without_bl') {
        document.querySelector('.cr-pop-item[onclick*="without_bl"]')?.classList.add('active');
    } else {
        document.querySelector('.cr-pop-item.cr-pop-off')?.classList.add('active');
    }

    if (crButton) {
        crButton.classList.remove('cr-is-off', 'cr-is-active');
        if (mode === 'none' || !mode) {
            crButton.classList.add('cr-is-off');    
        } else {
            crButton.classList.add('cr-is-active'); 
        }
    }

    const allCrCols = document.querySelectorAll('.cr-col');
    const blCols = document.querySelectorAll('.cr-bl-only');
    const wobCols = document.querySelectorAll('.cr-wob-only');

    if (mode === 'none' || !mode) {
        if (crLabel) crLabel.textContent = 'Off';
        allCrCols.forEach(col => col.style.display = 'none');
    } else {
        allCrCols.forEach(col => col.style.display = '');

        if (mode === 'with_bl') {
            if (crLabel) crLabel.textContent = 'With BL';
            wobCols.forEach(col => col.style.display = '');
            blCols.forEach(col => col.style.display = '');
        } else if (mode === 'without_bl') {
            if (crLabel) crLabel.textContent = 'Without BL';
            wobCols.forEach(col => col.style.display = '');
            blCols.forEach(col => col.style.display = 'none');
        }

        document.querySelectorAll('#calcRows tr').forEach(row => {
            const baseInput = row.querySelector('[name="cr_base_val"]');
            const totalVal = parseFloat(row.querySelector('[name="total"]')?.value) || 0;
            
            if (baseInput && (parseFloat(baseInput.value) === 0 || !baseInput.value)) {
                baseInput.value = totalVal.toFixed(2);
            }
            
            calculateRow(row);
        });
    }
}

function getCurrentCrMode() {
    return document.getElementById('globalCrMode')?.value || 'none';
}

function recalculateAllRows() {
    const rows = document.querySelectorAll('#calcRows tr');
    rows.forEach(row => calculateRow(row));
}

function calculateRow(rowInput, activeInput = null) {
    const row = (rowInput && rowInput.jquery) ? rowInput[0] : rowInput;
    if (!row || typeof row.querySelector !== 'function') return;
    
    const crMode = getCurrentCrMode();
    const ltaYearsCount = parseInt(document.getElementById('ltaYearsCount')?.value || 3);
    const selectDraftEl = document.getElementById('rate_draft_id');
    const selectedDraftOption = selectDraftEl ? selectDraftEl.options[selectDraftEl.selectedIndex] : null;
    const manpowerRateSec = selectedDraftOption ? parseFloat(selectedDraftOption.getAttribute('data-mp')) || 0 : 0;
    const globalQtyForecast = parseFloat(row.querySelector('[name="qty_forecast_month"]')?.value) || 0;
    const cycleTime = parseFloat(row.querySelector('[name="cycle_time"]')?.value) || 0;
    const processTypeSelect = row.querySelector('[name="other_process_type"]');
    const processType = processTypeSelect ? processTypeSelect.value : '';
    const ctOtherInput = row.querySelector('[name="ct_other"]');
    const ctOther = parseFloat(ctOtherInput?.value) || 0;
    const rateAnnealInput = row.querySelector('[name="rate_annealing"]');
    const rateAnnealing = parseFloat(rateAnnealInput?.value) || 0.00;

    let otherProcessCost = 0;
    if (['finishing', 'inspection', 'assembly'].includes(processType)) {
        otherProcessCost = ctOther * manpowerRateSec;
        if (rateAnnealInput) {
            rateAnnealInput.disabled = true;
            rateAnnealInput.style.background = '#f1f5f9';
            rateAnnealInput.value = 0;
        }
    } else if (processType === 'annealing') {
        otherProcessCost = ctOther * rateAnnealing;
        if (rateAnnealInput) {
            rateAnnealInput.disabled = false;
            rateAnnealInput.style.background = '#ffffff';
        }
    } else {
        otherProcessCost = 0;
        if (rateAnnealInput) {
            rateAnnealInput.disabled = true;
            rateAnnealInput.style.background = '#f1f5f9';
            rateAnnealInput.value = 0;
        }
    }

    if (row.querySelector('[name="other_process_cost"]')) {
        row.querySelector('[name="other_process_cost"]').value = otherProcessCost.toFixed(2);
    }

    const basicPrice = parseFloat(row.querySelector('[name="basic_price"]')?.value) || 0;
    const currency = row.querySelector('[name="currency"]')?.value || 'USD';
    const exchangeRate = parseFloat(row.querySelector('[name="exchange_rate"]')?.value) || 0;
    const partWeight = parseFloat(row.querySelector('[name="part_weight"]')?.value) || 0;
    const runnerWeight = parseFloat(row.querySelector('[name="runner_weight"]')?.value) || 0;
    const pigmenCost = parseFloat(row.querySelector('[name="pigmen_cost"]')?.value) || 0;
    const cavity = parseFloat(row.querySelector('[name="cavity"]')?.value) || 1;
    const ratePerSecond = parseFloat(row.querySelector('[name="rate_hour"]')?.value) || 0;
    
    const purgingOriKg = parseFloat(row.querySelector('[name="purging_ori_kg"]')?.value) || 0;
    const cellpurgKg = parseFloat(row.querySelector('[name="purging_cellpurg_kg"]')?.value) || 0;
    const cellpurgPrice = parseFloat(row.querySelector('[name="cellpurge_price"]')?.value) || 0;
    const dandoriMinutes = parseFloat(row.querySelector('[name="dandori_minutes"]')?.value) || 0;
    
    let idrPriceKg = parseFloat(row.querySelector('[name="idr_price_kg"]')?.value) || 0;
    if (basicPrice > 0) {
        idrPriceKg = currency === 'IDR' ? basicPrice : basicPrice * exchangeRate;
        if (row.querySelector('[name="idr_price_kg"]')) {
            row.querySelector('[name="idr_price_kg"]').value = idrPriceKg.toFixed(2);
        }
    }

    let purgingPcs = 0;
    if (globalQtyForecast > 0) {
        purgingPcs = ((purgingOriKg * idrPriceKg) + (cellpurgKg * cellpurgPrice)) / globalQtyForecast;
    }
    if (row.querySelector('[name="purging"]')) {
        row.querySelector('[name="purging"]').value = purgingPcs.toFixed(2);
    }

    let dandoriPcs = 0;
    if (globalQtyForecast > 0) {
        dandoriPcs = (dandoriMinutes * 60 * ratePerSecond) / globalQtyForecast;
    }
    if (row.querySelector('[name="dandori"]')) {
        row.querySelector('[name="dandori"]').value = dandoriPcs.toFixed(2);
    }

    const rejectPercent = parseFloat(row.querySelector('[name="reject_rate"]')?.value) || 0.00;
    const packing = parseFloat(row.querySelector('[name="packing"]')?.value) || 0;
    const transport = parseFloat(row.querySelector('[name="transport"]')?.value) || 0;
    const ohPercent = parseFloat(row.querySelector('[name="oh_percent"]')?.value) || 0;
    const moldMtnCostMonth = parseFloat(row.querySelector('[name="cost_per_month"]')?.value) || 0;

    let moldMtnPcs = 0;
    if (globalQtyForecast > 0) {
        moldMtnPcs = moldMtnCostMonth / globalQtyForecast;
    }

    const inputMoldMtnPcs = row.querySelector('[name="mold_mtn"]');
    if (inputMoldMtnPcs) {
        inputMoldMtnPcs.value = moldMtnPcs.toFixed(4);
    }
    
    let weightPcs = parseFloat(row.querySelector('[name="weight_per_pcs"]')?.value) || 0;
    if (partWeight > 0 || runnerWeight > 0) {
        weightPcs = partWeight + (runnerWeight / cavity);
        if(row.querySelector('[name="weight_per_pcs"]')) {
            row.querySelector('[name="weight_per_pcs"]').value = weightPcs.toFixed(4);
        }
    }

    const matCost = ((weightPcs * idrPriceKg) / 1000) + pigmenCost;
    const processCost = ((cycleTime / cavity) * ratePerSecond) + purgingPcs + dandoriPcs;
    const rejectionRate = rejectPercent * matCost;

    const moldPrice = parseFloat(row.querySelector('[name="mold_price"]')?.value) || 0;
    const depreciationYears = parseFloat(row.querySelector('[name="depreciation_years"]')?.value) || 1;
    
    let moldDepreciationPcs = 0;
    const totalMonths = depreciationYears * 12;

    if (moldPrice > 0 && globalQtyForecast > 0) {
        moldDepreciationPcs = moldPrice / (globalQtyForecast * totalMonths);
    }

    const depPcsEl = row.querySelector('[name="mold_depreciation_pcs"]');
    if (depPcsEl) {
        depPcsEl.value = moldDepreciationPcs.toFixed(4);
    }

    const cogs = matCost + processCost + rejectionRate + otherProcessCost + packing + transport;
    const ohProfit = cogs * (ohPercent / 100);
    const total = cogs + ohProfit + moldDepreciationPcs + moldMtnPcs;

    if(row.querySelector('[name="material_price"]')) row.querySelector('[name="material_price"]').value = matCost.toFixed(2);
    if(row.querySelector('[name="process_cost"]')) row.querySelector('[name="process_cost"]').value = processCost.toFixed(2);
    if(row.querySelector('[name="rejection_rate"]')) row.querySelector('[name="rejection_rate"]').value = rejectionRate.toFixed(2);
    if(row.querySelector('[name="cogs"]')) row.querySelector('[name="cogs"]').value = cogs.toFixed(2);
    if(row.querySelector('[name="oh_profit"]')) row.querySelector('[name="oh_profit"]').value = ohProfit.toFixed(2);
    if(row.querySelector('[name="total"]')) row.querySelector('[name="total"]').value = total.toFixed(2);

    const crBaseInput = row.querySelector('[name="cr_base_val"]');
    let baseValue = parseFloat(crBaseInput?.value);
    
    if (isNaN(baseValue) || baseValue <= 0) {
        baseValue = total;
        if (crBaseInput && document.activeElement !== crBaseInput) {
            crBaseInput.value = baseValue.toFixed(2);
        }
    }
    
    let previousPrice = baseValue;
    const pctInputs = row.querySelectorAll('.input-cr-lta-pct');
    const resInputs = row.querySelectorAll('.input-cr-lta-res');

    for (let i = 0; i < ltaYearsCount; i++) {
        if (pctInputs[i] && resInputs[i]) {
            const pctVal = parseFloat(pctInputs[i].value) || 0;
            const resVal = previousPrice * (1 + (pctVal / 100));
            
            resInputs[i].value = resVal.toFixed(2);
            previousPrice = resVal; 
        }
    }

    const lastLtaCost = previousPrice;
    const blPercent = parseFloat(row.querySelector('[name="cr_pct_bl"]')?.value) || 0;
    const blCost = lastLtaCost * (1 - (blPercent / 100));

    if (row.querySelector('[name="cr_res_bl"]')) {
        row.querySelector('[name="cr_res_bl"]').value = blCost.toFixed(2);
    }

    let finalCost = total;
    if (crMode === 'with_bl') {
        finalCost = blCost;
    } else if (crMode === 'without_bl') {
        finalCost = lastLtaCost;
    }

    if (row.querySelector('[name="cr_final_cost"]')) {
        row.querySelector('[name="cr_final_cost"]').value = finalCost.toFixed(2);
    }
}

function addRow() {
    const tbody = document.getElementById('calcRows');
    const currentGroup = Math.ceil(tbody.rows.length / 2) + 1;
    const defaultPartNo = `${currentGroup}`;

    createRowElement(tbody, 'Internal', defaultPartNo, `${currentGroup} (Int)`);
    createRowElement(tbody, 'External', defaultPartNo, `${currentGroup} (Ext)`);

    updateCounts();
    const activeMode = getCurrentCrMode();
    selectCrMode(activeMode);
}

function createRowElement(tbody, type, partNumber, partName) {
    const baseIndex = tbody.rows.length + 1;
    const row = document.createElement('tr');
    row.className = "process-row-item";
    row.setAttribute('data-row-id', baseIndex);

    const currentType = (type || 'internal').toLowerCase();
    const ltaYearsCount = parseInt(document.getElementById('ltaYearsCount')?.value || 3);

    let ltaCellsHtml = '';
        for (let i = 1; i <= ltaYearsCount; i++) {
            ltaCellsHtml += `
                <td class="cr-col cr-wob-only col-sm dynamic-lta-cell">
                    <input class="quote-input input-cr-lta-pct" type="number" step="0.01" data-year="${i}" value="0.00" style="font-weight:600;" placeholder="%" oninput="calculateRow(this.closest('tr'), this)">
                </td>
                <td class="cr-col cr-wob-only col-md dynamic-lta-cell">
                    <input class="quote-input input-cr-lta-res" type="number" step="0.01" data-year="${i}" placeholder="Nilai LTA" readonly style="background:#f1f5f9;">
                </td>
            `;
        }

    row.innerHTML = `
        <td><input type="checkbox" class="row-checkbox"></td>
        <td class="row-number">${baseIndex}</td>
        <td>
            <select class="quote-input" name="type" onchange="calculateRow(this.closest('tr'))">
                <option value="internal" ${currentType === 'internal' ? 'selected' : ''}>Internal</option>
                <option value="external" ${currentType === 'external' ? 'selected' : ''}>External</option>
            </select>
        </td>
        <td>
            <input class="quote-input" type="text" name="part_number" value="${partNumber}" data-packing-link="true">
            <input type="hidden" name="packing_standard_id" value="0">
        </td>
        <td>
            <div class="search-container">
                <input class="quote-input part-name-input" type="text" name="part_name" id="part_name_${baseIndex}" value="${partName}" autocomplete="off" placeholder="Ketik Part Name...">
                <div class="live-search-results" id="search_results_${baseIndex}"></div>
            </div>
        </td>
        <td><input type="text" name="material_spec" class="quote-input form-control material_spec_input" placeholder="Spesifikasi material..." autocomplete="off"></td>
        <td><input class="quote-input" type="number" step="0.01" name="basic_price" value="0"></td>
        <td><select class="quote-input" name="currency"><option value="USD">USD</option><option value="IDR">IDR</option></select></td>
        <td><input class="quote-input" type="number" step="0.01" name="exchange_rate" value="16279.87"></td>
        <td><input class="quote-input" type="number" step="0.01" name="part_weight" value="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="runner_weight" value="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="pigmen_cost" value="0"></td>
        <td><input type="number" name="weight_per_pcs" class="quote-input form-control weight_per_pcs" step="any" oninput="calculateRow(this.closest('tr'))"></td>
        <td><input type="number" name="idr_price_kg" class="quote-input form-control idr_price_kg" step="any" oninput="calculateRow(this.closest('tr'))"></td>
        <td><input class="quote-input" type="number" step="0.01" name="material_price" value="0" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="cycle_time" id="cycle_time_${baseIndex}" value="0"></td>
        <td><input class="quote-input" type="number" step="0" name="cavity" value="1"></td>
        <td>
            <select class="quote-input" name="mc_ton" id="mc_tonnage_${baseIndex}" onchange="hitungCostProsesBaris(${baseIndex})">
                <option value="">- Pilih MC Ton -</option>
            </select>
        </td>
        <td><input class="quote-input" type="number" step="0" name="qty_forecast_month" value="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="purging_ori_kg" value="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="purging_cellpurg_kg" value="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="cellpurge_price" value="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="purging" value="0" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="dandori_minutes" value="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="dandori" value="0" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0" name="rate_hour" id="rate_sec_${baseIndex}" value="0" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="process_cost" id="cost_pcs_process_${baseIndex}" value="0.00" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="reject_rate" value="0.00"></td>
        <td><input class="quote-input" type="number" step="0.01" name="rejection_rate" value="0" readonly style="background:#f1f5f9;"></td>
        <td>
            <select class="quote-input other-process-type" name="other_process_type" onchange="calculateRow(this.closest('tr'))">
                <option value="Tanpa Proses">Tanpa Proses</option>
                <option value="finishing">Finishing</option>
                <option value="inspection">Inspection</option>
                <option value="assembly">Assembly</option>
                <option value="annealing">Annealing</option>
            </select>
        </td>
        <td><input class="quote-input ct-other" type="number" step="0.01" name="ct_other" value="0" placeholder="sec" oninput="calculateRow(this.closest('tr'))"></td>
        <td><input class="quote-input rate-annealing" type="number" step="0.01" name="rate_annealing" value="0.00" placeholder="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="other_process_cost" value="0.00" readonly style="background:#fef3c7; font-weight:600;"></td>
        <td><input class="quote-input packing-input" type="number" step="any" name="packing" id="packing_${baseIndex}" value="0" placeholder="0"></td>
        <td><input class="quote-input transport-input" type="number" step="any" name="transport" id="transport_${baseIndex}" value="0" placeholder="0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="cogs" value="0" readonly style="background:#cbd5e1; font-weight:600;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="oh_percent" value="1.0"></td>
        <td><input class="quote-input" type="number" step="0.01" name="oh_profit" value="0" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="mold_price" value="0" placeholder="Harga Mold" oninput="calculateRow(this.closest('tr'))"></td>
        <td>
            <select class="quote-input" name="depreciation_years" onchange="calculateRow(this.closest('tr'))">
                <option value="1">1 Thn</option>
                <option value="2">2 Thn</option>
                <option value="3">3 Thn</option>
            </select>
        </td>
        <td><input class="quote-input" type="number" step="0.1" name="mold_depreciation_pcs" value="0" readonly style="background:#f1f5f9;"></td>
        <td>
            <select class="quote-input mold-key-select" name="mold_key_spec" onchange="onMoldKeyChange(this)">
                <option value="">- Pilih Key -</option>
            </select>
            <input type="hidden" name="cost_per_month" value="0">
        </td>
        <td><input class="quote-input" type="number" step="0" name="mold_mtn" value="0" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="total" value="0.00" readonly style="background:#bbf7d0; font-weight:bold;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="lumpsum_price" value="0.00"></td>
        
        <td class="cr-col col-lg lta-cells-marker" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_base_val" value="0.00" oninput="calculateRow(this.closest('tr'), this)">
        </td>
        ${ltaCellsHtml}
        <td class="cr-col cr-bl-only col-sm" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_pct_bl" value="0" placeholder="%" oninput="calculateRow(this.closest('tr'), this)">
        </td>
        <td class="cr-col cr-bl-only col-md" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_res_bl" value="0.00" readonly style="background:#f1f5f9;">
        </td>
        <td class="cr-col col-xl col-cr-final" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_final_cost" value="0.00" readonly style="background:#e0f2fe; font-weight:bold;">
        </td>
    `;

    tbody.appendChild(row);
    bindRow(row);
    return row;
}

function appendSavedRow(rowData) {
    const tbody = document.getElementById('calcRows');
    const baseIndex = tbody.rows.length + 1;
    const type = (rowData.type || 'internal').toLowerCase();
    
    const row = document.createElement('tr');
    row.className = "process-row-item";
    row.setAttribute('data-row-id', baseIndex);

    const ltaYearsCount = parseInt(rowData.cr_lta_years|| document.getElementById('ltaYearsCount')?.value || 3);
    const savedPct = rowData.cr_lta_pct_list || [];
    const savedRes = rowData.cr_lta_res_list || [];

    let ltaCellsHtml = '';
        for (let i = 0; i < ltaYearsCount; i++) {
            const pctVal = savedPct[i] !== undefined ? savedPct[i] : 0;
            const resVal = savedRes[i] !== undefined ? savedRes[i] : 0;
            ltaCellsHtml += `
                <td class="cr-col cr-wob-only col-sm dynamic-lta-cell">
                    <input class="quote-input input-cr-lta-pct" type="number" step="0.01" data-year="${i+1}" value="${pctVal}" style="font-weight:600;" placeholder="%" oninput="calculateRow(this.closest('tr'), this)">
                </td>
                <td class="cr-col cr-wob-only col-md dynamic-lta-cell">
                    <input class="quote-input input-cr-lta-res" type="number" step="0.01" data-year="${i+1}" value="${resVal}" placeholder="Nilai LTA" readonly style="background:#f1f5f9;">
                </td>
            `;
        }

    row.innerHTML = `
        <td><input type="checkbox" class="row-checkbox"></td>
        <td class="row-number">${baseIndex}</td>
        <td>
            <select class="quote-input" name="type" onchange="calculateRow(this.closest('tr'))">
                <option value="Internal" ${type === 'internal' ? 'selected' : ''}>Internal</option>
                <option value="External" ${type === 'external' ? 'selected' : ''}>External</option>
            </select>
        </td>
        <td>
            <input class="quote-input" type="text" name="part_number" id="part_number_${baseIndex}" value="${rowData.part_number || ''}" data-packing-link="true" onchange="ambilCostPackingOtomatis(${baseIndex})">
            <input type="hidden" name="packing_standard_id" value="${rowData.packing_standard_id || 0}">
        </td>
        <td><input class="quote-input" type="text" name="part_name" id="part_name_${baseIndex}" value="${rowData.part_name || ''}" oninput="ambilCostPackingOtomatis(${baseIndex})"></td>
        <td><input class="quote-input material_spec_input" type="text" name="material_spec" value="${rowData.material_spec || ''}" placeholder="Spesifikasi material..." autocomplete="off"></td>
        <td><input class="quote-input" type="number" step="0.01" name="basic_price" value="${rowData.basic_price || 0}"></td>
        <td><select class="quote-input" name="currency"><option value="USD" ${rowData.currency === 'USD' ? 'selected' : ''}>USD</option><option value="IDR" ${rowData.currency === 'IDR' ? 'selected' : ''}>IDR</option></select></td>
        <td><input class="quote-input" type="number" step="0.01" name="exchange_rate" value="${rowData.exchange_rate || 16279.87}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="part_weight" value="${rowData.part_weight || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="runner_weight" value="${rowData.runner_weight || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="pigmen_cost" value="${rowData.pigmen_cost || 0}"></td>
        <td><input class="quote-input weight_per_pcs" type="number" step="0.01" name="weight_per_pcs" value="${rowData.weight_per_pcs || 0}"></td>
        <td><input class="quote-input idr_price_kg" type="number" step="0.01" name="idr_price_kg" value="${rowData.idr_price_kg || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="material_price" value="${rowData.material_price || 0}" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="cycle_time" id="cycle_time_${baseIndex}" value="${rowData.cycle_time || 0}"></td>
        <td><input class="quote-input" type="number" step="0" name="cavity" value="${rowData.cavity || 1}"></td>
        <td>
            <select class="quote-input" name="mc_ton" id="mc_tonnage_${baseIndex}" data-saved-val="${rowData.mc_ton || ''}">
                <option value="">- Pilih MC Ton -</option>
            </select>
        </td>
        <td><input class="quote-input" type="number" step="0" name="qty_forecast_month" value="${rowData.qty_forecast_month || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="purging_ori_kg" value="${rowData.purging_ori_kg || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="purging_cellpurg_kg" value="${rowData.purging_cellpurg_kg || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="cellpurge_price" value="${rowData.cellpurge_price || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="purging" value="${rowData.purging || 0}" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="dandori_minutes" value="${rowData.dandori_minutes || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="dandori" value="${rowData.dandori || 0}" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.1" name="rate_hour" id="rate_sec_${baseIndex}" value="${rowData.rate_hour || '0'}" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="process_cost" id="cost_pcs_process_${baseIndex}" value="${rowData.process_cost || 0}" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="reject_rate" value="${rowData.reject_rate || 0.00}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="rejection_rate" value="${rowData.rejection_rate || 0}" readonly style="background:#f1f5f9;"></td>
        <td>
            <select class="quote-input" name="other_process_type" onchange="calculateRow(this.closest('tr'))">
                <option value="">Tanpa Process</option>
                <option value="finishing" ${rowData.other_process_type === 'finishing' ? 'selected' : ''}>Finishing</option>
                <option value="inspection" ${rowData.other_process_type === 'inspection' ? 'selected' : ''}>Inspection</option>
                <option value="assembly" ${rowData.other_process_type === 'assembly' ? 'selected' : ''}>Assembly</option>
                <option value="annealing" ${rowData.other_process_type === 'annealing' ? 'selected' : ''}>Annealing</option>
            </select>
        </td>
        <td><input class="quote-input" type="number" step="0.01" name="ct_other" value="${rowData.ct_other || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="rate_annealing" value="0.00" placeholder="0" oninput="calculateRow(this.closest('tr'))" disabled style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="other_process_cost" value="${rowData.other_process_cost || 0}" readonly style="background:#fef3c7; font-weight:600;"></td>
        <td><input class="quote-input packing-input" type="number" step="any" name="packing" value="${rowData.packing || 0}"></td>
        <td><input class="quote-input transport-input" type="number" step="any" name="transport" value="${rowData.transport || 0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="cogs" value="${rowData.cogs || 0}" readonly style="background:#cbd5e1; font-weight:600;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="oh_percent" value="${rowData.oh_percent || 1.0}"></td>
        <td><input class="quote-input" type="number" step="0.01" name="oh_profit" value="${rowData.oh_profit || 0}" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="mold_price" value="${rowData.mold_price || 0}" placeholder="Harga Mold" oninput="calculateRow(this.closest('tr'))"></td>
        <td>
            <select class="quote-input" name="depreciation_years" onchange="calculateRow(this.closest('tr'))">
                <option value="1" ${rowData.depreciation_years == 1 ? 'selected' : ''}>1 Thn</option>
                <option value="2" ${rowData.depreciation_years == 2 ? 'selected' : ''}>2 Thn</option>
                <option value="3" ${rowData.depreciation_years == 3 ? 'selected' : ''}>3 Thn</option>
            </select>
        </td>
        <td><input class="quote-input" type="number" step="0.1" name="mold_depreciation_pcs" value="${rowData.mold_depreciation_pcs || 0}" readonly style="background:#f1f5f9;"></td>
        <td>
            <select class="quote-input mold-key-select" name="mold_key_spec" data-saved-key="${rowData.mold_key_spec || ''}" onchange="onMoldKeyChange(this)">
                <option value="">-- Pilih Key --</option>
            </select>
            <input type="hidden" name="cost_per_month" value="${rowData.mold_cost_month || 0}">
        </td>
        <td><input class="quote-input" type="number" step="0" name="mold_mtn" value="${rowData.mold_mtn || 0}" readonly style="background:#f1f5f9;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="total" value="${rowData.total || 0}" readonly style="background:#bbf7d0; font-weight:bold;"></td>
        <td><input class="quote-input" type="number" step="0.01" name="lumpsum_price" value="${rowData.lumpsum_price || 0}"></td>
        
        <td class="cr-col col-lg lta-cells-marker" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_base_val" value="${rowData.cr_base_val || 0.00}" oninput="calculateRow(this.closest('tr'), this)">
        </td>
        ${ltaCellsHtml}
        <td class="cr-col cr-bl-only col-sm" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_pct_bl" value="${rowData.cr_pct_bl || 0}" placeholder="%" oninput="calculateRow(this.closest('tr'), this)">
        </td>
        <td class="cr-col cr-bl-only col-md" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_res_bl" value="${rowData.cr_res_bl || 0.00}" readonly style="background:#f1f5f9;">
        </td>
        <td class="cr-col col-xl col-cr-final" style="display:none;">
            <input class="quote-input" type="number" step="0.01" name="cr_final_cost" value="${rowData.cr_final_cost || 0.00}" readonly style="background:#e0f2fe; font-weight:bold;">
        </td>
    `;

    tbody.appendChild(row);
    bindRow(row, rowData);
    selectCrMode(getCurrentCrMode()); 
}

function bindRow(row, rowData = null) {
    const inputs = row.querySelectorAll('input, select');
    const partNumberInput = row.querySelector('[name="part_number"]');
    const packingStandardInput = row.querySelector('[name="packing_standard_id"]');
    const cycleTimeInput = row.querySelector('[name="cycle_time"]');
    const rowId = row.getAttribute('data-row-id');
    const mcTonSelect = row.querySelector('select[name="mc_ton"]');
    if (mcTonSelect) {
        const savedTon = rowData ? rowData.mc_ton : "";
        populateTonnageOptions(mcTonSelect, savedTon);
    }

    inputs.forEach(el => {
        el.addEventListener('input', (e) => {
            if (el === partNumberInput && typeof packingStandards !== 'undefined' && packingStandards[el.value]) {
                packingStandardInput.value = packingStandards[el.value].id;
            }
            if (el === cycleTimeInput) {
                hitungCostProsesBaris(rowId);
            } else {
                calculateRow(row, e.target);
            }
        });

        el.addEventListener('change', (e) => {
            if (el.name === 'mc_ton') {
                hitungCostProsesBaris(rowId);
            } else {
                calculateRow(row, e.target);
            }
        });
    });

    const moldSelect = row.querySelector('.mold-key-select');
    if (moldSelect) {
        const savedKey = moldSelect.getAttribute('data-saved-key') || "";
        populateMoldKeyOptions(moldSelect, savedKey);
    }

    calculateRow(row);
    initPackTransLiveSearch(row);
}

function openReviewModal() {
    const selectDraft = document.getElementById('rate_draft_id');
    const selectCustomer = document.querySelector('[name="customer_name"]') || document.getElementById('opt_customer');
    const selectMmp = document.getElementById('acuan_mmp');

    if (!selectDraft || selectDraft.value === "0" || selectDraft.value === "") {
        Swal.fire('Peringatan', 'Silakan pilih Tahun Masspro terlebih dahulu!', 'warning');
        if (selectDraft) selectDraft.focus();
        return;
    }

    if (!selectCustomer || selectCustomer.value === "") {
        Swal.fire('Peringatan', 'Customer Name belum dipilih!', 'warning');
        if (selectCustomer) selectCustomer.focus();
        return;
    }

    calculateAll(); 
    
    const currentCrMode = getCurrentCrMode();
    const ltaYearsCount = parseInt(document.getElementById('ltaYearsCount')?.value || 3);
    const isCrActive = (currentCrMode === 'with_bl' || currentCrMode === 'without_bl');

    const ltaHeaderEl = document.getElementById('dynamicLtaHeaders');
    if (ltaHeaderEl) {
        if (isCrActive) {
            ltaHeaderEl.style.display = '';
            ltaHeaderEl.setAttribute('colspan', ltaYearsCount);
            ltaHeaderEl.textContent = `LTA 1–${ltaYearsCount} (%)`;
        } else {
            ltaHeaderEl.style.display = 'none';
        }
    }

    const crBlHeaders = document.querySelectorAll('#reviewModal .cr-bl-dep');
    crBlHeaders.forEach(el => el.style.display = (currentCrMode === 'with_bl') ? '' : 'none');

    const customerName = selectCustomer.value || '-';
    const customerPic = document.querySelector('[name="customer_pic"]')?.value || '-';
    const customerAddress = document.querySelector('[name="customer_address"]')?.value || '-';
    const customerPhone = document.querySelector('[name="customer_phone"]')?.value || '-';
    const customerEmail = document.querySelector('[name="customer_email"]')?.value || '-';
    const quoNo = document.querySelector('[name="quotation_no"]')?.value || '-';
    const quoDate = document.querySelector('[name="quotation_date"]')?.value || '-';
    const mmpText = selectMmp && selectMmp.selectedIndex >= 0 ? selectMmp.options[selectMmp.selectedIndex].text : '-';

    document.getElementById('modalQuoMeta').innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; color: #334155;">
            <div>
                <strong style="color: #0f172a; font-size: 0.9rem; display: block; margin-bottom: 5px;">FROM:</strong>
                <strong>PT Citra Plastik Makmur</strong><br>
                Jl. Jababeka XIV A Blok J4F, Kawasan Industri Jababeka 1<br>
                <span>No. Quo: <strong>${quoNo}</strong></span> | <span>Tanggal: <strong>${quoDate}</strong></span><br>
                <span>MMP: <strong>${mmpText}</strong></span>
            </div>
            <div>
                <strong style="color: #0f172a; font-size: 0.9rem; display: block; margin-bottom: 5px;">TO:</strong>
                Company: <strong>${customerName}</strong><br>
                Attn / PIC: <strong>${customerPic}</strong><br>
                Address: ${customerAddress}<br>
                Telp/Email: ${customerPhone} / ${customerEmail}
            </div>
        </div>
    `;

    const tbodyReview = document.getElementById('modalReviewRows');
    if (tbodyReview) tbodyReview.innerHTML = '';
    
    const rows = document.querySelectorAll('#calcRows tr');
    if(rows.length === 0) {
        Swal.fire('Info', 'Belum ada data slot untuk dihitung.', 'info');
        return;
    }

    const mainNoteText = document.querySelector('[name="quotation_notes"]')?.value || '';
    document.getElementById('modalQuotationNotes').value = mainNoteText;

    rows.forEach((row, index) => {
        const type = row.querySelector('[name="type"]')?.value || 'internal';
        const partNo = row.querySelector('[name="part_number"]')?.value || '-';
        const partName = row.querySelector('[name="part_name"]')?.value || '-';
        
        const matSpecSelect = row.querySelector('[name="mat_spec"]');
        let matSpec = row.querySelector('[name="material_spec"]')?.value || '';
        if (!matSpec && matSpecSelect && matSpecSelect.selectedIndex >= 0) {
            matSpec = matSpecSelect.options[matSpecSelect.selectedIndex].text;
        }

        const basicPrice = parseFloat(row.querySelector('[name="basic_price"]')?.value) || 0;
        const currInput = row.querySelector('[name="currency"]')?.value || 'USD';
        const symbol = (currInput === 'USD' || currInput === '$') ? '$' : 'Rp ';
        const weightPcs = parseFloat(row.querySelector('[name="weight_per_pcs"]')?.value) || 0;
        const idrPriceKg = parseFloat(row.querySelector('[name="idr_price_kg"]')?.value) || 0;
        const pigmenCost = parseFloat(row.querySelector('[name="pigmen_cost"]')?.value) || 0;
        const matCost = parseFloat(row.querySelector('[name="material_price"]')?.value) || 0;
        const cycleTime = parseFloat(row.querySelector('[name="cycle_time"]')?.value) || 0;
        const cavity = parseFloat(row.querySelector('[name="cavity"]')?.value) || 1;
        const mcTon = row.querySelector('[name="mc_ton"]')?.value || '0';
        const rateSec = parseFloat(row.querySelector('[name="rate_hour"]')?.value) || 0;
        const processCost = parseFloat(row.querySelector('[name="process_cost"]')?.value) || 0;
        const rejectionRate = parseFloat(row.querySelector('[name="rejection_rate"]')?.value) || 0;

        const otherTypeSelect = row.querySelector('[name="other_process_type"]');
        const otherTypeName = otherTypeSelect && otherTypeSelect.selectedIndex >= 0 ? otherTypeSelect.options[otherTypeSelect.selectedIndex].text : 'Tanpa Proses';
        const costOtherProcess = parseFloat(row.querySelector('[name="other_process_cost"]')?.value) || 0;

        const packing = parseFloat(row.querySelector('[name="packing"]')?.value) || 0;
        const transport = parseFloat(row.querySelector('[name="transport"]')?.value) || 0;
        const cogs = parseFloat(row.querySelector('[name="cogs"]')?.value) || 0;
        const ohProfit = parseFloat(row.querySelector('[name="oh_profit"]')?.value) || 0;

        const moldDepreciationPcs = parseFloat(row.querySelector('[name="mold_depreciation_pcs"]')?.value) || 0;
        const moldMtn = parseFloat(row.querySelector('[name="mold_mtn"]')?.value) || 0;
        const total = parseFloat(row.querySelector('[name="total"]')?.value) || 0;
        const lumpsumPrice = parseFloat(row.querySelector('[name="lumpsum_price"]')?.value) || 0;

        const crBaseVal = parseFloat(row.querySelector('[name="cr_base_val"]')?.value) || 0;
        const crPctBl   = parseFloat(row.querySelector('[name="cr_pct_bl"]')?.value) || 0;
        const crFinalCost = parseFloat(row.querySelector('[name="cr_final_cost"]')?.value) || 0;

        let crCellsHtml = '';
        if (isCrActive) {
            if (currentCrMode === 'with_bl') {
                crCellsHtml += `<td align="right" class="cr-col cr-bl-dep">${formatMoney(crBaseVal)}</td>`;
            }
            
            const pctInputs = row.querySelectorAll('.input-cr-lta-pct');
            for (let i = 0; i < ltaYearsCount; i++) {
                const pctVal = parseFloat(pctInputs[i]?.value) || 0;
                crCellsHtml += `<td align="center" class="cr-col cr-wob-dep">${pctVal}%</td>`;
            }

            if (currentCrMode === 'with_bl') {
                crCellsHtml += `<td align="center" class="cr-col cr-bl-dep">${crPctBl}%</td>`;
            }
            crCellsHtml += `<td align="right" class="cr-col" style="background:#e0e7ff; font-weight:bold; color:#3730a3;">${formatMoney(crFinalCost)}</td>`;
        }

        const tr = document.createElement('tr');
        tr.style.background = type === 'external' ? '#f8fafc' : '#ffffff';
        
        tr.innerHTML = `
            <td align="center">${index + 1}</td>
            <td style="text-transform:uppercase; font-weight:bold; color:${type === 'external' ? '#2563eb' : '#16a34a'}" align="center">${type}</td>
            <td><strong>${partNo}</strong></td>
            <td>${partName}</td>
            <td>${matSpec}</td>
            <td align="right">${symbol}${formatMoney(basicPrice)}</td>
            <td align="right">${symbol}${formatMoney(pigmenCost)}</td>
            <td align="right" style="background:#fefec8;">${weightPcs.toFixed(2)}</td>
            <td align="right" style="background:#fefec8;">${formatMoney(idrPriceKg)}</td>
            <td align="right" style="background:#fefec8; font-weight:600;">${formatMoney(matCost)}</td>
            <td align="center" style="background:#eff6ff;">${cycleTime}</td>
            <td align="center" style="background:#eff6ff;">${cavity}</td>
            <td align="center" style="background:#eff6ff;">${mcTon} T</td>
            <td align="right" style="background:#eff6ff;">Rp ${rateSec.toFixed(2)}/s</td>
            <td align="right" style="background:#eff6ff; font-weight:600;">${formatMoney(processCost)}</td>
            <td align="right">${formatMoney(rejectionRate)}</td>
            <td align="center" style="background:#fdf2f8;">
                <div style="font-size:0.75rem; color:#be185d; font-weight:bold;">(${otherTypeName})</div>
                <div style="font-weight:600;">${formatMoney(costOtherProcess)}</div>
            </td>
            <td align="right">${formatMoney(packing)}</td>
            <td align="right">${formatMoney(transport)}</td>
            <td align="right" style="background:#cbd5e1; font-weight:bold;">${formatMoney(cogs)}</td>
            <td align="right">${formatMoney(ohProfit)}</td>
            <td align="right">${formatMoney(moldDepreciationPcs)}</td>
            <td align="right">${formatMoney(moldMtn)}</td>
            <td align="right" style="background:#dcfce7; font-weight:bold; color:#15803d; font-size:0.85rem;">${formatMoney(total)}</td>
            <td align="right" style="background:#fef3c7; font-weight:bold; color:#b45309; font-size:0.85rem;">${formatMoney(lumpsumPrice)}</td>
            ${crCellsHtml}
        `;
        if (tbodyReview) tbodyReview.appendChild(tr);
    });

    const reviewModal = document.getElementById('reviewModal');
    reviewModal.classList.remove('hidden-element');
    reviewModal.style.display = 'flex';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

function submitToReport() {
    const updatedNote = document.getElementById('modalQuotationNotes').value;
    const mainNotesInput = document.querySelector('[name="quotation_notes"]');
    if (mainNotesInput) mainNotesInput.value = updatedNote;

    const ltaYearsCount = parseInt(document.getElementById('ltaYearsCount')?.value || 3);

    const rows = Array.from(document.getElementById('calcRows').rows).map(row => {
        const dataObj = {};
        row.querySelectorAll('input, select').forEach(el => {
            if (el.name) {
                const cleanName = el.name.replace('[]', '');
                dataObj[cleanName] = el.value;
            }
        });

        const crLtaPctList = [];
        const crLtaResList = [];

        row.querySelectorAll('.input-cr-lta-pct').forEach(el => crLtaPctList.push(parseFloat(el.value) || 0));
        row.querySelectorAll('.input-cr-lta-res').forEach(el => crLtaResList.push(parseFloat(el.value) || 0));

        dataObj['cr_lta_years'] = ltaYearsCount;
        dataObj['cr_lta_pct_list'] = crLtaPctList;
        dataObj['cr_lta_res_list'] = crLtaResList;

        return {
            type: row.querySelector('[name="type"]')?.value || 'internal',
            data: dataObj
        };
    });
    
    document.getElementById('quotationRowsInput').value = JSON.stringify(rows);
    document.getElementById('saveQuotationFlag').value = '1';
    document.getElementById('quotationForm').submit();
}

function formatMoney(v) {
    return Number(v || 0).toLocaleString('id-ID', {maximumFractionDigits: 2});
}

function calculateAll() {
    const rows = document.querySelectorAll('#calcRows tr');
    rows.forEach(row => calculateRow(row));
}

function updateCounts() {
    const countSpan = document.getElementById('slotCount');
    if (countSpan) countSpan.textContent = document.getElementById('calcRows').rows.length;
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checkbox.checked);
}

function deleteSelectedRows() {
    const tbody = document.getElementById('calcRows');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    let selectedIndices = [];
    
    checkboxes.forEach((cb, index) => { if (cb.checked) selectedIndices.push(index); });

    if (!selectedIndices.length) return Swal.fire('Info', 'Pilih baris yang ingin dihapus.', 'info');
    
    Swal.fire({
        title: 'Hapus Baris?',
        text: 'Apakah Anda yakin ingin menghapus baris terpilih?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            for (let i = selectedIndices.length - 1; i >= 0; i--) {
                tbody.deleteRow(selectedIndices[i]);
            }
            document.getElementById('selectAllCheckbox').checked = false;
            Array.from(tbody.rows).forEach((row, idx) => {
                row.querySelector('.row-number').textContent = idx + 1;
                row.setAttribute('data-row-id', idx + 1);
            });
            updateCounts();
        }
    });
}

function autoFillCustomer() {
    const selectEl = document.getElementById('opt_customer');
    const customerIdInput = document.getElementById('customerIdInput');
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    
    if (!selectedOption || selectEl.value === "") {
        if (customerIdInput) customerIdInput.value = "0";
        document.getElementById('cust_pic').value = "";
        document.getElementById('cust_address').value = "";
        document.getElementById('cust_phone').value = "";
        document.getElementById('cust_email').value = "";
        return;
    }

    if (customerIdInput) customerIdInput.value = selectedOption.dataset.id || "0";
    
    document.getElementById('cust_pic').value = selectedOption.getAttribute('data-pic') || "";
    document.getElementById('cust_address').value = selectedOption.getAttribute('data-address') || "";
    document.getElementById('cust_phone').value = selectedOption.getAttribute('data-phone') || "";
    document.getElementById('cust_email').value = selectedOption.getAttribute('data-email') || "";
}

function refreshQuotationCustomers() {
    fetch('get_customers_ajax.php', { cache: 'no-store' })
        .then(response => response.json())
        .then(result => {
            if (!result.success || !Array.isArray(result.data)) return;
            const selectEl = document.getElementById('opt_customer');
            if (!selectEl) return;

            const selectedValue = selectEl.value;
            selectEl.querySelectorAll('option:not(:first-child)').forEach(option => option.remove());
            result.data.forEach(customer => {
                const option = document.createElement('option');
                option.value = customer.customer_name;
                option.textContent = customer.customer_name;
                option.dataset.id = customer.id || '';
                option.dataset.pic = customer.pic || '';
                option.dataset.address = customer.address || '';
                option.dataset.phone = customer.phone || '';
                option.dataset.email = customer.email || '';
                selectEl.appendChild(option);
            });

            if (selectedValue && result.data.some(customer => customer.customer_name === selectedValue)) {
                selectEl.value = selectedValue;
                autoFillCustomer();
            } else if (selectedValue) {
                selectEl.value = '';
                autoFillCustomer();
            }
        })
        .catch(error => console.error('Gagal memperbarui data customer:', error));
}

const quotationCustomerChannel = new BroadcastChannel('customer_master_update');
quotationCustomerChannel.onmessage = (event) => {
    const payload = event.data || {};
    if (payload.event === 'new_customer' || payload.event === 'update_customer' || payload.event === 'delete_customer') {
        refreshQuotationCustomers();
    }
};

function autoFillMasterProcessData(rowElement, tonnageVal) {
    const ton = parseFloat(tonnageVal) || 0;
    if (!ton) return;
    const matchedPurge = purgingMasterData.find(p => ton >= p.mc_ton_min && ton <= p.mc_ton_max);
    if (matchedPurge) {
        const inputPurgeOri  = rowElement.querySelector('[name="purging_ori_kg"]');
        const inputCellPurg  = rowElement.querySelector('[name="purging_cellpurg_kg"]');
        const inputCellPrice = rowElement.querySelector('[name="cellpurge_price"]');

        if (inputPurgeOri) inputPurgeOri.value = matchedPurge.purging_ori_kg || 0;
        if (inputCellPurg) inputCellPurg.value = matchedPurge.purging_cellpurg_kg || 0;
        if (inputCellPrice) inputCellPrice.value = matchedPurge.cellpurge_price || 0;
    } else {
        ['purging_ori_kg', 'purging_cellpurg_kg', 'cellpurge_price'].forEach(name => {
            const input = rowElement.querySelector(`[name="${name}"]`);
            if (input) input.value = 0;
        });
    }

    const matchedDandori = dandoriMasterData.find(d => ton >= d.mc_ton_min && ton <= d.mc_ton_max);
    if (matchedDandori && rowElement.querySelector('[name="dandori_minutes"]')) {
        rowElement.querySelector('[name="dandori_minutes"]').value = matchedDandori.dandori_minutes || 0;
    } else {
        const dandoriInput = rowElement.querySelector('[name="dandori_minutes"]');
        if (dandoriInput) dandoriInput.value = 0;
    }
}

function refreshQuotationProcessMasters() {
    fetch('get_process_master_data.php', { cache: 'no-store' })
        .then(response => response.json())
        .then(result => {
            if (!result.success) return;
            window.purgingMasterData = result.purging || [];
            window.dandoriMasterData = result.dandori || [];
            window.moldMasterData = result.mold || [];

            document.querySelectorAll('#calcRows tr').forEach(row => {
                const tonnage = row.querySelector('[name="mc_ton"]')?.value || '';
                autoFillMasterProcessData(row, tonnage);
                const moldSelect = row.querySelector('.mold-key-select');
                if (moldSelect) populateMoldKeyOptions(moldSelect, moldSelect.value);
                calculateRow(row);
            });
        })
        .catch(error => console.error('Gagal memperbarui master process:', error));
}

const quotationProcessMasterChannel = new BroadcastChannel('process_master_update');
quotationProcessMasterChannel.onmessage = (event) => {
    const payload = event.data || {};
    if (payload.event === 'new_process_master' || payload.event === 'update_process_master' || payload.event === 'delete_process_master') {
        refreshQuotationProcessMasters();
    }
};

function hitungCostProsesBaris(elementOrRowId) {
    let $row;
    let tonnage = "";

    if (typeof elementOrRowId === 'object') {
        $row = $(elementOrRowId).closest('tr');
        tonnage = $row.find('select[name="mc_ton"]').val();
    } else {
        const rowId = elementOrRowId;
        $row = $(`tr[data-row-id="${rowId}"]`);
        tonnage = $(`#mc_tonnage_${rowId}`).val();
    }

    let draftId = $('#rate_draft_id').val();

    if ($row.length) {
        autoFillMasterProcessData($row[0], tonnage);
    }

    if (!draftId || !tonnage || draftId === "0") {
        $row.find('[name="rate_hour"]').val('0');
        if ($row.length) calculateRow($row[0]);
        return;
    }

    $.ajax({
        url: 'get_rate_ajax.php',
        type: 'GET',
        data: { draft_id: draftId, tonnage: tonnage },
        dataType: 'json',
        success: function(response) {
            let rateSec = parseFloat(response.rate_per_second) || 0;
            $row.find('[name="rate_hour"]').val(rateSec.toFixed(4));
            if ($row.length) calculateRow($row[0]);
        },
        error: function() {
            console.error("Gagal mengambil data rate proses.");
        }
    });
}

function updateAllProcessRates() {
    const draftId = $('#rate_draft_id').val();
    if (draftId && window.allDraftsData && window.allDraftsData[draftId]) {
        const selectedDraft = window.allDraftsData[draftId];
        if (selectedDraft.draft_title) {
            const yearMatch = selectedDraft.draft_title.match(/\d{4}/);
            if (yearMatch) {
                $('#note_input_masspro').val(yearMatch[0]);
            }
        }
    }

    $('#calcRows tr').each(function() {
        if (typeof calculateRow === 'function') {
            calculateRow(this);
        } else if (typeof hitungCostProsesBaris === 'function') {
            hitungCostProsesBaris($(this));
        }
    });
}

function populateMatSpecOptions(selectElement) {
    selectElement.innerHTML = '<option value="">- Pilih Spec -</option>';
    currentMmpDetails.forEach((item, index) => {
        const opt = document.createElement('option');
        opt.value = index;
        opt.textContent = item.mat_quotation || item.mat_aktual || 'Tanpa Nama Material';
        selectElement.appendChild(opt);
    });
}

function onAcuanMmpChange(mmpId) {
    const hiddenMmpInput = document.getElementById('mmp_id');
    if (hiddenMmpInput) hiddenMmpInput.value = mmpId || 0;
    const matSpecSelects = document.querySelectorAll('select[name="mat_spec"]');

    if (!mmpId) {
        currentMmpDetails = [];
        matSpecSelects.forEach(select => {
            select.innerHTML = '<option value="">- Pilih Material Spec -</option>';
        });
        return;
    }
    currentMmpDetails = mmpMasterData.filter(item => item.mmp_id == mmpId);

    matSpecSelects.forEach(select => {
        populateMatSpecOptions(select);
    });
}

function refreshQuotationMmpData() {
    fetch('get_mmp_data.php?action=get_mmp_all', { cache: 'no-store' })
        .then(response => response.json())
        .then(result => {
            if (!result.success || !Array.isArray(result.data)) return;

            window.mmpMasterData = result.data;
            const mmpSelect = document.getElementById('acuan_mmp');
            if (!mmpSelect) return;

            const selectedValue = mmpSelect.value;
            const documents = [];
            result.data.forEach(item => {
                if (!documents.some(doc => String(doc.mmp_id) === String(item.mmp_id))) {
                    documents.push(item);
                }
            });

            mmpSelect.querySelectorAll('option:not(:first-child)').forEach(option => option.remove());
            documents.forEach(documentItem => {
                const option = document.createElement('option');
                option.value = documentItem.mmp_id;
                option.textContent = documentItem.judul_mmp || 'MMP Tanpa Judul';
                option.selected = String(documentItem.mmp_id) === String(selectedValue);
                mmpSelect.appendChild(option);
            });

            if (selectedValue && documents.some(item => String(item.mmp_id) === String(selectedValue))) {
                onAcuanMmpChange(selectedValue);
            } else if (selectedValue) {
                mmpSelect.value = '';
                onAcuanMmpChange('');
            }
        })
        .catch(error => console.error('Gagal memperbarui data MMP:', error));
}

const quotationMmpChannel = new BroadcastChannel('matrix_update');
quotationMmpChannel.onmessage = (event) => {
    const payload = event.data || {};
    if (payload.event === 'new_mmp' || payload.event === 'update_mmp' || payload.event === 'delete_mmp') {
        refreshQuotationMmpData();
    }
};

function onMaterialSpecChange(selectElement) {
    const selectedIndex = selectElement.value;
    const row = selectElement.closest('tr'); 
    if (!row) return;

    if (selectedIndex === "" || !currentMmpDetails[selectedIndex]) {
        row.querySelector('[name="basic_price"]').value = 0;
        row.querySelector('[name="part_weight"]').value = 0;
        row.querySelector('[name="idr_price_kg"]').value = 0;
        row.querySelector('[name="weight_per_pcs"]').value = 0;
    } else {
        const item = currentMmpDetails[selectedIndex];
        const price = parseFloat(item.idr_price_kg || item.harga_pch || item.harga_mkr || 0);
        const weight = parseFloat(item.weight_per_pcs || item.berat_part || 0);

        row.querySelector('[name="basic_price"]').value = 0;
        row.querySelector('[name="idr_price_kg"]').value = price;
        row.querySelector('[name="part_weight"]').value = weight;
        row.querySelector('[name="weight_per_pcs"]').value = weight;
    }
    calculateRow(row);
}

function populateMoldKeyOptions(selectElement, selectedValue = "") {
    selectElement.innerHTML = '<option value="">- Pilih Key -</option>';
    if (Array.isArray(moldMasterData)) {
        moldMasterData.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.key_name;
            opt.setAttribute('data-cost', item.cost_per_month || 0);
            opt.textContent = `${item.tipe_kategori ? '[' + item.tipe_kategori + '] ' : ''}${item.key_name}`;
            
            if (selectedValue && selectedValue === item.key_name) {
                opt.selected = true;
            }
            selectElement.appendChild(opt);
        });
    }
}

function ambilCostPackingOtomatis(baseIndex) {
    const partNameInput = document.getElementById('part_name_' + baseIndex);
    const rowElement = document.querySelector(`tr[data-row-id="${baseIndex}"]`);
    
    if (!rowElement || !partNameInput) return;
    
    const packingInput = rowElement.querySelector('input[name="packing"]');
    const transportInput = rowElement.querySelector('input[name="transport"]');
    const packingStandardIdInput = rowElement.querySelector('input[name="packing_standard_id"]');
    
    const cleanPartName = partNameInput.value.trim();
    if (!cleanPartName) return; 
    
    const partNameParam = encodeURIComponent(cleanPartName);
    
    fetch(`pack_trans.php?get_packing_cost_by_part=${partNameParam}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const targetPackingCost = parseFloat(res.cost_packing) || 0;
                const targetTransportCost = parseFloat(res.cost_transport) || 0;
                
                if (packingStandardIdInput) packingStandardIdInput.value = "0"; 
                if (packingInput) packingInput.value = targetPackingCost;
                if (transportInput) transportInput.value = targetTransportCost;
                
                calculateRow(rowElement);
            }
        })
        .catch(err => console.error("Gagal sinkronisasi data cost pack & trans:", err));
}

function initPackTransLiveSearch(row) {
    const baseIndex = row.getAttribute('data-row-id');
    const inputPartName = row.querySelector('.part-name-input');
    const resultsBox = row.querySelector(`#search_results_${baseIndex}`);
    const packingInput = row.querySelector('.packing-input');
    const transportInput = row.querySelector('.transport-input');

    if (!inputPartName || !resultsBox) return;

    let searchTimeout;

    inputPartName.addEventListener('input', function () {
        const query = this.value.trim();
        clearTimeout(searchTimeout);
        
        if (query.length < 1) {
            resultsBox.innerHTML = '';
            resultsBox.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`search_pack_trans.php?q=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) throw new Error("Network response was not ok");
                    return response.json();
                })
                .then(data => {
                    resultsBox.innerHTML = '';
                    if (data && data.length > 0) {
                        data.forEach(item => {
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'live-search-item';
                            itemDiv.innerHTML = `
                                <strong>${item.part_name}</strong>
                                <div style="font-size: 11px; color: #666;">
                                    Packing: Rp ${parseFloat(item.total_packing_pcs || 0).toLocaleString('id-ID')} | Trans: Rp ${parseFloat(item.transport_pcs_total || 0).toLocaleString('id-ID')}
                                </div>
                            `;

                            itemDiv.addEventListener('click', function () {
                                inputPartName.value = item.part_name;
                                if (packingInput) packingInput.value = item.total_packing_pcs;
                                if (transportInput) transportInput.value = item.transport_pcs_total;
                                resultsBox.innerHTML = '';
                                resultsBox.style.display = 'none';
                                calculateRow(row);
                            });

                            resultsBox.appendChild(itemDiv);
                        });
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.style.display = 'none';
                    }
                })
                .catch(err => console.error("Error fetching live search:", err));
        }, 200);
    });

    document.addEventListener('click', function (e) {
        if (!row.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });
}

function updateQuotationRowsFromPackTrans(result) {
    const targetPartName = String(result.part_name || '').trim().toLowerCase();
    if (!targetPartName) return;

    document.querySelectorAll('#calcRows tr').forEach(row => {
        const partName = row.querySelector('[name="part_name"]')?.value.trim().toLowerCase();
        if (partName !== targetPartName) return;

        const packingInput = row.querySelector('[name="packing"]');
        const transportInput = row.querySelector('[name="transport"]');
        if (packingInput) packingInput.value = result.total_packing_pcs ?? 0;
        if (transportInput) transportInput.value = result.transport_pcs_total ?? 0;
        calculateRow(row);
    });
}

function clearQuotationRowsFromPackTrans(partName) {
    const targetPartName = String(partName || '').trim().toLowerCase();
    if (!targetPartName) return;

    document.querySelectorAll('#calcRows tr').forEach(row => {
        const currentPartName = row.querySelector('[name="part_name"]')?.value.trim().toLowerCase();
        if (currentPartName !== targetPartName) return;

        const packingInput = row.querySelector('[name="packing"]');
        const transportInput = row.querySelector('[name="transport"]');
        if (packingInput) packingInput.value = 0;
        if (transportInput) transportInput.value = 0;
        calculateRow(row);
    });
}

const quotationPackTransChannel = new BroadcastChannel('matrix_update');
quotationPackTransChannel.onmessage = (event) => {
    const payload = event.data || {};
    if (payload.event === 'new_pack_trans' || payload.event === 'update_pack_trans') {
        updateQuotationRowsFromPackTrans(payload.data || {});
    }
    if (payload.event === 'delete_pack_trans') {
        clearQuotationRowsFromPackTrans(payload.data?.part_name);
    }
};

function onMoldKeyChange(selectElement) {
    const row = selectElement.closest('tr');
    if (!row) return;

    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const costMonthInput = row.querySelector('[name="cost_per_month"]');

    if (selectedOption && selectedOption.value !== "") {
        const costPerMonth = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
        if (costMonthInput) costMonthInput.value = costPerMonth;
    } else {
        if (costMonthInput) costMonthInput.value = 0;
    }

    calculateRow(row);
}

function updateOtherProcessVisibility() {
    let showCT = false;
    let showRate = false;
    $('.other_process_type').each(function() {
        let val = $(this).val();
        if (val === 'finishing' || val === 'inspection' || val === 'assembly') {
            showCT = true;
        } else if (val === 'annealing') {
            showCT = true;
            showRate = true;
        }
    });

    if (showCT) {
        $('.th-ct-other').show();
    } else {
        $('.th-ct-other').hide();
    }

    if (showRate) {
        $('.th-rate-annealing').show();
    } else {
        $('.th-rate-annealing').hide();
    }

    $('.other_process_type').each(function() {
        let row = $(this).closest('tr');
        let val = $(this).val();

        let tdCT = row.find('.td-ct-other');
        let tdRate = row.find('.td-rate-annealing');

        if (val === 'Tanpa Proses') {
            tdCT.toggle(showCT); 
            tdRate.toggle(showRate);
            row.find('.ct-other').val(0);
            row.find('.rate-annealing').val(0);
        } else if (val === 'finishing' || val === 'inspection' || val === 'assembly') {
            tdCT.show();
            tdRate.toggle(showRate);
            row.find('.rate-annealing').val(0);
        } else if (val === 'annealing') {
            tdCT.show();
            tdRate.show();
        }
    });
}

$(document).on('change', '.other_process_type', function() {
    updateOtherProcessVisibility();
    if (typeof calculateRow === "function") {
        calculateRow($(this).closest('tr'));
    }
});

function initAutocompleteMaterial(element) {
    if (!$.fn.autocomplete) {
        console.warn("jQuery UI Autocomplete belum dimuat.");
        return;
    }
    $(element).autocomplete({
        source: function(request, response) {
            var selectedMmp = $('select[name="mmp"]').val() || $('select[name="mmp_id"]').val() || $('#mmp_select').val() || '';

            $.ajax({
                url: "search_mmp.php",
                dataType: "json",
                data: { 
                    term: request.term,
                    mmp_id: selectedMmp 
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 1,
        select: function(event, ui) {
            var $row = $(this).closest('tr');
            $(this).val(ui.item.value);
            
            if (ui.item.idr_price_kg !== undefined) {
                $row.find('.idr_price_kg').val(ui.item.idr_price_kg);
            }
            
            if (ui.item.weight_per_pcs !== undefined && parseFloat(ui.item.weight_per_pcs) > 0) {
                var wPcs = parseFloat(ui.item.weight_per_pcs);
                $row.find('.weight_per_pcs').val(wPcs);
                $row.find('[name="part_weight"]').val(wPcs); 
            }

            if (typeof calculateRow === 'function') {
                calculateRow($row[0]); 
            }
            return false;
        }
    });
}

$(document).ready(function() {
    $(document).on('change input', '.idr_price_kg, .weight_per_pcs', function() {
        let $row = $(this).closest('tr');
        if (typeof calculateRow === 'function' && $row.length) {
            calculateRow($row[0]);
        }
    });

    $(document).on('focus', '.material_spec_input', function() {
        if (!$(this).data('autocomplete-bound')) {
            initAutocompleteMaterial(this);
            $(this).data('autocomplete-bound', true);
        }
    });
});