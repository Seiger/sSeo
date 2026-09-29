<?php

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
    return;
}

// Standalone run (e.g. phpunit.phar): pure support classes need no Evolution CMS runtime.
spl_autoload_register(static function (string $class): void {
    $prefix = 'Seiger\\sSeo\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
