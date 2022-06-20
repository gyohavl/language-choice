<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getPHPMailerInstance($host, $email, $password, $sender, $subject) {
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP(); // Send using SMTP
    $mail->Host = $host; // Set the SMTP server to send through
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = $email; // SMTP username
    $mail->Password = $password; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption
    $mail->Port = 465; // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($email, $sender);
    $mail->Subject = $subject;

    return $mail;
}
