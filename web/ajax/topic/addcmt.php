<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
header('Content-Type: text/html; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if ($user) {
    $id = $_POST['id'];
    $content = loc($CVH->xss($_POST['content']));
    if (isset($_POST['content']) && !empty($_POST['content'])) {

        if ($user['active'] == 1) {

            if ($CVH->checkPost($id)) {

                // Lưu dữ liệu bài viết
                $CVH->addCMT($id, $content, $user['id']);
                $CVH->Ex(true, "Đăng bình luận thành công!");

            } else {

                $CVH->Ex(false, "Bài viết không tồn tại hoặc đã bị xóa!");

            }

        } else {

            $CVH->Ex(false, "Vui lòng kích hoạt tài khoản trước khi bình luận!");

        }
    } else {
        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");

    }

} else {
    $CVH->Ex(false, "Bạn chưa đăng nhập!");

}