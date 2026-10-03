<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if (isset($user['is_admin'])) {
    if (isset($_POST['title']) && isset($_POST['content']) && isset($_POST['avatar']) && !empty($_POST['title']) && !empty($_POST['content']) && !empty($_POST['avatar'])) {
        if (isset($user['is_admin'])) {
            $if_admin = abs($_POST['avatar']);
            $content = $_POST['content'];
            $title = $_POST['title'];

            $table = "cvh_baiviet";
            $data = array(
                "id" => null,
                "title" => $title,
                "if_admin" => getAdminPosts($if_admin),
                "content" => $content,
                "likes" => "[]",
                "comments" => "[]",
                "status" => 1,
                "poster" => $user['id'],
                "role" => 2,
                "time" => time()
            );
            $CVH->insert($table, $data);
            $CVH->Ex(true, "Đăng bài viết thành công!");

        } else {
            $CVH->Ex(false, "Địt mẹ mày cút ngay!");
        }

    } else {
        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
    }
} else {
    $CVH->Ex(false, "Bạn chưa đăng nhập!");
}