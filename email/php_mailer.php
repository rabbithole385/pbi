<?php
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $pDir = dirname(__DIR__) . '/includes';
    if (file_exists($pDir . '/PHPMailer.php')) {
        require_once($pDir . '/Exception.php');
        require_once($pDir . '/PHPMailer.php');
        require_once($pDir . '/SMTP.php');
    } elseif (file_exists(__DIR__ . '/includes/PHPMailer.php')) {
        require_once(__DIR__ . '/includes/Exception.php');
        require_once(__DIR__ . '/includes/PHPMailer.php');
        require_once(__DIR__ . '/includes/SMTP.php');
    }
}

if (!function_exists('configure_pbi_mailer')) {
    require_once(dirname(__DIR__) . '/scripts/functions.php');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);
if (function_exists('configure_pbi_mailer')) {
    configure_pbi_mailer($mail);
} else {
    $mail->isSMTP();
    $mail->Host = !empty($smtp_host) ? $smtp_host : 'smtp.resend.com';
    $mail->SMTPAuth = true;
    $mail->CharSet = "UTF-8";
    $mail->Username = !empty($smtp_username) ? $smtp_username : 'resend'; 
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = !empty($smtp_auth) ? $smtp_auth : 'ssl';
    $mail->Port = !empty($smtp_port) ? $smtp_port : 465;
    $sender = (filter_var($smtp_username, FILTER_VALIDATE_EMAIL)) ? $smtp_username : 'info@pbigroups.com';
    $senderName = !empty($display_name) ? $display_name : 'PBI Group';
    $mail->setFrom($sender, $senderName);
    $mail->addReplyTo($sender, $senderName);
}
