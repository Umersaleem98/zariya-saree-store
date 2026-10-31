<?php

// Keep credentials out of command arguments and database backups outside the web root.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$database = config('database.connections.mysql');
$destination = $argv[1] ?? '';
if (!is_dir(dirname($destination)) || $destination === '') {
    throw new RuntimeException('A private backup destination is required.');
}
$credentials = tempnam(sys_get_temp_dir(), 'rangmahal-db-');
chmod($credentials, 0600);
$quote = static fn ($value) => '"'.str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], (string) $value).'"';
try {
    file_put_contents($credentials, "[client]\nuser=".$quote($database['username'])."\npassword=".$quote($database['password'])."\nhost=".$quote($database['host'])."\nport=".(int) $database['port']."\n");
    $process = proc_open(['mysqldump', '--defaults-extra-file='.$credentials, '--single-transaction', '--no-tablespaces', '--skip-lock-tables', $database['database']], [0 => ['pipe', 'r'], 1 => ['file', $destination, 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Could not start database backup.');
    }
    fclose($pipes[0]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
        throw new RuntimeException('Database backup failed: '.$error);
    }
    chmod($destination, 0600);
    echo "Database backup saved outside the web root.\n";
} finally {
    unlink($credentials);
}
