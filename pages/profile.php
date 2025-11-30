<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

redirect_login();

$user = get_user_by_id($pdo, $_SESSION['user_id']);
$photo_url = 'assets/uploads/profiles/' . $user['foto'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    if ($filename = upload_profile_photo($_FILES['foto'], $user['id'])) {
        $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?")->execute([$filename, $user['id']]);
        $user['foto'] = $filename;
        $photo_url = 'assets/uploads/profiles/' . $filename;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil - RentalMobil.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">RentalMobil.id</a>
        <div class="navbar-nav">
            <a class="nav-link" href="sewa.php">Sewa Mobil</a>
            <a class="nav-link" href="transaksi.php">Transaksi</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4 text-center">
            <img src="<?= htmlspecialchars($photo_url) ?>" alt="Foto Profil" class="img-fluid rounded-circle mb-3" width="150">
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="foto" accept="image/*" class="form-control mb-2" onchange="this.form.submit()">
            </form>
        </div>
        <div class="col-md-8">
            <h3>Profil Saya</h3>
            <p><strong>Nama:</strong> <?= htmlspecialchars($user['nama']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <a href="transaksi.php" class="btn btn-outline-primary">Lihat Transaksi</a>
        </div>
    </div>
</div>
</body>
</html>