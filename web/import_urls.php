<?php
// Script import danh sách URL vào database
header('Content-Type: text/html; charset=utf-8');

// Database connection
$host = getenv('DB_HOST') ?: 'database';
$user = getenv('DB_USER') ?: 'teamobi';
$pass = getenv('DB_PASSWORD') ?: 'change-me';
$dbname = getenv('DB_NAME') ?: 'team2026';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_error());
}
mysqli_set_charset($conn, "utf8");

// Tạo bảng nếu chưa có (chỉ 2 cột: id và url)
$createTable = "CREATE TABLE IF NOT EXISTS girl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(500) NOT NULL
)";
mysqli_query($conn, $createTable);

$importedCount = 0;
$failedCount = 0;
$importedUrls = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urls'])) {
    $urls = trim($_POST['urls']);
    
    if (!empty($urls)) {
        echo "<h3>Đang import URLs...</h3>";
        
        // Tách URLs theo dòng
        $urlArray = explode("\n", $urls);
        
        foreach ($urlArray as $url) {
            $url = trim($url);
            
            if (!empty($url)) {
                // Kiểm tra URL hợp lệ
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    // Kiểm tra xem URL đã tồn tại chưa
                    $stmt = mysqli_prepare($conn, "SELECT id FROM girl WHERE url = ?");
                    mysqli_stmt_bind_param($stmt, "s", $url);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) == 0) {
                        // URL chưa tồn tại, thêm vào database
                        $insertStmt = mysqli_prepare($conn, "INSERT INTO girl (url) VALUES (?)");
                        mysqli_stmt_bind_param($insertStmt, "s", $url);
                        
                        if (mysqli_stmt_execute($insertStmt)) {
                            echo "<p style='color: green;'>✅ Import thành công: <a href='$url' target='_blank'>$url</a></p>";
                            $importedCount++;
                            $importedUrls[] = $url;
                        } else {
                            echo "<p style='color: red;'>❌ Lỗi import: $url - " . mysqli_error($conn) . "</p>";
                            $failedCount++;
                        }
                        mysqli_stmt_close($insertStmt);
                    } else {
                        echo "<p style='color: orange;'>⚠️ URL đã tồn tại: $url</p>";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo "<p style='color: red;'>❌ URL không hợp lệ: $url</p>";
                    $failedCount++;
                }
            }
        }
        
        echo "<hr>";
        echo "<h3>📊 Kết quả import:</h3>";
        echo "<p>✅ Import thành công: $importedCount</p>";
        echo "<p>❌ Import thất bại: $failedCount</p>";
        
        if (!empty($importedUrls)) {
            echo "<h4>🔗 Danh sách URL đã import:</h4>";
            echo "<textarea rows='10' cols='80'>";
            foreach ($importedUrls as $url) {
                echo $url . "\n";
            }
            echo "</textarea>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import URLs vào Database</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .example { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📥 Import URLs vào Database</h1>
        
        <div class="info">
            <h3>ℹ️ Hướng dẫn:</h3>
            <ul>
                <li>Nhập danh sách URL ảnh (mỗi URL một dòng)</li>
                <li>URL sẽ được kiểm tra tính hợp lệ</li>
                <li>URL trùng lặp sẽ được bỏ qua</li>
                <li>URL sẽ được lưu vào database</li>
            </ul>
        </div>
        
        <div class="example">
            <h4>📝 Ví dụ format:</h4>
            <pre>https://example.com/image1.jpg
https://example.com/image2.png
https://example.com/image3.gif</pre>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="urls">🔗 Danh sách URL ảnh (mỗi URL một dòng):</label>
                <textarea name="urls" id="urls" rows="15" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.png&#10;https://example.com/image3.gif"></textarea>
            </div>
            
            <button type="submit">📥 Import vào Database</button>
        </form>
        
        <hr>
        
        <h3>📊 Thống kê Database:</h3>
        <?php
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM girl");
        $row = mysqli_fetch_assoc($result);
        echo "<p>Tổng số ảnh trong database: <strong>" . $row['total'] . "</strong></p>";
        
        $result = mysqli_query($conn, "SELECT * FROM girl ORDER BY id DESC LIMIT 10");
        if (mysqli_num_rows($result) > 0) {
            echo "<h4>🖼️ 10 ảnh mới nhất:</h4>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div style='margin-bottom: 10px;'>";
                echo "<a href='" . $row['url'] . "' target='_blank'>ID: " . $row['id'] . "</a>";
                echo "</div>";
            }
        }
        ?>
        
        <hr>
        
        <h3>🔧 Công cụ khác:</h3>
        <p><a href="upload_images.php" style="color: #007bff;">📤 Upload ảnh trực tiếp</a></p>
        <p><a href="export_urls.php" style="color: #007bff;">📤 Export danh sách URL</a></p>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?> 
