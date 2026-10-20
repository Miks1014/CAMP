<?php
declare(strict_types=1);

/**
 * Loads KEY=VALUE lines from <project>/.env.local into the environment so
 * the app runs without Docker (e.g. `php -S` on Windows). Real environment
 * variables (Docker, hosting panel) always win - nothing is overwritten.
 */
function loadLocalEnv(string $root): void
{
    $file = $root . '/.env.local';
    if (!is_file($file)) {
        return;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $i => $line) {
        if ($i === 0) {
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line); // Windows Notepad BOM
        }
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        if (strlen($val) >= 2 && ($val[0] === '"' || $val[0] === "'") && substr($val, -1) === $val[0]) {
            $val = substr($val, 1, -1);
        }
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $val);
            $_ENV[$key] = $val;
        }
    }
}

loadLocalEnv(dirname(__DIR__));
