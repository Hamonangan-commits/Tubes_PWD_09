<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: ../pages/profile.php');
    exit();
}

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    
    if (in_array($status, ['aktif', 'selesai', 'dibatalkan'])) {
        // Ambil data transaksi
        $stmt = $pdo->prepare("SELECT mobil_id, status FROM transaksi WHERE id = ?");
        $stmt->execute([$id]);
        $trans = $stmt->fetch();

        if ($trans) {
            // Jika status berubah dari 'aktif' ke 'selesai/dibatalkan', kembalikan mobil
            if ($trans['status'] === 'aktif' && in_array($status, ['selesai', 'dibatalkan'])) {
                $pdo->prepare("UPDATE mobil SET status = 'tersedia' WHERE id = ?")
                    ->execute([$trans['mobil_id']]);
            }
            // Update status transaksi
            $pdo->prepare("UPDATE transaksi SET status = ? WHERE id = ?")
                ->execute([$status, $id]);
        }
        
        // Redirect kembali ke halaman ini
        header('Location: manage_transaksi.php');
        exit();
    }
}

$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
}

// Ambil data transaksi — PAKAI t.* SESUAI PERMINTAAN ANDA
$stmt = $pdo->prepare("
    SELECT t.*, u.nama as nama_user, u.email, m.merek, m.model
    FROM transaksi t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN mobil m ON t.mobil_id = m.id
    ORDER BY t.created_at DESC
");
$searchTerm = "%$search%";
$stmt->execute();
$transaksi = $stmt->fetchAll();

// Hitung statistik
$stats = [
    'aktif' => 0,
    'selesai' => 0,
    'total' => count($transaksi)
];

foreach ($transaksi as $t) {
    if ($t['status'] === 'aktif') $stats['aktif']++;
    if ($t['status'] === 'selesai') $stats['selesai']++;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - RentalKu Management</title>
  <!-- Link ke CSS di folder lain -->
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
    <div class="nav-item active">
      <span class="icon">📅</span>
      Transaksi <span class="badge">3</span>
    </div>
    <a href="manage_users.php" class="nav-item">
      <span class="icon">👥</span>
      Kelola Pengguna <span class="badge">5</span>
    </a>
  </div>

  <!-- Konten Utama -->
  <div class="container">
    <h2 class="section-title">Manajemen Transaksi</h2>
    <p class="section-subtitle">Kelola semua transaksi rental mobil</p>

    <!-- Statistik Cards -->
    <div class="stats-cards">
      <div class="stat-card green">
        <div class="info">
          <div class="label">Sewa Aktif</div>
          <div class="value"><?= $stats['aktif'] ?></div>
        </div>
        <div class="icon">✅</div>
      </div>
      <div class="stat-card blue">
        <div class="info">
          <div class="label">Selesai</div>
          <div class="value"><?= $stats['selesai'] ?></div>
        </div>
        <div class="icon">✅</div>
      </div>
      <div class="stat-card purple">
        <div class="info">
          <div class="label">Total Transaksi</div>
          <div class="value"><?= $stats['total'] ?></div>
        </div>
        <div class="icon">✅</div>
      </div>
    </div>

    <!-- Search Bar -->
    <div style="margin-bottom: 20px;">
      <form method="GET" style="display: flex; gap: 12px; align-items: center;">
        <div style="flex: 1; position: relative;">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d;">
            <path d="M11.742 10.344a6.5 6.5 0 1 1-1.397 1.398h-.001c1.18.22 1.832 1.12 1.832 2.082 0 1.05-.745 1.832-1.832 1.832-1.05 0-1.832-.782-1.832-1.832 0-1.05.782-1.832 1.832-1.832 1.05 0 1.832.782 1.832 1.832m6.5 0c0 .552-.448 1-1 1H9c-.552 0-1-.448-1-1s.448-1 1-1h5c.552 0 1 .448 1 1z"/>
          </svg>
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari berdasarkan nama pengguna, mobil, email, atau lokasi" style="padding: 12px 12px 12px 40px; border: 1px solid #ced4da; border-radius: 8px; font-size: 14px; width: 100%; outline: none;" />
        </div>
        <button type="submit" class="btn-add-mobil" style="padding: 12px 20px;">Cari</button>
      </form>
    </div>

    <!-- Tabel Transaksi -->
    <div class="table-container">
      <div class="table-header">
        <div>ID</div>
        <div>Pengguna</div>
        <div>Mobil</div>
        <div>Tanggal</div>
        <div>Lokasi</div>
        <div>Status</div>
        <div>Aksi</div>
      </div>

      <?php if (empty($transaksi)): ?>
        <div class="table-row">
          <div class="table-cell" colspan="7" style="text-align: center; padding: 20px; color: #6c757d;">
            Tidak ada transaksi.
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($transaksi as $t): ?>
          <div class="table-row">
            <div class="table-cell"><?= htmlspecialchars($t['id']) ?></div>
            <div class="table-cell user-info">
              <span class="name"><?= htmlspecialchars($t['nama_user'] ?? '–') ?></span>
              <span class="email"><?= htmlspecialchars($t['email'] ?? '–') ?></span>
            </div>
            <div class="table-cell">
              <?= htmlspecialchars(($t['merek'] ?? '') . ' ' . ($t['model'] ?? '')) ?>
            </div>
            <div class="table-cell dates">
              <span>
                <?= !empty($t['tgl_mulai']) ? date('Y-m-d', strtotime($t['tgl_mulai'])) : '–' ?>
              </span>
              <span>s/d</span>
              <span>
                <?= !empty($t['tgl_selesai']) ? date('Y-m-d', strtotime($t['tgl_selesai'])) : '–' ?>
              </span>
            </div>
            <div class="table-cell location">
              Lat: <?= number_format($t['lat'] ?? 0, 4) ?>,
              Lng: <?= number_format($t['lng'] ?? 0, 4) ?>
            </div>
            <div class="table-cell">
              <?php
                $status_class = match($t['status'] ?? 'aktif') {
                  'aktif' => 'aktif',
                  'selesai' => 'selesai',
                  'dibatalkan' => 'selesai',
                  default => 'aktif'
                };
              ?>
              <span class="status-badge <?= $status_class ?>">
                <?= ucfirst($t['status'] ?? 'Aktif') ?>
              </span>
            </div>
            <div class="table-cell action-group">
              <form method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <select name="status" class="select-status">
                  <option value="aktif" <?= ($t['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                  <option value="selesai" <?= ($t['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                  <option value="dibatalkan" <?= ($t['status'] ?? '') === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
                <button type="submit" name="update_status" value="1" class="btn-update">Update</button>
              </form>
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