<?php
require_once 'db.php';

function upload_profile_photo($file, $user_id) {
    $upload_dir = '../assets/uploads/profiles/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $allowed = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed) || $file['error'] !== 0) return false;
    if ($file['size'] > 2 * 1024 * 1024) return false; 

    $filename = $user_id . '.jpg'; // simpan sebagai user_id.jpg
    $path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $filename;
    }
    return false;
}

function get_user_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}
?>