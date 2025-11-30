<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/mailer.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Cek duplikat (untuk AJAX, tapi tetap validasi server)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        $message = "Email atau username sudah digunakan.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, username, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $username, $hashed_password]);
        $user_id = $pdo->lastInsertId();

        // Generate & simpan token aktivasi
        $token = generate_token();
        $stmt = $pdo->prepare("INSERT INTO user_activations (user_id, token) VALUES (?, ?)");
        $stmt->execute([$user_id, $token]);

        if (send_activation_email($email, $token)) {
            $message = "Registrasi berhasil! Silakan cek email untuk aktivasi.";
        } else {
            $message = "Registrasi berhasil, tapi gagal kirim email aktivasi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - RentalMobil.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <h2 class="text-center mb-4">Daftar Akun</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
        </div>
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary w-100">Daftar</button>
        <div class="text-center mt-3">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>
    </form>
</div>
</body>
</html>