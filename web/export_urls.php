<?php
// Script export danh sách URL từ database
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

// Xử lý export
if (isset($_GET['export'])) {
    $format = $_GET['format'] ?? 'txt';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
    
    $sql = "SELECT url FROM girl ORDER BY id DESC";
    if ($limit > 0) {
        $sql .= " LIMIT $limit";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="urls.json"');
        
        $urls = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $urls[] = $row['url'];
        }
        
        echo json_encode($urls, JSON_PRETTY_PRINT);
        exit;
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="urls.txt"');
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo $row['url'] . "\n";
        }
        exit;
    }
}

// Xử lý xóa URL
if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM girl WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $deleteId);
    
    if (mysqli_stmt_execute($stmt)) {
        $deleteMessage = "✅ Đã xóa URL thành công!";
    } else {
        $deleteMessage = "❌ Lỗi xóa URL: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Xử lý xóa tất cả
if (isset($_POST['delete_all']) && $_POST['delete_all'] === 'yes') {
    if (mysqli_query($conn, "TRUNCATE TABLE girl")) {
        $deleteMessage = "✅ Đã xóa tất cả URLs thành công!";
    } else {
        $deleteMessage = "❌ Lỗi xóa tất cả: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export URLs từ Database</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        button:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .url-list { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px; }
        .url-item { margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee; }
        .url-item:last-child { border-bottom: none; }
        .delete-btn { background: #dc3545; color: white; padding: 4px 8px; border: none; border-radius: 3px; cursor: pointer; font-size: 12px; }
        .delete-btn:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📤 Export URLs từ Database</h1>
        
        <?php if (isset($deleteMessage)): ?>
            <div class="info"><?php echo $deleteMessage; ?></div>
        <?php endif; ?>
        
        <div class="info">
            <h3>ℹ️ Hướng dẫn:</h3>
            <ul>
                <li>Export danh sách URL từ database</li>
                <li>Chọn format: TXT (mỗi URL một dòng) hoặc JSON</li>
                <li>Có thể giới hạn số lượng URL export</li>
                <li>Xem và quản lý URLs trong database</li>
            </ul>
        </div>
        
        <h3>📤 Export URLs:</h3>
        <form method="GET" style="display: inline-block;">
            <input type="hidden" name="export" value="1">
            
            <label for="format">📄 Format:</label>
            <select name="format" id="format">
                <option value="txt">TXT (mỗi URL một dòng)</option>
                <option value="json">JSON</option>
            </select>
            
            <label for="limit">📊 Giới hạn (0 = tất cả):</label>
            <input type="number" name="limit" id="limit" value="0" min="0" max="10000">
            
            <button type="submit" class="btn-success">📥 Download</button>
        </form>
        
        <hr>
        
        <h3>📊 Thống kê Database:</h3>
        <?php
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM girl");
        $row = mysqli_fetch_assoc($result);
        $totalUrls = $row['total'];
        echo "<p>Tổng số ảnh trong database: <strong>$totalUrls</strong></p>";
        
        if ($totalUrls > 0) {
            $result = mysqli_query($conn, "SELECT * FROM girl ORDER BY id DESC LIMIT 50");
            if (mysqli_num_rows($result) > 0) {
                echo "<h4>🖼️ Danh sách URLs (tối đa 50 mới nhất):</h4>";
                echo "<div class='url-list'>";
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='url-item'>";
                    echo "<a href='" . $row['url'] . "' target='_blank'>ID: " . $row['id'] . "</a>";
                    echo " <form method='POST' style='display: inline;'>";
                    echo "<input type='hidden' name='delete_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' class='delete-btn' onclick='return confirm(\"Bạn có chắc muốn xóa URL này?\")'>🗑️</button>";
                    echo "</form>";
                    echo "</div>";
                }
                echo "</div>";
                
                if ($totalUrls > 50) {
                    echo "<p><em>Hiển thị 50 URLs mới nhất. Sử dụng Export để xem tất cả.</em></p>";
                }
            }
        } else {
            echo "<p>📭 Database trống. Hãy import hoặc upload ảnh trước.</p>";
        }
        ?>
        
        <hr>
        
        <h3>🗑️ Quản lý Database:</h3>
        <?php if ($totalUrls > 0): ?>
            <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa TẤT CẢ URLs? Hành động này không thể hoàn tác!')">
                <input type="hidden" name="delete_all" value="yes">
                <button type="submit" class="btn-danger">🗑️ Xóa tất cả URLs</button>
            </form>
        <?php endif; ?>
        
        <hr>
        
        <h3>🔧 Công cụ khác:</h3>
        <p><a href="upload_images.php" style="color: #007bff;">📤 Upload ảnh trực tiếp</a></p>
        <p><a href="import_urls.php" style="color: #007bff;">📥 Import danh sách URL</a></p>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?> 
