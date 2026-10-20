<?php
/**
 * Router for PHP's built-in server (no Apache/XAMPP/Docker needed):
 *     php -S localhost:8080 router.php
 * Serves the site, and blocks files that must never be downloaded.
 */
$path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if ($path === '/' || $path === '') {
    header('Location: /Login.html');
    return true;
}

$forbidden =
    preg_match('#(^|/)\.#', $path)                                   // .env, .env.local, .vscode, .git
    || preg_match('#^/(database|docker|java|PHPMailer)(/|$)#i', $path)
    || preg_match('#^/php/(seed|db|env|mailer|login_helpers|session)\.php$#i', $path)
    || preg_match('#^/assets/uploads/.*\.(php|phtml|phar)$#i', $path)
    || preg_match('#\.(sql|bat|md|yml|example)$#i', $path)
    || preg_match('#^/(Dockerfile|router\.php)$#i', $path);

if ($forbidden) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

return false; // let PHP serve the file / run the .php script
