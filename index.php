<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['is_admin']) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: pages/profile.php');
    }
} else {
    header('Location: pages/login.php');
}
?>