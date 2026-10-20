<?php
/**
 * mailer.php
 * PHPMailer wrapper for verification-code and notification emails.
 * SMTP settings come from environment variables (.env) - never hardcode
 * credentials here. Variables: MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM,
 * MAIL_HOST, MAIL_PORT.
 */

declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function mailEnv(string $key, string $default = ''): string
{
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

define('MAIL_USERNAME',  mailEnv('MAIL_USERNAME'));
define('MAIL_PASSWORD',  mailEnv('MAIL_PASSWORD'));
define('MAIL_FROM',      mailEnv('MAIL_FROM', MAIL_USERNAME));
define('MAIL_HOST',      mailEnv('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT',      (int)mailEnv('MAIL_PORT', '587'));
define('MAIL_FROM_NAME', 'Camp Mayagay');

/**
 * Sends the 6-digit verification code to the given email address.
 *
 * @param string      $toEmail  Where to send the code
 * @param string      $code     The verification code to include
 * @param string|null $errorOut If sending fails, the reason is written here
 *                              (for server-side logging only - never show
 *                              this raw text to the guest).
 * @return bool true on success, false on failure
 */
function sendVerificationEmail(string $toEmail, string $code, ?string &$errorOut = null): bool
{
    if (MAIL_USERNAME === '' || MAIL_PASSWORD === '') {
        $errorOut = 'Email is not configured (set MAIL_USERNAME / MAIL_PASSWORD in .env).';
        return false;
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->Timeout    = 10; // fail fast instead of hanging for minutes on connection issues

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail);
        $mail->CharSet = 'UTF-8';
        $mail->XMailer = ' '; // blank this out - the default "PHPMailer" signature is a common spam trigger

        $mail->isHTML(true);
        $mail->Subject = 'Camp Mayagay - Password Reset Code';
        $mail->Body =
            '<p>You requested to reset your Camp Mayagay password.</p>' .
            '<p>Your verification code is:</p>' .
            '<h2 style="letter-spacing:6px;">' . htmlspecialchars($code) . '</h2>' .
            '<p>This code expires in 10 minutes. If you did not request this, you can safely ignore this email.</p>';
        $mail->AltBody = "Your Camp Mayagay verification code is: {$code}\nThis code expires in 10 minutes.";

        $mail->send();

        return true;
    } catch (PHPMailerException $e) {
        $errorOut = $mail->ErrorInfo;

        return false;
    }
}

/**
 * Sends a general-purpose notification email (used by the admin
 * "Add Message" composer on the Notifications page).
 *
 * @param string      $toEmail   Recipient's email address
 * @param string      $subject   Email subject line (the notification title)
 * @param string      $message   Plain-text message body (will be escaped
 *                                and turned into simple HTML paragraphs)
 * @param string|null $errorOut  If sending fails, the reason is written
 *                                here (server-side logging only).
 * @return bool true on success, false on failure
 */
function sendNotificationEmail(string $toEmail, string $subject, string $message, ?string &$errorOut = null): bool
{
    if (MAIL_USERNAME === '' || MAIL_PASSWORD === '') {
        $errorOut = 'Email is not configured (set MAIL_USERNAME / MAIL_PASSWORD in .env).';
        return false;
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->Timeout    = 10;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail);
        $mail->CharSet = 'UTF-8';
        $mail->XMailer = ' '; // blank this out - the default "PHPMailer" signature is a common spam trigger

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body =
            '<p>' . nl2br(htmlspecialchars($message)) . '</p>' .
            '<p style="color:#888; font-size:12px; margin-top:24px;">This message was sent by Camp Mayagay.</p>';
        $mail->AltBody = $message;

        $mail->send();

        return true;
    } catch (PHPMailerException $e) {
        $errorOut = $mail->ErrorInfo;

        return false;
    }
}
