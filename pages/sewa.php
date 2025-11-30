<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_login(); 

// Ambil mobil tersedia
$mobil = $pdo->query("SELECT * FROM mobil WHERE status = 'tersedia'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobil_id = $_POST['mobil_id'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $lokasi = $_POST['lokasi_jemput'];

    // Simpan transaksi
    $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, mobil_id, tgl_mulai, tgl_selesai, lokasi_jemput) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $mobil_id, $tgl_mulai, $tgl_selesai, $lokasi]);

    // Update status mobil
    $pdo->prepare("UPDATE mobil SET status = 'disewa' WHERE id = ?")->execute([$mobil_id]);

    header('Location: transaksi.php?pesan=success');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sewa Mobil - RentalMobil.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">RentalMobil.id</a>
        <div class="navbar-nav">
            <a class="nav-link" href="profile.php">Profil</a>
            <a class="nav-link" href="transaksi.php">Transaksi</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Sewa Mobil</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Pilih Mobil</label>
            <select name="mobil_id" class="form-control" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($mobil as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= $m['merek'] ?> <?= $m['model'] ?> (Rp <?= number_format($m['harga_per_hari']) ?>/hari)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Lokasi Jemput</label>
            <input type="text" id="lokasi_jemput" name="lokasi_jemput" class="form-control" placeholder="Klik tombol di bawah untuk isi otomatis" required>
            <button type="button" class="btn btn-secondary mt-2" onclick="getLocation()">Isi Lokasi Otomatis</button>
        </div>
        <button type="submit" class="btn btn-success">Sewa Sekarang</button>
    </form>
</div>

<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            document.getElementById('lokasi_jemput').value = 
                'Lat: ' + pos.coords.latitude + ', Lng: ' + pos.coords.longitude;
        });
    } else {
        alert('Geolocation tidak didukung.');
    }
}
</script>
</body>
</html>