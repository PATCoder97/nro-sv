<?php
// Script upload ảnh và import vào database
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

// Tạo bảng sexy nếu chưa có
$createTableSexy = "CREATE TABLE IF NOT EXISTS sexy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(500) NOT NULL
)";
mysqli_query($conn, $createTable);

// Cấu hình upload
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Danh sách dịch vụ upload ảnh
$imageHostingServices = [
    'postimages' => 'https://postimages.org/json/rr',
    'imgbb' => 'https://api.imgbb.com/1/upload',
    'freeimage' => 'https://freeimage.host/api/1/upload'
];

// API Keys (bạn cần đăng ký để lấy key)
$apiKeys = [
    'imgbb' => getenv('IMGBB_API_KEY') ?: '',
    'freeimage' => getenv('FREEIMAGE_API_KEY') ?: ''
];



function uploadToImgBB($imagePath, $apiKey) {
    $imageData = base64_encode(file_get_contents($imagePath));
    
    $data = [
        'key' => $apiKey,
        'image' => $imageData
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout 30 giây
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout kết nối 10 giây
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['data']['url'])) {
            return $result['data']['url'];
        }
    }
    return false;
}

function uploadToPostImages($imagePath) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://postimages.org/json/rr');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'file' => new CURLFile($imagePath)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout 30 giây
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout kết nối 10 giây
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['url'])) {
            return $result['url'];
        }
    }
    return false;
}

function uploadToFreeImage($imagePath, $apiKey) {
    $imageData = base64_encode(file_get_contents($imagePath));
    
    $data = [
        'key' => $apiKey,
        'source' => $imageData,
        'format' => 'json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://freeimage.host/api/1/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout 30 giây
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout kết nối 10 giây
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['image']['url'])) {
            return $result['image']['url'];
        }
    }
    return false;
}



// Xử lý upload
$uploadedCount = 0;
$failedCount = 0;
$uploadedUrls = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $files = $_FILES['images'];
    $service = $_POST['service'] ?? 'postimages';
    $category = $_POST['category'] ?? 'girl'; // Thêm category để chọn bảng
    $totalFiles = count($files['name']);
    
    echo "<h3>🚀 Bắt đầu upload $totalFiles ảnh...</h3>";
    echo "<div id='progress' style='margin: 20px 0;'>";
    echo "<div style='background: #f0f0f0; border-radius: 10px; padding: 3px;'>";
    echo "<div id='progress-bar' style='background: #007bff; height: 20px; border-radius: 8px; width: 0%; transition: width 0.3s;'></div>";
    echo "</div>";
    echo "<p id='progress-text'>Đang xử lý file 1/$totalFiles...</p>";
    echo "</div>";
    
    // Tăng timeout cho script
    set_time_limit(0); // Không giới hạn thời gian
    ini_set('max_execution_time', 0);
    
    for ($i = 0; $i < $totalFiles; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tempName = $files['tmp_name'][$i];
            $fileName = $files['name'][$i];
            $fileSize = $files['size'][$i];
            $currentFile = $i + 1;
            
            // Cập nhật progress
            $progress = round(($currentFile / $totalFiles) * 100);
            echo "<script>
                document.getElementById('progress-bar').style.width = '$progress%';
                document.getElementById('progress-text').textContent = 'Đang xử lý file $currentFile/$totalFiles... ($fileName)';
            </script>";
            ob_flush();
            flush();
            
            // Kiểm tra file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($tempName);
            
            if (!in_array($fileType, $allowedTypes)) {
                echo "<p style='color: red;'>❌ $fileName - Không phải file ảnh hợp lệ</p>";
                $failedCount++;
                continue;
            }
            
            // Kiểm tra kích thước (max 10MB)
            if ($fileSize > 10 * 1024 * 1024) {
                echo "<p style='color: red;'>❌ $fileName - File quá lớn (>10MB)</p>";
                $failedCount++;
                continue;
            }
            
            echo "<p>📤 Đang upload: $fileName ($currentFile/$totalFiles)</p>";
            
            $uploadedUrl = false;
            
            // Upload theo service được chọn với timeout
            switch ($service) {
                case 'imgbb':
                    $uploadedUrl = uploadToImgBB($tempName, $apiKeys['imgbb']);
                    break;
                    
                case 'freeimage':
                    $uploadedUrl = uploadToFreeImage($tempName, $apiKeys['freeimage']);
                    break;
                    
                case 'postimages':
                default:
                    $uploadedUrl = uploadToPostImages($tempName);
                    break;
            }
            
            if ($uploadedUrl) {
                // Lưu vào database theo category được chọn
                $tableName = ($category === 'sexy') ? 'sexy' : 'girl';
                $stmt = mysqli_prepare($conn, "INSERT INTO $tableName (url) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "s", $uploadedUrl);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo "<p style='color: green;'>✅ $fileName - Upload thành công vào bảng $tableName!</p>";
                    $uploadedCount++;
                    $uploadedUrls[] = $uploadedUrl;
                } else {
                    echo "<p style='color: red;'>❌ $fileName - Lỗi lưu database: " . mysqli_error($conn) . "</p>";
                    $failedCount++;
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "<p style='color: red;'>❌ $fileName - Upload thất bại</p>";
                $failedCount++;
            }
            
            // Nghỉ 0.5 giây giữa các upload để tránh spam
            usleep(500000);
        } else {
            echo "<p style='color: red;'>❌ Lỗi upload file</p>";
            $failedCount++;
        }
    }
    
    echo "<script>
        document.getElementById('progress-bar').style.width = '100%';
        document.getElementById('progress-text').textContent = 'Hoàn thành!';
    </script>";
    
    echo "<hr>";
    echo "<h3>📊 Kết quả:</h3>";
    echo "<p>✅ Upload thành công: <strong>$uploadedCount</strong></p>";
    echo "<p>❌ Upload thất bại: <strong>$failedCount</strong></p>";
    echo "<p>📈 Tỷ lệ thành công: <strong>" . round(($uploadedCount / $totalFiles) * 100, 1) . "%</strong></p>";
    
    if (!empty($uploadedUrls)) {
        echo "<h4>🔗 Danh sách URL đã upload:</h4>";
        echo "<textarea rows='10' cols='80' readonly>";
        foreach ($uploadedUrls as $url) {
            echo $url . "\n";
        }
        echo "</textarea>";
        
        echo "<p><strong>💡 Tip:</strong> Copy danh sách URL trên để backup hoặc chia sẻ.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Ảnh và Import vào Database</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📤 Upload Ảnh và Import vào Database</h1>
        
        <div class="info">
            <h3>ℹ️ Hướng dẫn:</h3>
            <ul>
                <li>Chọn dịch vụ upload ảnh (PostImages không cần API key)</li>
                <li>Chọn file ảnh (JPG, PNG, GIF, max 10MB)</li>
                <li>Ảnh sẽ được upload và link sẽ được lưu vào database</li>
            </ul>
        </div>
        
        <div class="warning">
            <h3>⚠️ Lưu ý:</h3>
            <ul>
                <li><strong>PostImages</strong>: Không cần API key, upload miễn phí</li>
                <li><strong>ImgBB</strong>: Cần đăng ký API key tại <a href="https://api.imgbb.com/" target="_blank">https://api.imgbb.com/</a></li>
                <li><strong>FreeImage</strong>: Cần đăng ký API key tại <a href="https://freeimage.host/" target="_blank">https://freeimage.host/</a></li>
            </ul>
        </div>
        
        <div class="info">
            <h3>⚡ Để upload nhanh hơn:</h3>
            <ul>
                <li><strong>Chọn ít file hơn</strong>: Upload từng 10-20 file một lần</li>
                <li><strong>Dùng PostImages</strong>: Thường nhanh hơn các dịch vụ khác</li>
                <li><strong>Kiểm tra kết nối</strong>: Đảm bảo internet ổn định</li>
                <li><strong>File nhỏ hơn</strong>: Nén ảnh trước khi upload</li>
            </ul>
        </div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category">📂 Chọn danh mục:</label>
                <select name="category" id="category">
                    <option value="girl">👧 Girl (Ảnh gái xinh)</option>
                    <option value="sexy">🔥 Sexy (Ảnh sexy)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="service">🌐 Chọn dịch vụ upload:</label>
                <select name="service" id="service">
                    <option value="postimages">PostImages (Miễn phí, không cần API key)</option>
                    <option value="imgbb">ImgBB (Cần API key)</option>
                    <option value="freeimage">FreeImage (Cần API key)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="images">📁 Chọn file ảnh (có thể chọn nhiều):</label>
                <input type="file" name="images[]" id="images" multiple accept="image/*" required>
            </div>
            
            <button type="button" onclick="startUpload()" id="uploadBtn">🚀 Upload và Import vào Database</button>
        </form>
        
        <hr>
        
        <h3>📊 Thống kê Database:</h3>
        <?php
        // Thống kê bảng girl
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM girl");
        $row = mysqli_fetch_assoc($result);
        $girlCount = $row['total'];
        
        // Thống kê bảng sexy
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM sexy");
        $row = mysqli_fetch_assoc($result);
        $sexyCount = $row['total'];
        
        echo "<p>👧 Ảnh Girl: <strong>$girlCount</strong></p>";
        echo "<p>🔥 Ảnh Sexy: <strong>$sexyCount</strong></p>";
        echo "<p>📈 Tổng cộng: <strong>" . ($girlCount + $sexyCount) . "</strong></p>";
        
        // Hiển thị 5 ảnh mới nhất từ bảng girl
        $result = mysqli_query($conn, "SELECT * FROM girl ORDER BY id DESC LIMIT 5");
        if (mysqli_num_rows($result) > 0) {
            echo "<h4>👧 5 ảnh Girl mới nhất:</h4>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div style='margin-bottom: 10px;'>";
                echo "<a href='" . $row['url'] . "' target='_blank'>Girl ID: " . $row['id'] . "</a>";
                echo "</div>";
            }
        }
        
        // Hiển thị 5 ảnh mới nhất từ bảng sexy
        $result = mysqli_query($conn, "SELECT * FROM sexy ORDER BY id DESC LIMIT 5");
        if (mysqli_num_rows($result) > 0) {
            echo "<h4>🔥 5 ảnh Sexy mới nhất:</h4>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div style='margin-bottom: 10px;'>";
                echo "<a href='" . $row['url'] . "' target='_blank'>Sexy ID: " . $row['id'] . "</a>";
                echo "</div>";
            }
        }
        ?>
    </div>
    
    <!-- Modal Upload Progress -->
    <div id="uploadModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📤 Đang Upload Ảnh</h3>
            </div>
            <div class="modal-body">
                <div id="uploadProgress">
                    <div class="progress-bar">
                        <div id="progressBar" class="progress-fill"></div>
                    </div>
                    <p id="progressText">Đang chuẩn bị...</p>
                    <p id="progressDetail">0/0 files</p>
                </div>
                <div id="uploadLog" class="upload-log"></div>
            </div>
            <div class="modal-footer">
                <button id="cancelBtn" onclick="cancelUpload()" class="btn-danger">❌ Hủy</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Success -->
    <div id="successModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header success">
                <h3>✅ Upload Thành Công!</h3>
            </div>
            <div class="modal-body">
                <div id="successStats"></div>
                <div id="successUrls"></div>
            </div>
            <div class="modal-footer">
                <button onclick="closeSuccessModal()" class="btn-success">🎉 Đóng</button>
            </div>
        </div>
    </div>
    
    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            background: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-header.success {
            background: #28a745;
        }
        
        .modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            text-align: right;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .upload-log {
            margin-top: 15px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }
        
        .log-success { color: #28a745; }
        .log-error { color: #dc3545; }
        .log-info { color: #007bff; }
        
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .btn-danger:hover { background: #c82333; }
        .btn-success:hover { background: #218838; }
    </style>
    
    <script>
        let uploadInProgress = false;
        let uploadAborted = false;
        let successCount = 0;
        let failedCount = 0;
        let totalFiles = 0;
        
        function startUpload() {
            const form = document.getElementById('uploadForm');
            const files = document.getElementById('images').files;
            const service = document.getElementById('service').value;
            const category = document.getElementById('category').value;
            
            if (files.length === 0) {
                alert('Vui lòng chọn file ảnh!');
                return;
            }
            
            // Hiển thị modal
            document.getElementById('uploadModal').style.display = 'block';
            document.getElementById('uploadBtn').disabled = true;
            uploadInProgress = true;
            uploadAborted = false;
            successCount = 0;
            failedCount = 0;
            totalFiles = files.length;
            
            // Reset progress
            document.getElementById('progressBar').style.width = '0%';
            document.getElementById('progressText').textContent = 'Đang chuẩn bị...';
            document.getElementById('progressDetail').textContent = `0/${files.length} files`;
            document.getElementById('uploadLog').innerHTML = '';
            
            // Bắt đầu upload
            uploadFiles(files, service, category, 0);
        }
        
        function uploadFiles(files, service, category, currentIndex) {
            if (uploadAborted || currentIndex >= files.length) {
                if (uploadAborted) {
                    addLog('Upload đã bị hủy', 'error');
                } else {
                    finishUpload();
                }
                return;
            }
            
            const file = files[currentIndex];
            const progress = ((currentIndex + 1) / files.length) * 100;
            
            // Cập nhật progress
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressText').textContent = `Đang upload: ${file.name}`;
            document.getElementById('progressDetail').textContent = `${currentIndex + 1}/${files.length} files`;
            
            addLog(`📤 Uploading: ${file.name}`, 'info');
            
            // Tạo FormData
            const formData = new FormData();
            formData.append('images', file);
            formData.append('service', service);
            formData.append('category', category);
            
            // Gửi request AJAX
            fetch('?request=upload-ajax', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    addLog(`✅ ${file.name} - Upload thành công vào bảng ${data.category}!`, 'success');
                    successCount++;
                } else {
                    addLog(`❌ ${file.name} - ${data.message}`, 'error');
                    failedCount++;
                }
                
                // Upload file tiếp theo sau 0.5 giây
                setTimeout(() => {
                    uploadFiles(files, service, category, currentIndex + 1);
                }, 500);
            })
            .catch(error => {
                addLog(`❌ ${file.name} - Lỗi: ${error.message}`, 'error');
                setTimeout(() => {
                    uploadFiles(files, service, category, currentIndex + 1);
                }, 500);
            });
        }
        
        function addLog(message, type) {
            const log = document.getElementById('uploadLog');
            const logEntry = document.createElement('div');
            logEntry.className = `log-${type}`;
            logEntry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            log.appendChild(logEntry);
            log.scrollTop = log.scrollHeight;
        }
        
        function cancelUpload() {
            uploadAborted = true;
            addLog('🛑 Upload đã bị hủy bởi người dùng', 'error');
            setTimeout(() => {
                document.getElementById('uploadModal').style.display = 'none';
                document.getElementById('uploadBtn').disabled = false;
                uploadInProgress = false;
            }, 1000);
        }
        
        function finishUpload() {
            addLog('🎉 Upload hoàn thành!', 'success');
            setTimeout(() => {
                document.getElementById('uploadModal').style.display = 'none';
                document.getElementById('uploadBtn').disabled = false;
                uploadInProgress = false;
                
                // Hiển thị modal thành công
                showSuccessModal();
            }, 1000);
        }
        
        function showSuccessModal() {
            // Lấy thống kê từ server
            fetch('?request=get-upload-stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('successStats').innerHTML = `
                    <p><strong>📊 Kết quả:</strong></p>
                    <p>✅ Upload thành công: <strong>${successCount}</strong></p>
                    <p>❌ Upload thất bại: <strong>${failedCount}</strong></p>
                    <p>📈 Tỷ lệ thành công: <strong>${totalFiles > 0 ? Math.round((successCount / totalFiles) * 100) : 0}%</strong></p>
                    <hr>
                    <p><strong>📊 Thống kê Database:</strong></p>
                    <p>👧 Ảnh Girl: <strong>${data.girl_count}</strong></p>
                    <p>🔥 Ảnh Sexy: <strong>${data.sexy_count}</strong></p>
                    <p>📈 Tổng cộng: <strong>${data.total_count}</strong></p>
                `;
                
                let urlsText = '';
                if (data.recent_girl_urls && data.recent_girl_urls.length > 0) {
                    urlsText += '👧 Girl URLs:\n' + data.recent_girl_urls.join('\n') + '\n\n';
                }
                if (data.recent_sexy_urls && data.recent_sexy_urls.length > 0) {
                    urlsText += '🔥 Sexy URLs:\n' + data.recent_sexy_urls.join('\n');
                }
                
                if (urlsText) {
                    document.getElementById('successUrls').innerHTML = `
                        <p><strong>🔗 URLs mới nhất:</strong></p>
                        <textarea rows="8" cols="50" readonly>${urlsText}</textarea>
                    `;
                }
                
                document.getElementById('successModal').style.display = 'block';
            });
        }
        
        function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            location.reload(); // Reload để cập nhật thống kê
        }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?> 
