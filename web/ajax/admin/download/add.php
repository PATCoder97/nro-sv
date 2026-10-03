<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if (isset($user['is_admin'])) {
    if (isset($_POST['text']) && isset($_POST['url']) && isset($_POST['link']) && !empty($_POST['image']) && !empty($_POST['type'])) {
        $current_data = $CVH->get_row("SELECT download FROM cvh_setting WHERE id = 1");
        $current_download = !empty($current_data) ? json_decode($current_data['download'], true) : [];
        $id = empty($current_download) ? 1 : (count($current_download) + 1);
        $image = $_POST['image'];
        $link = $_POST['link'];
        $type = $_POST['type'];
        $new_entry = [
            "id" => $id,
            "image" => $image,
            "link" => $link,
            "type" => $type,
            "description" => [
                "text" => $_POST['text'],
                "link" => $_POST['url']
            ]
        ];
        $current_download[] = $new_entry;
        $json_data = json_encode($current_download, JSON_UNESCAPED_UNICODE);
        $table = "cvh_setting"; 
        $data = array(
            "download" => $json_data
        ); 
        $where = 'id = 1';  
        if ($CVH->update($table, $data, $where)) {
            $CVH->Ex(true, "Đăng bài viết thành công!");
        } else {
            $CVH->Ex(false, "Có lỗi xảy ra trong quá trình đăng bài viết.");
        }
    } else {
        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
    }
} else {
    $CVH->Ex(false, "Bạn chưa đăng nhập!");
}