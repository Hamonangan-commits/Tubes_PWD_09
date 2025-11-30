<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}
?>