<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if (!$user['is_admin']) {
    header("Location: /");
    exit;
} else {
    if ($_POST['type'] == 'Del_Post' && isset($_POST['id'])) {
        $id_to_delete = intval($_POST['id']);
        $current_data = $CVH->get_row("SELECT download FROM cvh_setting WHERE id = 1");
        $current_download = !empty($current_data) ? json_decode($current_data['download'], true) : [];
        $current_download = array_filter($current_download, function($entry) use ($id_to_delete) {
            return $entry['id'] !== $id_to_delete;
        });
        $json_data = json_encode(array_values($current_download), JSON_UNESCAPED_UNICODE);
        
        $table = "cvh_setting"; 
        $data = array(
            "download" => $json_data
        ); 
        $where = 'id = 1';  
        if ($CVH->update($table, $data, $where)) {
            $CVH->Ex(true, "Xóa bài viết có ID #". $id_to_delete ." thành công!");
        } else {
            $CVH->Ex(false, "Có lỗi xảy ra trong quá trình xóa bài viết.");
        }
    } else {
        $CVH->Ex(false, "ID không hợp lệ!");
    }
}
?>
