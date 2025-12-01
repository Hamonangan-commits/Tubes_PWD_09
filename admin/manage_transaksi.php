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

// Ambil data transaksi
$stmt = $pdo->prepare("
    SELECT t.*, u.nama as nama_user, u.email, m.merek, m.model
    FROM transaksi t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN mobil m ON t.mobil_id = m.id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$transaksi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Transaksi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
        <a class="btn btn-outline-light" href="../pages/logout.php">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <h2>Manajemen Transaksi</h2>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Pengguna</th>
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
                    <td><?= $t['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($t['nama_user']) ?><br>
                        <small><?= htmlspecialchars($t['email']) ?></small>
                    </td>
                    <td>
                        <?= ($t['merek'] && $t['model']) 
                            ? htmlspecialchars($t['merek'] . ' ' . $t['model']) 
                            : '<em>Mobil dihapus</em>' ?>
                    </td>
                    <td><?= htmlspecialchars($t['tgl_mulai']) ?><br>s/d<br><?= htmlspecialchars($t['tgl_selesai']) ?></td>
                    <td><?= htmlspecialchars($t['lokasi_jemput']) ?></td>
                    <td>
                        <span class="badge bg-<?= 
                            $t['status'] === 'aktif' ? 'primary' : 
                            ($t['status'] === 'selesai' ? 'success' : 'danger')
                        ?>"><?= ucfirst(htmlspecialchars($t['status'])) ?></span>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <select name="status" class="form-select form-select-sm d-inline" style="width:auto;" required>
                                <option value="aktif" <?= $t['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="selesai" <?= $t['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="dibatalkan" <?= $t['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-outline-secondary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>