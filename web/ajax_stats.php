<?php
// AJAX handler cho stats
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = getenv('DB_HOST') ?: 'database';
$user = getenv('DB_USER') ?: 'teamobi';
$pass = getenv('DB_PASSWORD') ?: 'change-me';
$dbname = getenv('DB_NAME') ?: 'team2026';

try {
    $conn = mysqli_connect($host, $user, $pass, $dbname);
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_error());
    }
    mysqli_set_charset($conn, "utf8");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

try {
    // Get total images
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM girl");
    $row = mysqli_fetch_assoc($result);
    $totalImages = $row['total'];
    
    // Get 10 most recent URLs
    $result = mysqli_query($conn, "SELECT url FROM girl ORDER BY id DESC LIMIT 10");
    $recentUrls = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $recentUrls[] = $row['url'];
    }
    
    // Calculate stats
    $successCount = $totalImages;
    $failedCount = 0;
    $successRate = $totalImages > 0 ? 100 : 0;
    
    echo json_encode([
        'total_images' => $totalImages,
        'success_count' => $successCount,
        'failed_count' => $failedCount,
        'success_rate' => $successRate,
        'recent_urls' => $recentUrls
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?> 
