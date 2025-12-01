<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_login();

$is_admin = is_admin();

// Ambil transaksi
if ($is_admin) {
    $stmt = $pdo->prepare("
        SELECT t.*, u.nama as nama_user, m.merek, m.model 
        FROM transaksi t 
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN mobil m ON t.mobil_id = m.id 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT t.*, m.merek, m.model 
        FROM transaksi t 
        LEFT JOIN mobil m ON t.mobil_id = m.id 
        WHERE t.user_id = ? 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
}
$transaksi = $stmt->fetchAll();

// Handle hapus (hanya untuk user biasa)
if (!$is_admin && isset($_GET['hapus']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT status, mobil_id FROM transaksi WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $trans = $stmt->fetch();
    if ($trans && $trans['status'] === 'aktif') {
        $pdo->prepare("DELETE FROM transaksi WHERE id = ?")->execute([$id]);
        $pdo->prepare("UPDATE mobil SET status = 'tersedia' WHERE id = ?")->execute([$trans['mobil_id']]);
        header('Location: transaksi.php?pesan=dihapus');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi - RentalMobil.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="profile.php">RentalMobil.id</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="sewa.php">Sewa Mobil</a>
            <a class="nav-link" href="transaksi.php">Transaksi</a>
            <?php if ($is_admin): ?>
                <a class="nav-link" href="../admin/dashboard.php">Admin</a>
            <?php endif; ?>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2><?= $is_admin ? 'Semua Transaksi' : 'Riwayat Transaksi Saya' ?></h2>
    
    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-success">Transaksi berhasil dibatalkan.</div>
    <?php endif; ?>
    
    <?php if (empty($transaksi)): ?>
        <div class="alert alert-info">Tidak ada transaksi.</div>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <?php if ($is_admin): ?>
                        <th>Pengguna</th>
                    <?php endif; ?>
                    <th>Mobil</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <?php if (!$is_admin): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi as $t): ?>
                    <tr>
                        <?php if ($is_admin): ?>
                            <td><?= htmlspecialchars($t['nama_user'] ?? 'User dihapus') ?></td>
                        <?php endif; ?>
                        <td><?= ($t['merek'] && $t['model']) ? htmlspecialchars($t['merek'] . ' ' . $t['model']) : '<em>Mobil dihapus</em>' ?></td>
                        <td><?= htmlspecialchars($t['tgl_mulai']) ?> s/d <?= htmlspecialchars($t['tgl_selesai']) ?></td>
                        <td><?= htmlspecialchars($t['lokasi_jemput']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($t['status'])) ?></td>
                        <?php if (!$is_admin && $t['status'] === 'aktif'): ?>
                            <td>
                                <a href="?hapus=1&id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Batalkan sewa?')">Batalkan</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="sewa.php" class="btn btn-primary">Sewa Mobil Baru</a>
    
    <!-- 🔥 TOMBOL INI HANYA UNTUK ADMIN -->
    <?php if ($is_admin): ?>
        <a href="../admin/manage_transaksi.php" class="btn btn-secondary">Kelola di Admin Panel</a>
    <?php endif; ?>
</div>
</body>
</html>