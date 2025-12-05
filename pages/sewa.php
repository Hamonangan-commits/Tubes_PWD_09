<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_login();

// Handle penyewaan mobil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobil_id = (int)$_POST['mobil_id'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $lokasi = trim($_POST['lokasi_jemput']);

    if ($mobil_id && $tgl_mulai && $tgl_selesai && $lokasi) {
        // Cek apakah mobil masih tersedia
        $stmt = $pdo->prepare("SELECT status FROM mobil WHERE id = ?");
        $stmt->execute([$mobil_id]);
        $mobil_status = $stmt->fetchColumn();

        if ($mobil_status !== 'tersedia') {
            header('Location: sewa.php?error=mobil_tidak_tersedia');
            exit();
        }

        // Simpan transaksi
        $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, mobil_id, tgl_mulai, tgl_selesai, lokasi_jemput) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $mobil_id, $tgl_mulai, $tgl_selesai, $lokasi]);

        // Update status mobil
        $pdo->prepare("UPDATE mobil SET status = 'disewa' WHERE id = ?")->execute([$mobil_id]);

        header('Location: transaksi.php?pesan=success');
        exit();
    }
}

// Ambil semua mobil
$stmt = $pdo->prepare("SELECT * FROM mobil");
$stmt->execute();
$mobil = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sewa Mobil - RentalKu</title>
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
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            border-radius: 0.5rem;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-3px);
        }
        .btn-sewa {
            background-color: #0d6efd;
            color: white;
            border-radius: 20px;
        }
        .btn-sewa:hover {
            background-color: #0d6efd;
        }
        .btn-secondary {
            background-color: #6c757d81;
            color: white;
        }
        .modal-body img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            margin-bottom: 1rem;
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
            <a class="nav-link active" href="sewa.php">Sewa Mobil</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="transaksi.php">Booking Saya <span class="badge bg-primary"></span></a>
        </li>
    </ul>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'mobil_tidak_tersedia'): ?>
        <div class="alert alert-warning">Mobil sudah tidak tersedia. Silakan pilih mobil lain.</div>
    <?php endif; ?>

    <h4>Mobil Tersedia</h4>
    <p>Pilih mobil yang Anda inginkan</p>

    <div class="row g-4">
        <?php foreach ($mobil as $m): ?>
            <div class="col-md-4">
                <div class="card position-relative">
                    <img src="../assets/uploads/mobil/default_car.avif" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($m['model']) ?>" 
                         style="height: 200px; object-fit: cover;">

                    <?php if ($m['status'] === 'disewa'): ?>
                        <div class="position-absolute top-50 start-50 translate-middle bg-danger text-white px-3 py-2 rounded-pill">
                            Tidak Tersedia
                        </div>
                        <div class="card-body">
                            <h6 class="text-muted"><?= htmlspecialchars($m['merek']) ?></h6>
                            <h5><?= htmlspecialchars($m['model']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <small>Harga per hari</small><br>
                                    <strong>Rp <?= number_format($m['harga_per_hari']) ?></strong>
                                </div>
                                <button class="btn btn-secondary btn-sm" disabled>Sewa</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card-body">
                            <h6 class="text-muted"><?= htmlspecialchars($m['merek']) ?></h6>
                            <h5><?= htmlspecialchars($m['model']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <small>Harga per hari</small><br>
                                    <strong>Rp <?= number_format($m['harga_per_hari']) ?></strong>
                                </div>
                                <button type="button" class="btn btn-sewa btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#bookingModal<?= $m['id'] ?>"
                                        data-harga="<?= $m['harga_per_hari'] ?>">
                                    Sewa
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL DI LUAR LOOP -->
<?php foreach ($mobil as $m): ?>
    <?php if ($m['status'] !== 'disewa'): ?>
    <div class="modal fade" id="bookingModal<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Mobil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img src="../assets/uploads/mobil/default_car.avif" alt="<?= htmlspecialchars($m['model']) ?>">
                    <div class="mt-3">
                        <h6><?= htmlspecialchars($m['merek']) ?></h6>
                        <h5><?= htmlspecialchars($m['model']) ?></h5>
                    </div>

                    <form method="POST" action="sewa.php">
                        <input type="hidden" name="mobil_id" value="<?= $m['id'] ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi Jemput</label>
                            <div class="input-group">
                                <input type="text" name="lokasi_jemput" class="form-control" placeholder="Klik tombol di bawah untuk isi otomatis" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="getLocation(this)">Isi Lokasi Otomatis</button>
                            </div>
                            <div class="form-text">Sistem akan mengambil lokasi GPS Anda.</div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>Harga per hari:</strong> Rp <?= number_format($m['harga_per_hari']) ?><br>
                            <strong>Durasi:</strong> <span id="durasi_<?= $m['id'] ?>">0 hari</span><br>
                            <strong>Total Pembayaran:</strong> <span id="total_<?= $m['id'] ?>">Rp 0</span>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Konfirmasi Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
function getLocation(button) {
    const form = button.closest('form');
    const lokasiInput = form.querySelector('input[name="lokasi_jemput"]');
    if (!lokasiInput) return;

    if (navigator.geolocation) {
        lokasiInput.placeholder = "Mengambil lokasi...";
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);
                lokasiInput.value = `Lat: ${lat}, Lng: ${lng}`;
                lokasiInput.placeholder = "Lokasi jemput";
            },
            (error) => {
                console.error("Geolocation error:", error);
                lokasiInput.placeholder = "Izinkan lokasi atau isi manual";
                lokasiInput.value = "";
                alert("Gagal mengambil lokasi. Pastikan izin lokasi diaktifkan.");
            }
        );
    } else {
        alert("Browser Anda tidak mendukung geolocation.");
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function () {
            const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if (button) {
                const hargaPerHari = parseFloat(button.getAttribute('data-harga')) || 0;
                const form = this.querySelector('form');
                const tglMulai = form.querySelector('input[name="tgl_mulai"]');
                const tglSelesai = form.querySelector('input[name="tgl_selesai"]');
                const durasiSpan = this.querySelector('[id^="durasi_"]');
                const totalSpan = this.querySelector('[id^="total_"]');

                function calculate() {
                    if (tglMulai.value && tglSelesai.value) {
                        const start = new Date(tglMulai.value);
                        const end = new Date(tglSelesai.value);
                        if (end < start) {
                            durasiSpan.textContent = 'Tanggal tidak valid';
                            totalSpan.textContent = 'Rp 0';
                            return;
                        }
                        const diffTime = Math.abs(end - start);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        durasiSpan.textContent = `${diffDays} hari`;
                        totalSpan.textContent = `Rp ${diffDays * hargaPerHari}`;
                    } else {
                        durasiSpan.textContent = '0 hari';
                        totalSpan.textContent = 'Rp 0';
                    }
                }

                tglMulai.addEventListener('change', calculate);
                tglSelesai.addEventListener('change', calculate);
            }
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>