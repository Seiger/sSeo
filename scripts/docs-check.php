#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$docs = $root . '/docs';
$languages = ['en', 'ua', 'uk', 'pl', 'de', 'fr'];
$required = ['README.md', 'user-guide.md', 'developer-guide.md'];
$legacySources = [
    $docs . '/pages',
    $docs . '/i18n',
];
$errors = [];

foreach ($legacySources as $legacySource) {
    if (is_dir($legacySource)) {
        $errors[] = 'Legacy Docusaurus docs source is still present: ' . relative($legacySource, $root);
    }
}

foreach ($languages as $language) {
    foreach ($required as $file) {
        $path = $docs . '/' . $language . '/' . $file;
        if (!is_file($path)) {
            $errors[] = 'Missing required docs file: ' . relative($path, $root);
        }
    }
}

foreach (markdownFiles($docs) as $file) {
    $content = (string) file_get_contents($file);

    preg_match_all('/!?\[[^\]]*\]\(([^)]+)\)/', $content, $matches);
    foreach ($matches[1] ?? [] as $target) {
        $target = trim((string) $target);
        if ($target === '' || isExternal($target) || str_starts_with($target, '#')) {
            continue;
        }

        $pathOnly = explode('#', $target, 2)[0];
        $pathOnly = explode('?', $pathOnly, 2)[0];
        if ($pathOnly === '') {
            continue;
        }

        $absolute = str_starts_with($pathOnly, '/img/')
            ? realpath($docs . '/static' . $pathOnly)
            : realpath(dirname($file) . '/' . $pathOnly);
        if ($absolute === false || !str_starts_with($absolute, realpath($docs) ?: $docs)) {
            $errors[] = relative($file, $root) . ' has broken local link: ' . $target;
        }
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, '[FAIL] ' . $error . PHP_EOL);
    }
    exit(1);
}

echo 'sSeo docs check OK' . PHP_EOL;

function markdownFiles(string $dir): array
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    $files = [];
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && strtolower($file->getExtension()) === 'md') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

function isExternal(string $target): bool
{
    return (bool) preg_match('/^(?:[a-z][a-z0-9+.-]*:|\/\/)/i', $target);
}

function relative(string $path, string $root): string
{
    $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    return str_starts_with($path, $root) ? substr($path, strlen($root)) : $path;
}
