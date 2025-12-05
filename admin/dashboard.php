<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: ../pages/profile.php');
    exit();
}

// Ambil statistik
$total_mobil = $pdo->query("SELECT COUNT(*) FROM mobil")->fetchColumn();
$total_pengguna = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_transaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();

// Ambil pendapatan (jika kolom tanggal ada)
$total_pendapatan = 0;
$stmt = $pdo->query("
    SELECT SUM(m.harga_per_hari * (DATEDIFF(t.tgl_selesai, t.tgl_mulai) + 1)) 
    FROM transaksi t
    JOIN mobil m ON t.mobil_id = m.id
    WHERE t.status = 'selesai'
");
$result = $stmt->fetchColumn();
if ($result) {
    $total_pendapatan = (int)$result;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Dashboard</title>
  <!-- Link ke CSS custom Anda -->
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <!-- Header -->
  <div class="header">
    <a href="dashboard.php" class="logo" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;">
      <div class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
      </div>
      <div>
        <h1>Admin Panel</h1>
        <p>RentalKu Management</p>
      </div>
    </a>
    <div class="user-actions">
      <div class="user-info">
        <span>Administrator</span>
        <p>Admin User</p>
      </div>
      <a href="../pages/logout.php" class="btn-logout">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
          <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
        </svg>
        Keluar
      </a>
    </div>
  </div>

  <!-- Navbar -->
  <div class="navbar">
    <div class="nav-item active">
      <span class="icon">📊</span>
      Dashboard <span class="badge">1</span>
    </div>
    <a href="manage_mobil.php" class="nav-item">
      <span class="icon">🚗</span>
      Kelola Mobil <span class="badge"><?= $total_mobil ?></span>
    </a>
    <a href="manage_transaksi.php" class="nav-item">
      <span class="icon">📅</span>
      Transaksi <span class="badge"><?= $total_transaksi ?></span>
    </a>
    <a href="manage_users.php" class="nav-item">
      <span class="icon">👥</span>
      Kelola Pengguna <span class="badge"><?= $total_pengguna ?></span>
    </a>
  </div>

  <!-- Konten Utama -->
  <div class="container">
    <h2 class="section-title">Dashboard Admin</h2>
    <p class="section-subtitle">Ringkasan aktivitas sistem rental mobil Anda</p>

    <!-- Statistik Cards -->
    <div class="stats-cards">
      <div class="stat-card purple">
        <div class="info">
          <div class="label">Total Mobil</div>
          <div class="value"><?= $total_mobil ?></div>
        </div>
        <div class="icon">🚗</div>
      </div>
      <div class="stat-card blue">
        <div class="info">
          <div class="label">Total Pengguna</div>
          <div class="value"><?= $total_pengguna ?></div>
        </div>
        <div class="icon">👥</div>
      </div>
      <div class="stat-card green">
        <div class="info">
          <div class="label">Total Transaksi</div>
          <div class="value"><?= $total_transaksi ?></div>
        </div>
        <div class="icon">📅</div>
      </div>
      <div class="stat-card purple">
        <div class="info">
          <div class="label">Pendapatan (Rp)</div>
          <div class="value"><?= number_format($total_pendapatan, 0, ',', '.') ?></div>
        </div>
        <div class="icon">💰</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="table-container" style="margin-top: 24px;">
      <div style="padding: 16px;">
        <h3 class="section-title" style="font-size: 18px; margin-bottom: 12px;">Aksi Cepat</h3>
        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
          <a href="manage_mobil.php" class="btn-add-mobil" style="text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Kelola Mobil
          </a>
          <a href="manage_users.php" class="btn-add-mobil" style="background: #28a745; text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Kelola Pengguna
          </a>
          <a href="manage_transaksi.php" class="btn-add-mobil" style="background: #17a2b8; text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Kelola Transaksi
          </a>
          <a href="generate_pdf.php" class="btn-add-mobil" style="background: #ffc107; color: #212529; text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Laporan PDF
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Help Button -->
  <div class="help-button">?</div>

</body>
</html>