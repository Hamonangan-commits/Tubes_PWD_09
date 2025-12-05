<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: ../pages/profile.php');
    exit();
}

// Nonaktifkan/aktifkan pengguna (via GET)
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id == 1) {
        die("Tidak bisa mengubah akun admin utama.");
    }
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        $new_status = $user['is_active'] ? 0 : 1;
        $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$new_status, $id]);
        header('Location: manage_users.php');
        exit();
    }
}

// Hapus pengguna (via GET)
if (isset($_GET['hapus']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id == 1) {
        die("Tidak bisa menghapus akun admin utama.");
    }
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header('Location: manage_users.php');
    exit();
}

// Ambil semua pengguna
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Kelola Pengguna</title>
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
    <a href="dashboard.php" class="nav-item">
    <span class="icon">📊</span>
      Dashboard <span class="badge">1</span>
    </a>
    <a href="manage_mobil.php" class="nav-item">
      <span class="icon">🚗</span>
      Kelola Mobil <span class="badge">6</span>
    </a>
    <a href="manage_transaksi.php" class="nav-item">
      <span class="icon">📅</span>
      Transaksi <span class="badge">3</span>
    </a>
    <div class="nav-item active">
      <span class="icon">👥</span>
      Kelola Pengguna <span class="badge">5</span>
    </div>
  </div>

  <!-- Konten Utama -->
  <div class="container">
    <h2 class="section-title">Daftar Pengguna</h2>

    <!-- Tabel Pengguna -->
    <div class="table-container">
      <div class="table-header">
        <div>ID</div>
        <div>Nama</div>
        <div>Email</div>
        <div>Username</div>
        <div>Status</div>
        <div>Aksi</div>
      </div>

      <?php if (empty($users)): ?>
        <div class="table-row">
          <div class="table-cell" colspan="6" style="text-align: center; padding: 20px; color: #6c757d;">
            Tidak ada pengguna.
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
          <div class="table-row">
            <div class="table-cell"><?= htmlspecialchars($u['id']) ?></div>
            <div class="table-cell"><?= htmlspecialchars($u['nama']) ?></div>
            <div class="table-cell"><?= htmlspecialchars($u['email']) ?></div>
            <div class="table-cell"><?= htmlspecialchars($u['username']) ?></div>
            <div class="table-cell">
              <?php if ($u['id'] == 1): ?>
                <span class="status-badge aktif">Admin Utama</span>
              <?php else: ?>
                <span class="status-badge <?= $u['is_active'] ? 'aktif' : 'nonaktif' ?>">
                  <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="table-cell action-group">
              <?php if ($u['id'] != 1): ?>
                <a href="?toggle=1&id=<?= $u['id'] ?>" 
                   class="btn-update <?= $u['is_active'] ? 'btn-yellow' : 'btn-green' ?>"
                   onclick="return confirm('<?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?> pengguna ini?')">
                  <?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                </a>
                <a href="?hapus=1&id=<?= $u['id'] ?>" 
                   class="btn-update btn-red"
                   onclick="return confirm('Hapus pengguna ini?')">
                  Hapus
                </a>
              <?php else: ?>
                <span style="color: #6c757d; font-size: 12px;">Tidak bisa diubah</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Help Button -->
  <div class="help-button">?</div>

</body>
</html>