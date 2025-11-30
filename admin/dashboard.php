<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: ../pages/login.php?error=unauthorized');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - RentalMobil.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">Admin Dashboard</span>
        <a class="btn btn-outline-light" href="../pages/logout.php">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <h2>Selamat Datang, Admin</h2>
    <div class="row">
        <div class="col"><a href="manage_mobil.php" class="btn btn-primary w-100">Kelola Mobil</a></div>
        <div class="col"><a href="manage_users.php" class="btn btn-success w-100">Kelola Pengguna</a></div>
        <div class="col"><a href="manage_transaksi.php" class="btn btn-info w-100">Transaksi</a></div>
        <div class="col"><a href="generate_pdf.php" class="btn btn-warning w-100">Laporan PDF</a></div>
    </div>
</div>
</body>
</html>