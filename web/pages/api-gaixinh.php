<?php
// Set JSON header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$hp2k1_host = getenv('DB_HOST') ?: 'database';
$hp2k1_user = getenv('DB_USER') ?: 'teamobi';
$hp2k1_pass = getenv('DB_PASSWORD') ?: 'change-me';
$hp2k1_dbname = getenv('DB_NAME') ?: 'team2026';

$connection = mysqli_connect($hp2k1_host, $hp2k1_user, $hp2k1_pass, $hp2k1_dbname);
if (!$connection) {
    echo json_encode([
        'status' => false,
        'message' => 'Database connection failed: ' . mysqli_error()
    ]);
    exit;
}

// Get random girl image
$sqlrandom = 'SELECT url FROM girl ORDER BY RAND() LIMIT 1';
$hp2k1_go = mysqli_query($connection, $sqlrandom);

if (!$hp2k1_go) {
    echo json_encode([
        'status' => false,
        'message' => 'Query failed: ' . mysqli_error()
    ]);
    exit;
}

$row = mysqli_fetch_array($hp2k1_go, MYSQLI_NUM);

// Return JSON response
echo json_encode([
    'status' => true,
    'message' => 'Success',
    'data' => [
        'messages' => [
            [
                'attachment' => [
                    'type' => 'image',
                    'payload' => [
                        'url' => $row[0]
                    ]
                ]
            ],
            [
                'text' => '<3',
                'quick_replies' => [
                    [
                        'title' => 'Xem tiếp!!!',
                        'block_names' => ['HP2k1', 'girl']
                    ]
                ]
            ]
        ]
    ]
]);

mysqli_close($connection);
?>
