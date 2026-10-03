<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'].'/cvhvn/autoload.php';

if(!$user['is_admin'] || (!$user['is_super_admin'] && empty($user['perm_maintenance_manager']))){ echo json_encode(['status'=>false,'message'=>'No permission']); exit; }
if (!isset($_POST['csrf_token']) || !$CVH->validateCSRFToken($user['id'], $_POST['csrf_token'])) { echo json_encode(['status'=>false,'message'=>'Token không hợp lệ']); exit; }

// Đường dẫn cố định
$bat = 'C:\\Users\\Administrator\\Desktop\\VENUS\\run.bat';

// Kiểm tra tồn tại file
if (!file_exists($bat)) {
  echo json_encode(['status'=>false,'message'=>'Không tìm thấy file run.bat']); exit;
}

// Cách ổn định: chạy Scheduled Task đã được cấu hình sẵn (khuyên dùng)
// Tạo trước một task tên "VenusRunServer" trỏ tới run.bat và chọn "Run whether user is logged on or not" + Highest privilege
// Khi đó từ PHP chỉ cần gọi schtasks /Run

$taskName = 'VenusRunServer';
$descriptor = [1=>['pipe','w'], 2=>['pipe','w']];
$process = proc_open('schtasks /Run /TN "'.$taskName.'"', $descriptor, $pipes, null, null);
if (is_resource($process)) {
    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $code = proc_close($process);
    if ($code === 0) {
        echo json_encode(['status'=>true,'message'=>'Đã yêu cầu Task Scheduler khởi động server','stdout'=>$stdout]);
    } else {
        // Fallback: thử PowerShell (có thể không chạy nếu dịch vụ không có quyền desktop)
        $ps = 'powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -Command "Start-Process -FilePath \"'.$bat.'\" -WorkingDirectory \"'.dirname($bat).'\" -WindowStyle Hidden"';
        $p2 = proc_open($ps, $descriptor, $pipes);
        if (is_resource($p2)) { $s1 = stream_get_contents($pipes[1]); $s2 = stream_get_contents($pipes[2]); proc_close($p2); }
        echo json_encode(['status'=>false,'message'=>'Task Scheduler chưa được cấu hình. Vui lòng tạo task \"'.$taskName.'\" trỏ tới run.bat và chạy lại.','stdout'=>$stdout,'stderr'=>$stderr,'fallback_stdout'=>($s1??''),'fallback_stderr'=>($s2??'')]);
    }
} else {
    echo json_encode(['status'=>false,'message'=>'Không thể gọi schtasks']);
}
