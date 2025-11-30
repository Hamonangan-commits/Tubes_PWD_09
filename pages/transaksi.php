<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_login(); 

// Handle Hapus (jika status = aktif)
if (isset($_GET['hapus']) && isset($_GET['id'])) {
    $id = $_GET['id'];
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

// Ambil transaksi pengguna
$stmt = $pdo->prepare("
    SELECT t.*, m.merek, m.model 
    FROM transaksi t 
    LEFT JOIN mobil m ON t.mobil_id = m.id 
    WHERE t.user_id = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transaksi = $stmt->fetchAll();
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
    <div class="...">...</div>
</nav>

<div class="container mt-4">
    <h2>Riwayat Transaksi</h2>
    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-success">Transaksi berhasil dibatalkan.</div>
    <?php endif; ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Mobil</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transaksi as $t): ?>
                <tr>
                    <td><?= $t['merek'] . ' ' . $t['model'] ?? 'Mobil dihapus' ?></td>
                    <td><?= $t['tgl_mulai'] ?> s/d <?= $t['tgl_selesai'] ?></td>
                    <td><?= htmlspecialchars($t['lokasi_jemput']) ?></td>
                    <td><?= ucfirst($t['status']) ?></td>
                    <td>
                        <?php if ($t['status'] === 'aktif'): ?>
                            <a href="?hapus=1&id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Batalkan sewa?')">Batalkan</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="sewa.php" class="btn btn-primary">Sewa Mobil Baru</a>
</div>
</body>
</html>