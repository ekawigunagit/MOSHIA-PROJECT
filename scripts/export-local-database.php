<?php

// Local handoff export. Credentials are read from Laravel, never printed or passed in argv.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $connection = Illuminate\Support\Facades\DB::connection();
    $config = $connection->getConfig();
    if (! in_array($config['driver'], ['mysql', 'mariadb'], true)
        || ! in_array($config['host'], ['localhost', '127.0.0.1', '::1'], true)) {
        throw new RuntimeException('Only a local MySQL/MariaDB connection can be exported.');
    }
    $binary = 'D:/XAMPP8212/mysql/bin/mysqldump.exe';
    if (! is_file($binary)) {
        throw new RuntimeException('mysqldump executable is unavailable.');
    }
    $directory = base_path('exports/database');
    if (! is_dir($directory) && ! mkdir($directory, 0700, true)) {
        throw new RuntimeException('Cannot create export directory.');
    }
    $stamp = (new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok')))->format('Ymd-His');
    $target = $directory.'/moshia-'.$stamp.'.sql';
    $partial = $target.'.partial';
    if (file_exists($target) || file_exists($partial)) {
        throw new RuntimeException('Export already exists; retry with a new timestamp.');
    }
    $command = [
        $binary, '--no-defaults', '--host='.$config['host'], '--port='.($config['port'] ?? 3306),
        '--user='.$config['username'], '--single-transaction', '--quick', '--hex-blob',
        '--routines', '--events', '--triggers', '--default-character-set=utf8mb4',
        '--result-file='.$partial, $config['database'],
    ];
    $environment = getenv();
    $environment['MYSQL_PWD'] = (string) ($config['password'] ?? '');
    $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path(), $environment);
    unset($environment);
    if (! is_resource($process)) {
        throw new RuntimeException('Cannot start mysqldump.');
    }
    fclose($pipes[0]);
    stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        throw new RuntimeException('mysqldump failed (exit '.$exitCode.'); incomplete .partial file retained.');
    }
    $tables = [];
    $complete = false;
    $handle = fopen($partial, 'rb');
    while (($line = fgets($handle)) !== false) {
        if (preg_match('/^CREATE TABLE `([^`]+)`/', $line, $matches)) {
            $tables[] = $matches[1];
        }
        if (str_starts_with($line, '-- Dump completed on')) {
            $complete = true;
        }
    }
    fclose($handle);
    foreach (['users', 'migrations', 'core_purchase_orders', 'wedding_invitations'] as $required) {
        if (! in_array($required, $tables, true)) {
            throw new RuntimeException('Required table missing from export.');
        }
    }
    if (! $complete || ! filesize($partial)) {
        throw new RuntimeException('Export completion validation failed.');
    }
    if (! rename($partial, $target)) {
        throw new RuntimeException('Cannot finalize export.');
    }
    $hash = hash_file('sha256', $target);
    file_put_contents($target.'.sha256', $hash.'  '.basename($target).PHP_EOL);
    echo json_encode([
        'file' => $target, 'bytes' => filesize($target), 'tables' => count($tables),
        'sha256' => $hash, 'completion_marker' => true,
        'restore_tested' => false, 'warnings_present' => trim($error) !== '',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
} catch (Throwable $exception) {
    // Do not expose connection details, SQL, passwords, or provider errors.
    fwrite(STDERR, 'Export failed. No database was changed; check local export configuration.'.PHP_EOL);
    exit(1);
}
