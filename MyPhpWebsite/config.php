<?php
session_start();
// Database Credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'concert_db';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Ensure the uploads directory exists for receipts and concert images
if (!is_dir('uploads')) {
    mkdir('uploads');
}

/**
 * Base URL (used for email verification links)
 * Update this if your project folder name is different.
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/MyPhpWebsite');
}

/**
 * Mail settings (for demo/local you can still show the link on-screen if mail() is not configured)
 */
if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', 'no-reply@tixlokal.local');
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', 'TixLokal');
}

// Simple redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Minimal email sender using PHP mail().
 * Returns true if mail() returns true, otherwise false.
 */
function send_mail_simple($to, $subject, $htmlBody) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    return @mail($to, $subject, $htmlBody, $headers);
}

/**
 * Send verification email (always stores link in session for local demo).
 */
function send_verification_email($email, $name, $token) {
    $link = rtrim(BASE_URL, '/') . "/verify.php?token=" . urlencode($token);
    $_SESSION['last_verify_link'] = $link; // helpful for localhost demo

    $subject = "Verify your email - TixLokal";
    $safeName = htmlspecialchars($name ?? 'there');
    $body = "
      <div style='font-family:Arial,sans-serif;line-height:1.5'>
        <h2>Hi {$safeName},</h2>
        <p>Thanks for registering at <b>TixLokal</b>. Please verify your email to activate your account.</p>
        <p><a href='{$link}' style='display:inline-block;padding:10px 14px;border-radius:10px;background:#7c3aed;color:#fff;text-decoration:none'>Verify Email</a></p>
        <p style='color:#555'>Or copy this link: <br><span>{$link}</span></p>
        <p style='color:#777;font-size:12px'>If you didn’t create this account, you can ignore this email.</p>
      </div>
    ";

    return send_mail_simple($email, $subject, $body);
}
?>
