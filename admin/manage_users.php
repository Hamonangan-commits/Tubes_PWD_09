<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!is_admin()) {
    header('Location: ../pages/profile.php');
    exit();
}

// Nonaktifkan/aktifkan pengguna
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

// Hapus pengguna (kecuali admin utama)
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
    <meta charset="UTF-8">
    <title>Kelola Pengguna - Admin</title>
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
    <h2>Daftar Pengguna</h2>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Username</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nama']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td>
                        <?php if ($u['id'] == 1): ?>
                            <span class="badge bg-success">Admin Utama</span>
                        <?php else: ?>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['id'] != 1): // bukan admin utama ?>
                            <a href="?toggle=1&id=<?= $u['id'] ?>" class="btn btn-sm <?= $u['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                <?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </a>
                            <a href="?hapus=1&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
                        <?php else: ?>
                            <em>Tidak bisa diubah</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>