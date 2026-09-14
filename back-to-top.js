(function () {
    if (document.getElementById('backToTopButton')) return;

    const button = document.createElement('button');
    button.id = 'backToTopButton';
    button.className = 'back-to-top-button';
    button.type = 'button';
    button.setAttribute('aria-label', 'Kembali ke atas');
    button.title = 'Kembali ke atas';
    button.innerHTML = '&#8593;';
    document.body.appendChild(button);

    const toggleButton = () => {
        button.classList.toggle('is-visible', window.scrollY > 300);
    };

    window.addEventListener('scroll', toggleButton, { passive: true });
    button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    toggleButton();
})();
