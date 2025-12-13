<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/mailer.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($nama) || empty($email) || empty($username) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } else {
        // Cek duplikat
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = "Email atau username sudah digunakan.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, username, password, is_active) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$nama, $email, $username, $hashed_password]);
                $user_id = $pdo->lastInsertId();

                $token = generate_token();
                $stmt = $pdo->prepare("INSERT INTO user_activations (user_id, token) VALUES (?, ?)");
                $stmt->execute([$user_id, $token]);

                if (send_activation_email($email, $token)) {
                    $message = "Registrasi berhasil! Silakan cek email untuk aktivasi.";
                } else {
                    $message = "Registrasi berhasil, tapi gagal kirim email aktivasi.";
                }
            } catch (PDOException $e) {
                // Jika terjadi duplicate karena race condition
                if (str_contains($e->getMessage(), 'Duplicate entry')) {
                    $error = "Email atau username sudah digunakan.";
                } else {
                    $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
                    error_log("Registrasi error: " . $e->getMessage());
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff; /* Light blue background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
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
    <div class="register-card">
    <div class="logo">
        <img src="../assets/images/logo.png" alt="RentalKu Logo" style="width: 50px; height: auto;">
    </div>
        <h5 class="text-center mb-4">RentalKu</h5>
        <p class="text-center text-muted mb-4">Buat akun baru</p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Buat username unik" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Daftar</button>
            <div class="text-center">
                <a href="login.php" class="text-link">Sudah punya akun? Masuk</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"></script>
</body>
</html>