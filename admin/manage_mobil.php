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
        $stmt = $pdo->prepare("INSERT INTO mobil (merek, model, harga_per_hari) VALUES (?, ?, ?)");
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
    <meta charset="UTF-8">
    <title>Kelola Mobil - Admin</title>
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
    <h2>Kelola Mobil</h2>

    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_GET['pesan']) ?></div>
    <?php endif; ?>

    <!-- Form Tambah Mobil -->
    <div class="card mb-4">
        <div class="card-header">Tambah Mobil Baru</div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="merek" class="form-control" placeholder="Merek (e.g., Toyota)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="model" class="form-control" placeholder="Model (e.g., Avanza)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="harga" class="form-control" placeholder="Harga/Hari" min="1" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" name="tambah" class="btn btn-success">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Mobil -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Mobil</th>
                <th>Harga/Hari</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mobil as $m): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= htmlspecialchars($m['merek']) ?> <?= htmlspecialchars($m['model']) ?></td>
                    <td>Rp <?= number_format($m['harga_per_hari']) ?></td>
                    <td>
                        <span class="badge bg-<?= $m['status'] === 'tersedia' ? 'success' : 'warning' ?>">
                            <?= ucfirst($m['status']) ?>
                        </span>
                    </td>
                    <td>
                        <!-- Edit (modal atau form inline sederhana) -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <input type="text" name="merek" value="<?= htmlspecialchars($m['merek']) ?>" class="form-control d-inline" style="width:80px;" required>
                            <input type="text" name="model" value="<?= htmlspecialchars($m['model']) ?>" class="form-control d-inline" style="width:80px;" required>
                            <input type="number" name="harga" value="<?= $m['harga_per_hari'] ?>" class="form-control d-inline" style="width:100px;" min="1" required>
                            <button type="submit" name="edit" class="btn btn-sm btn-primary">Simpan</button>
                        </form>
                        <a href="?hapus=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus mobil ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>