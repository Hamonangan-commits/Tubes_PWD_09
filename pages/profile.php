<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_login();

$user = get_user_by_id($pdo, $_SESSION['user_id']);
$upload_dir = '../assets/uploads/profiles/';
$photo_file = $user['id'] . '.jpg';
$photo_path = $upload_dir . $photo_file;

// Cek apakah foto ada, jika tidak gunakan default
if (file_exists($photo_path)) {
    $photo_url = '../assets/uploads/profiles/' . $photo_file;
} else {
    $photo_url = '../assets/images/user_default.png'; // fallback
}

$message = '';
$error = '';

// Handle upload foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_foto'])) {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        if (upload_profile_photo($_FILES['foto'], $user['id'])) {
            $message = "Foto profil berhasil diperbarui.";
            // Refresh path foto
            $photo_url = '../assets/uploads/profiles/' . $user['id'] . '.jpg';
        } else {
            $error = "Gagal upload foto. Pastikan format JPG/PNG dan ukuran < 2MB.";
        }
    }
}

// Handle update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = trim($_POST['alamat']);
    $kota = trim($_POST['kota']);

    $pdo->prepare("UPDATE users SET nama = ?, email = ?, telepon = ?, tanggal_lahir = ?, alamat = ?, kota = ? WHERE id = ?")
        ->execute([$nama, $email, $telepon, $tanggal_lahir, $alamat, $kota, $user['id']]);

    $user = get_user_by_id($pdo, $_SESSION['user_id']);
    $message = "Profil berhasil diperbarui.";
}

// Handle ubah password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user['id']]);
            $message = "Password berhasil diubah.";
        } else {
            $error = "Konfirmasi password tidak cocok.";
        }
    } else {
        $error = "Password lama salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-link {
            font-weight: 500;
        }
        .nav-link.active {
            border-bottom: 3px solid #6f42c1;
            color: #6f42c1 !important;
        }
        .profile-card {
            max-width: 300px;
            margin: 0 auto;
            text-align: center;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0d6efd;
        }
        .btn-edit {
            background-color: #6f42c1;
            color: white;
            border-radius: 8px;
        }
        .btn-edit:hover {
            background-color: #5e35b1;
        }
        .section-title {
            font-weight: 600;
            color: #333;
        }
        .btn-simpan {
            background-color: #6f42c1;
            color: white;
            border-radius: 8px;
        }
        .btn-simpan:hover {
            background-color: #5e35b1;
        }
        .btn-batal {
            background-color: #6c757d;
            color: white;
        }
        .btn-batal:hover {
            background-color: #5a6268;
        }
        .form-control {
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .avatar-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .avatar-container:hover .avatar-overlay {
            opacity: 1;
        }
        .avatar-icon {
            color: white;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <span class="navbar-brand d-flex align-items-center">
            <img src="../assets/images/logo_user.png" alt="RentalKu Logo" style="width: 50px; height: 50px; object-fit: contain;">
            <span class="ms-2">RentalKu</span>
        </span>
        <div class="ms-auto">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <img src="../assets/images/user_avatar.png" alt="User Avatar" style="width: 24px; height: 24px; border-radius: 50%; margin-right: 8px;">
                    <span>user</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">Profil Saya</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="sewa.php">Sewa Mobil</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="transaksi.php">Booking Saya <span class="badge bg-primary"></span></a>
        </li>
    </ul>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <h4>Profil Saya</h4>
            <p>Kelola informasi pribadi Anda</p>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Kartu Profil -->
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <!-- Form Upload Foto (Visible tapi tersembunyi) -->
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="avatar-container" onclick="document.getElementById('foto').click();">
                        <img src="<?= htmlspecialchars($photo_url) ?>" class="profile-avatar mx-auto d-block mb-3" alt="Foto Profil">
                        <div class="avatar-overlay">
                            <i class="bi bi-camera-fill avatar-icon"></i>
                        </div>
                    </div>
                    <input type="file" id="foto" name="foto" class="d-none" accept="image/*" onchange="this.form.submit()">
                    <input type="hidden" name="upload_foto" value="1">
                </form>
                <h5><?= htmlspecialchars($user['username']) ?></h5>
                <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-primary mb-3">User Account</span>
                <div class="mt-3">
                    <p><strong>Status Akun</strong>: <span class="text-success">Aktif</span></p>
                    <p><strong>Member Sejak</strong>: Nov 2025</p>
                    <p><strong>Total Booking</strong>: 0</p>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Informasi Pribadi -->
        <div class="col-md-8">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-title">Informasi Pribadi</h5>
                    <a href="#" class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profil</a>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['telepon'] ?? 'Belum diisi') ?>" readonly>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['tanggal_lahir'] ?? 'Belum diisi') ?>" readonly>
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($user['alamat'] ?? 'Belum diisi') ?></textarea>
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="form-label">Kota</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['kota'] ?? 'Belum diisi') ?>" readonly>
                    </div>
                </div>
            </div>

            <!-- Keamanan -->
            <div class="card p-4 mt-4">
                <h5 class="section-title">Keamanan</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Ubah Password</strong><br>
                            Perbarui password Anda secara berkala.
                        </div>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Profil -->
<div class="modal fade" id="editProfileModal" tabindex="-1"> 
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($user['tanggal_lahir'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kota</label>
                        <input type="text" name="kota" class="form-control" value="<?= htmlspecialchars($user['kota'] ?? '') ?>">
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-batal" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_profile" class="btn btn-simpan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>