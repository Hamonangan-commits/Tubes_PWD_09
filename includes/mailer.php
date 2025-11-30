<?php
// includes/mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/PHPMailer/src/Exception.php';
require_once '../vendor/PHPMailer/src/PHPMailer.php';
require_once '../vendor/PHPMailer/src/SMTP.php';

function send_activation_email($email, $token) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP (gunakan Gmail atau SMTP gratis)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';       // Ganti sesuai penyedia
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com'; // Ganti
        $mail->Password   = 'your_app_password';    // Ganti (gunakan App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('noreply@rentalmobil.id', 'RentalMobil.id');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Aktivasi Akun RentalMobil.id';
        $activation_link = "http://localhost/rentalmobil/pages/activate.php?token=" . urlencode($token);
        $mail->Body    = "Klik link berikut untuk aktivasi akun:<br><a href='$activation_link'>Aktivasi Akun</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Gagal kirim email: " . $mail->ErrorInfo);
        return false;
    }
}
?>