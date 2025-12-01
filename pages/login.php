<?php
require_once '../includes/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$input, $input]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Username atau email tidak ditemukan.";
    } elseif (!$user['is_active']) {
        $error = "Akun belum diaktivasi.";
    } elseif (!password_verify($password, $user['password'])) {
        $error = "Password salah.";
    } else {
        $_SESSION['user_id'] = $user['id'];

        $is_admin = ($user['username'] === 'admin');
        $_SESSION['is_admin'] = $is_admin ? 1 : 0;

        if ($is_admin) {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: profile.php');
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - RentalMobil.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <h2 class="text-center mb-4">Login</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username atau Email" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
        <div class="text-center mt-3">
            Belum punya akun? <a href="register.php">Daftar</a>
        </div>
    </form>
</div>
</body>
</html>