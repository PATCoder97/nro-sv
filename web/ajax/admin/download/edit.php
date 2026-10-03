<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
file_put_contents('debug_post.txt', print_r($_POST, true));
if (!empty($user) && !empty($user['is_admin'])) {
    if (
        isset($_POST['id']) &&
        isset($_POST['text']) &&
        isset($_POST['url']) &&
        isset($_POST['link']) &&
        !empty($_POST['image']) &&
        !empty($_POST['type'])
    ) {
        $current_data = $CVH->get_row("SELECT download FROM cvh_setting WHERE id = 1");
        $current_download = !empty($current_data) ? json_decode($current_data['download'], true) : [];
        $id = intval($_POST['id']);
        $found = false;

        foreach ($current_download as &$item) {
			if ($item['id'] == $id) {
				$item['image'] = $_POST['image'];
				$item['link'] = $_POST['link'];
				$item['type'] = $_POST['type'];
				$item['description'] = [
					"text" => $_POST['text'],
					"link" => $_POST['url']
				];
				$found = true;
			break;
		}
	}

        if ($found) {
            $json_data = json_encode($current_download, JSON_UNESCAPED_UNICODE);
            $table = "cvh_setting";
            $data = array(
                "download" => $json_data
            );
            $where = 'id = 1';
            if ($CVH->update($table, $data, $where)) {
                $CVH->Ex(true, "Cập nhật thành công!");
            } else {
                $CVH->Ex(false, "Có lỗi xảy ra trong quá trình cập nhật.");
            }
        } else {
            $CVH->Ex(false, "Không tìm thấy ID cần sửa!");
        }
    } else {
        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
    }
} else {
    $CVH->Ex(false, "Bạn chưa đăng nhập!");
}
