<?php
// AJAX handler cho upload ảnh
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
    
    // Tạo bảng girl nếu chưa có
    $createTableGirl = "CREATE TABLE IF NOT EXISTS girl (
        id INT AUTO_INCREMENT PRIMARY KEY,
        url VARCHAR(500) NOT NULL
    )";
    mysqli_query($conn, $createTableGirl);
    
    // Tạo bảng sexy nếu chưa có
    $createTableSexy = "CREATE TABLE IF NOT EXISTS sexy (
        id INT AUTO_INCREMENT PRIMARY KEY,
        url VARCHAR(500) NOT NULL
    )";
    mysqli_query($conn, $createTableSexy);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

// API Keys
$apiKeys = [
    'imgbb' => getenv('IMGBB_API_KEY') ?: '',
    'freeimage' => getenv('FREEIMAGE_API_KEY') ?: ''
];

function uploadToImgBB($imagePath, $apiKey) {
    $imageData = base64_encode(file_get_contents($imagePath));
    $data = ['key' => $apiKey, 'image' => $imageData];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => new CURLFile($imagePath)]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
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
    $data = ['key' => $apiKey, 'source' => $imageData, 'format' => 'json'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://freeimage.host/api/1/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
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

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
        $files = $_FILES['images'];
        $service = $_POST['service'] ?? 'postimages';
        $category = $_POST['category'] ?? 'girl'; // Thêm category để chọn bảng
        
        if ($files['error'] === UPLOAD_ERR_OK) {
            $tempName = $files['tmp_name'];
            $fileName = $files['name'];
            $fileSize = $files['size'];
            
            // Kiểm tra file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($tempName);
            
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Không phải file ảnh hợp lệ']);
                exit;
            }
            
            // Kiểm tra kích thước (max 10MB)
            if ($fileSize > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File quá lớn (>10MB)']);
                exit;
            }
            
            $uploadedUrl = false;
            
            // Upload theo service được chọn
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
                    echo json_encode([
                        'success' => true, 
                        'message' => "Upload thành công vào bảng $tableName",
                        'url' => $uploadedUrl,
                        'filename' => $fileName,
                        'category' => $category
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi lưu database: ' . mysqli_error($conn)]);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload thất bại']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi upload file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?> 
