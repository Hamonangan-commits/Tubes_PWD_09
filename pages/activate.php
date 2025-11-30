<?php
require_once '../includes/db.php';

if (!isset($_GET['token'])) {
    die("Token tidak valid.");
}

$token = $_GET['token'];
$stmt = $pdo->prepare("SELECT user_id FROM user_activations WHERE token = ?");
$stmt->execute([$token]);
$row = $stmt->fetch();

if ($row) {
    $user_id = $row['user_id'];
    $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM user_activations WHERE token = ?")->execute([$token]);
    echo "<div class='container mt-5 text-center'><h3>Akun berhasil diaktifkan! <a href='login.php'>Login sekarang</a></h3></div>";
} else {
    echo "<div class='container mt-5 text-center'><h3>Token aktivasi tidak valid atau kadaluarsa.</h3></div>";
}
?>