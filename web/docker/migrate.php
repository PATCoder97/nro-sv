<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('DB_HOST') ?: 'database';
$port = intval(getenv('DB_PORT') ?: 3306);
$name = getenv('DB_NAME') ?: 'team2026';
$user = getenv('DB_USER') ?: 'teamobi';
$password = getenv('DB_PASSWORD') ?: 'change-me';
$connection = null;

for ($attempt = 1; $attempt <= 60; $attempt++) {
    $connection = @new mysqli($host, $user, $password, $name, $port);
    if (!$connection->connect_errno) {
        break;
    }
    if ($attempt === 60) {
        fwrite(STDERR, "Database did not become ready: {$connection->connect_error}\n");
        exit(1);
    }
    sleep(2);
}

$connection->set_charset('utf8mb4');
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false || !$connection->multi_query($schema)) {
    fwrite(STDERR, "Unable to apply web schema: {$connection->error}\n");
    exit(1);
}

do {
    if ($result = $connection->store_result()) {
        $result->free();
    }
    if ($connection->errno) {
        fwrite(STDERR, "Web schema migration failed: {$connection->error}\n");
        exit(1);
    }
} while ($connection->more_results() && $connection->next_result());

$connection->close();
echo "Web schema is ready.\n";
