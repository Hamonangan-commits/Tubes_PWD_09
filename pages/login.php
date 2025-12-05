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
        $error = "Akun belum diaktivasi. Silakan cek email Anda.";
    } elseif (!password_verify($password, $user['password'])) {
        $error = "Password salah.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $is_admin = (strtolower(trim($user['email'])) === 'admin@rentalmobil.id');
        $_SESSION['is_admin'] = $is_admin ? 1 : 0;

        if ($is_admin) {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: sewa.php');
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff; /* Light blue background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 400px;
            margin: 5rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
            background: white;
        }
        .logo {
            width: 60px;
            height: 60px;
            background: #0d6efd; /* Biru utama */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }
        .btn-primary {
            background-color: #0d6efd; /* Biru utama */
            border-color: #0d6efd;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #0b5ed7; /* Biru hover */
            border-color: #0b5ed7;
        }
        .text-link {
            color: #0d6efd; /* Biru link */
            text-decoration: none;
        }
        .text-link:hover {
            text-decoration: underline;
        }
        h5 {
            color: #0d6efd; /* Judul "RentalKu" biru */
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-card">
    <div class="logo">
        <img src="../assets/images/logo.png" alt="RentalKu Logo" style="width: 50px; height: auto;">
    </div>
        <h5 class="text-center mb-4">RentalKu</h5>
        <p class="text-center text-muted mb-4">Masuk ke akun Anda</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Masuk</button>
            <div class="text-center">
                <a href="register.php" class="text-link">Belum punya akun? Daftar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"></script>
</body>
</html>