<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function is_admin() {
    return !empty($_SESSION['is_admin']); // Cukup cek nilai truthy
}
?>