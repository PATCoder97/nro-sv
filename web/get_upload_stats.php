<?php
// Lấy thống kê upload
header('Content-Type: application/json; charset=utf-8');

// Database connection
$host = getenv('DB_HOST') ?: 'database';
$user = getenv('DB_USER') ?: 'teamobi';
$pass = getenv('DB_PASSWORD') ?: 'change-me';
$dbname = getenv('DB_NAME') ?: 'team2026';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
mysqli_set_charset($conn, "utf8");

// Lấy thống kê tổng quan
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM girl");
$row = mysqli_fetch_assoc($result);
$totalImages = $row['total'];

// Lấy 10 URLs mới nhất
$result = mysqli_query($conn, "SELECT url FROM girl ORDER BY id DESC LIMIT 10");
$recentUrls = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recentUrls[] = $row['url'];
}

// Tính toán thống kê (giả định)
$successCount = $totalImages; // Giả sử tất cả đều thành công
$failedCount = 0; // Có thể tính từ session hoặc log
$successRate = $totalImages > 0 ? 100 : 0;

echo json_encode([
    'total_images' => $totalImages,
    'success_count' => $successCount,
    'failed_count' => $failedCount,
    'success_rate' => $successRate,
    'recent_urls' => $recentUrls
]);

mysqli_close($conn);
?> 
