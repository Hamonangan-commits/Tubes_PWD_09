<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: ../pages/profile.php');
    exit();
}

$message = '';

// Tambah mobil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $merek = trim($_POST['merek']);
    $model = trim($_POST['model']);
    $harga = (int)$_POST['harga'];
    if ($merek && $model && $harga > 0) {
        $stmt = $pdo->prepare("INSERT INTO mobil (merek, model, harga_per_hari, status) VALUES (?, ?, ?, 'tersedia')");
        $stmt->execute([$merek, $model, $harga]);
        $message = "Mobil berhasil ditambahkan.";
    }
}

// Edit mobil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $merek = trim($_POST['merek']);
    $model = trim($_POST['model']);
    $harga = (int)$_POST['harga'];
    if ($merek && $model && $harga > 0) {
        $stmt = $pdo->prepare("UPDATE mobil SET merek = ?, model = ?, harga_per_hari = ? WHERE id = ?");
        $stmt->execute([$merek, $model, $harga, $id]);
        $message = "Mobil berhasil diperbarui.";
    }
}

// Hapus mobil
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Cek apakah mobil sedang disewa
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE mobil_id = ? AND status = 'aktif'");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("DELETE FROM mobil WHERE id = ?")->execute([$id]);
        $message = "Mobil berhasil dihapus.";
    } else {
        $message = "Mobil sedang disewa, tidak bisa dihapus.";
    }
    header('Location: manage_mobil.php?pesan=' . urlencode($message));
    exit();
}

$mobil = $pdo->query("SELECT * FROM mobil ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Kelola Mobil</title>
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
    <div class="nav-item active">
      <span class="icon">🚗</span>
      Kelola Mobil <span class="badge"><?= count($mobil) ?></span>
    </div>
    <a href="manage_transaksi.php" class="nav-item">
      <span class="icon">📅</span>
      Transaksi <span class="badge">3</span>
    </a>
    <a href="manage_users.php" class="nav-item">
      <span class="icon">👥</span>
      Kelola Pengguna <span class="badge">5</span>
    </a>
  </div>

  <!-- Konten Utama -->
  <div class="container">
    <h2 class="section-title">Kelola Mobil</h2>

    <?php if (isset($_GET['pesan'])): ?>
      <div style="background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($_GET['pesan']) ?>
      </div>
    <?php endif; ?>

    <!-- Form Tambah Mobil Baru -->
    <div class="table-container" style="margin-bottom: 20px;">
      <div style="padding: 16px; background: #f8f9fa; border-radius: 12px;">
        <h5 style="margin-bottom: 16px; font-weight: 600;">Tambah Mobil Baru</h5>
        <form method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
          <div>
            <label style="font-size: 12px; color: #6c757d;">Merek (e.g., Toyota)</label>
            <input type="text" name="merek" placeholder="Toyota" class="form-input" required>
          </div>
          <div>
            <label style="font-size: 12px; color: #6c757d;">Model (e.g., Avanza)</label>
            <input type="text" name="model" placeholder="Avanza" class="form-input" required>
          </div>
          <div>
            <label style="font-size: 12px; color: #6c757d;">Harga/Hari</label>
            <input type="number" name="harga" placeholder="350000" min="1" class="form-input" required>
          </div>
          <div>
            <button type="submit" name="tambah" class="btn-add">Tambah</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel Mobil -->
    <div class="table-container">
      <div class="table-header">
        <div>ID</div>
        <div>Mobil</div>
        <div>Harga/Hari</div>
        <div>Status</div>
        <div>Aksi</div>
      </div>

      <?php if (empty($mobil)): ?>
        <div class="table-row">
          <div class="table-cell" colspan="5" style="text-align: center; padding: 20px; color: #6c757d;">
            Belum ada mobil.
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($mobil as $m): ?>
          <div class="table-row">
            <div class="table-cell"><?= htmlspecialchars($m['id']) ?></div>
            <div class="table-cell">
              <?= htmlspecialchars($m['merek']) ?> <?= htmlspecialchars($m['model']) ?>
            </div>
            <div class="table-cell">Rp <?= number_format($m['harga_per_hari'], 0, ',', '.') ?></div>
            <div class="table-cell">
              <span class="status-badge <?= $m['status'] === 'tersedia' ? 'aktif' : 'nonaktif' ?>">
                <?= ucfirst($m['status']) ?>
              </span>
            </div>
            <div class="table-cell action-group">
              <form method="POST" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                <input type="text" name="merek" value="<?= htmlspecialchars($m['merek']) ?>" class="form-input-sm" style="width: 80px;" required>
                <input type="text" name="model" value="<?= htmlspecialchars($m['model']) ?>" class="form-input-sm" style="width: 80px;" required>
                <input type="number" name="harga" value="<?= $m['harga_per_hari'] ?>" class="form-input-sm" style="width: 100px;" min="1" required>
                <button type="submit" name="edit" class="btn-update">Simpan</button>
                <a href="?hapus=<?= $m['id'] ?>" class="btn-delete" onclick="return confirm('Hapus mobil ini?')">Hapus</a>
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