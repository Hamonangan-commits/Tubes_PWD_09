<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

require_login();

// Handle pembatalan transaksi
if (isset($_GET['batal']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT status, mobil_id FROM transaksi WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $trans = $stmt->fetch();
    if ($trans && $trans['status'] === 'aktif') {
        // Update status jadi 'dibatalkan'
        $pdo->prepare("UPDATE transaksi SET status = 'dibatalkan' WHERE id = ?")->execute([$id]);
        // Kembalikan status mobil ke 'tersedia'
        $pdo->prepare("UPDATE mobil SET status = 'tersedia' WHERE id = ?")->execute([$trans['mobil_id']]);
        header('Location: transaksi.php?pesan=dibatalkan');
        exit();
    }
}

// Ambil transaksi pengguna
$stmt = $pdo->prepare("
    SELECT t.*, m.merek, m.model 
    FROM transaksi t 
    LEFT JOIN mobil m ON t.mobil_id = m.id 
    WHERE t.user_id = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transaksi = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Booking Saya - RentalKu</title>
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
        .badge-aktif {
            background-color: #dc3545;
        }
        .badge-selesai {
            background-color: #6c757d;
        }
        .btn-batalkan {
            background-color: #dc3545;
            color: white;
        }
        .btn-batalkan:hover {
            background-color: #c82333;
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
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person"></i> user
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
            <a class="nav-link active" href="transaksi.php">Booking Saya <span class="badge bg-primary"></span></a>
        </li>
    </ul>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'dibatalkan'): ?>
        <div class="alert alert-success">Transaksi berhasil dibatalkan.</div>
    <?php endif; ?>

    <h4>Riwayat Transaksi</h4>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>Mobil</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['merek'] . ' ' . $t['model']) ?></td>
                        <td><?= htmlspecialchars($t['tgl_mulai']) ?> s/d <?= htmlspecialchars($t['tgl_selesai']) ?></td>
                        <td><?= htmlspecialchars($t['lokasi_jemput']) ?></td>
                        <td>
                            <?php
                            $status_class = '';
                            $status_text = '';
                            switch ($t['status']) {
                                case 'aktif':
                                    $status_class = 'bg-danger';
                                    $status_text = 'Aktif';
                                    break;
                                case 'dibatalkan':
                                    $status_class = 'bg-secondary';
                                    $status_text = 'Dibatalkan';
                                    break;
                                case 'selesai':
                                    $status_class = 'bg-success';
                                    $status_text = 'Selesai';
                                    break;
                                default:
                                    $status_class = 'bg-light text-dark';
                                    $status_text = ucfirst($t['status']);
                            }
                            ?>
                            <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                        </td>
                        <td>
                            <?php if ($t['status'] === 'aktif'): ?>
                                <a href="?batal=1&id=<?= $t['id'] ?>" class="btn btn-batalkan btn-sm" onclick="return confirm('Batalkan sewa?')">Batalkan</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="sewa.php" class="btn btn-primary">Sewa Mobil Baru</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>