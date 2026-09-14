<?php
session_start();

include 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Quotation</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css">
</head>

<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-title">
            Welcome to QuotationApp
        </div>
        <div class="dashboard-desc">
            Selamat datang di sistem perhitungan quotation <strong>PT Citra Plastik Makmur</strong>.
            <br><br>
            Solusi penawaran harga yang memudahkan bisnis memberikan estimasi biaya layanan atau produk secara instan kepada calon pembeli.
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <div class="card-title">
                    Quotation
                </div>
                <div class="card-desc">
                    Buat dan kelola quotation penawaran harga
                </div>
            </div>
            <div class="card">
                <div class="card-title">
                    Packing Transport
                </div>
                <div class="card-desc">
                    Kelola data transportasi dan pengemasan
                </div>
            </div>
            <div class="card">
                <div class="card-title">
                    Packing Cost
                </div>
                <div class="card-desc">
                    Hitung biaya packing secara detail
                </div>
            </div>
            <div class="card">
                <div class="card-title">
                    Rate
                </div>
                <div class="card-desc">
                    Atur dan lihat rate harga terkini
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loginModal">
    <form class="login-form" id="loginForm">
        <h3>Login</h3>
        <input type="text" id="username" placeholder="Username" required>
        <input type="password" id="password" placeholder="Password" required>
        <button type="submit">
            Login
        </button>
        <span class="error-message" id="loginError"></span>
    </form>
</div>
<script>

const loginBtn = document.getElementById('loginBtn');
const loginModal = document.getElementById('loginModal');
const loginForm = document.getElementById('loginForm');
const loginError = document.getElementById('loginError');

if(loginBtn){

    loginBtn.onclick = function(){
        loginModal.style.display = 'flex';
    };

}

if(loginModal){
    loginModal.onclick = function(e){
        if(e.target === loginModal){
            loginModal.style.display = 'none';
        }
    };
}

if(loginForm){
    loginForm.onsubmit = async function(e){
        e.preventDefault();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        loginError.style.display = 'none';
        try{
            const response = await fetch('login.php', {
                method:'POST',
                headers:{
                    'Content-Type':'application/x-www-form-urlencoded'
                },
                body:`username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            });

            const result = await response.json();
            if(result.success){
                location.reload();
            }else{
                loginError.innerText = result.message || 'Login gagal';
                loginError.style.display = 'block';
            }

        }catch(error){
            loginError.innerText = 'Server error';
            loginError.style.display = 'block';
        }
    };
}

</script>
</body>
</html>