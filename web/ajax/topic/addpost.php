<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if ($user) {
    if (isset($_POST['title']) && isset($_POST['content']) && !empty($_POST['title']) && !empty($_POST['content'])) {
        if ($user['active'] == 1) {
            $content = $CVH->FormatString($_POST['content']);
            $title = $CVH->FormatString($_POST['title']);
            if ($CVH->canUserPost($user['id'])) {
                $table = "cvh_baiviet";
                $data = array(
                    "id" => null,
                    "title" => $title,
                    "content" => $content,
                    "likes" => "[]",
                    "comments" => "[]",
                    "status" => 1,
                    "poster" => $user['id'],
                    "role" => 1,
                    "time" => time()
                );
                $CVH->insert($table, $data);

                // Công điển cho user
                $table = 'account';
                $data = 'point_post';
                $sodiem = rand(1, 3);
                $where = 'username = "' . $user['username'] . '"';
                sendTele(templateTele("Người dùng: " . $user["username"] . "\nVừa đăng một bài viết\nTiêu đề: " . htmlspecialchars(strip_tags($title)) . "\nNội dung: " . htmlspecialchars(strip_tags($content, '<img>'))));
                $CVH->cong($table, $data, $sodiem, $where);
                $CVH->cong($table, "vnd", 500, $where);

                $CVH->Ex(true, "Đăng bài viết thành công!");
            } else {
                $CVH->Ex(false, "Đã đạt số lượng bài đăng cho phép vui lòng quay lại vào ngày mai!");
            }

        } else {

            $CVH->Ex(false, "Vui lòng kích hoạt tài khoản trước khi đăng bài viết!");

        }

    } else {

        $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");

    }
} else {
    $CVH->Ex(false, "Bạn chưa đăng nhập!");

}