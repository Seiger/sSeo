<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DemoWiringContractTest extends TestCase
{
    private string $root;
    private string $demoCore;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
        $this->demoCore = getenv('SSEO_DEMO_CORE') ?: dirname($this->root) . '/sArticles/demo/core';
    }

    public function test_shared_demo_uses_sseo_path_repository(): void
    {
        $customComposer = $this->readJson($this->demoCore . '/custom/composer.json');

        $this->assertSame('*', $customComposer['require']['seiger/sseo'] ?? null);
        $this->assertTrue($this->hasPathRepository($customComposer, '../../../../sSeo'));
    }

    public function test_installed_metadata_keeps_sseo_provider_discoverable(): void
    {
        $installed = $this->readJson($this->demoCore . '/vendor/composer/installed.json');
        $package = $this->installedPackage($installed, 'seiger/sseo');

        $this->assertNotNull($package);
        $this->assertStringContainsString('../../../../sSeo', (string) $this->dataGet($package, 'dist.url', ''));
        $this->assertContains('Seiger\\sSeo\\sSeoServiceProvider', $this->dataGet($package, 'extra.laravel.providers', []));
        $this->assertSame('Seiger\\sSeo\\Facades\\sSeo', $this->dataGet($package, 'extra.laravel.aliases.sSeo'));
    }

    public function test_demo_registers_sseo_routes_and_migration(): void
    {
        $routeList = $this->runCommand([PHP_BINARY, 'artisan', 'route:list']);

        foreach (['sSeo.module', 'sSeo.dashboard', 'sSeo.redirects', 'sSeo.templates', 'sSeo.robots', 'sSeo.analytics', 'sSeo.configure', 'sSeo.modulesave'] as $routeName) {
            $this->assertStringContainsString($routeName, $routeList);
        }

        $migrationStatus = $this->runCommand([PHP_BINARY, 'artisan', 'migrate:status']);

        $this->assertStringContainsString('2024_11_18_094556_create_s_seo_table', $migrationStatus);
        $this->assertStringContainsString('Ran', $migrationStatus);
    }

    public function test_demo_database_has_sseo_tables(): void
    {
        $database = $this->demoCore . '/database/database.sqlite';

        $this->assertFileExists($database);

        $pdo = new PDO('sqlite:' . $database);

        $this->assertTrue($this->tableExists($pdo, 'evo_s_seo') || $this->tableExists($pdo, 's_seo'));
        $this->assertTrue($this->tableExists($pdo, 'evo_s_redirects') || $this->tableExists($pdo, 's_redirects'));
    }

    public function test_demo_smoke_script_passes(): void
    {
        $script = $this->root . '/scripts/demo-smoke.php';

        $this->assertFileExists($script);

        exec(PHP_BINARY . ' ' . escapeshellarg($script) . ' 2>&1', $output, $code);

        $this->assertSame(0, $code, implode(PHP_EOL, $output));
        $this->assertContains('sSeo demo smoke OK', $output);
    }

    private function readJson(string $path): array
    {
        $this->assertFileExists($path);

        $json = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($json);

        return $json;
    }

    private function hasPathRepository(array $composer, string $url): bool
    {
        foreach (($composer['repositories'] ?? []) as $repository) {
            if (($repository['type'] ?? '') === 'path' && ($repository['url'] ?? '') === $url) {
                return true;
            }
        }

        return false;
    }

    private function installedPackage(array $installed, string $name): ?array
    {
        foreach (($installed['packages'] ?? $installed) as $package) {
            if (($package['name'] ?? '') === $name) {
                return $package;
            }
        }

        return null;
    }

    private function dataGet(?array $array, string $path, mixed $default = null): mixed
    {
        if ($array === null) {
            return $default;
        }

        $value = $array;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private function runCommand(array $command): string
    {
        $escaped = implode(' ', array_map('escapeshellarg', $command));
        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($escaped, $descriptorSpec, $pipes, $this->demoCore);

        $this->assertIsResource($process);

        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);

        $code = proc_close($process);

        $this->assertSame(0, $code, trim($stderr . PHP_EOL . $stdout));

        return $stdout;
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
        $statement = $pdo->prepare("select name from sqlite_master where type = 'table' and name = :table");
        $statement->execute(['table' => $table]);

        return (bool) $statement->fetchColumn();
    }
}
