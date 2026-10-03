<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

if (empty($user) || empty($user['is_admin'])) {
    $CVH->Ex(false, "Bạn không có quyền thực hiện thao tác này!");
    exit();
}

$title = $_POST["title"];
$author = $_POST["author"];
$description = $_POST["description"];
$keywords = $_POST["keywords"];
$amount_mtv = $_POST["amount_mtv"];
$thongbao = $_POST["thongbao"];
$favicon = $_POST["favicon"];
$logo = $_POST["logo"];
$navbar = $_POST["navbar"];
$nd_thongbao = $_POST["nd_thongbao"];
$background = $_POST["background"];
$size_logo = $_POST["size_logo"];
$banner = $_POST["banner"];

if (!empty($size_logo) &&!empty($background) && !empty($favicon) && isset($logo) &&!empty($title) && !empty($author) && !empty($description) && !empty($keywords) && !empty($amount_mtv)) {

    $data = array(
        "size_logo" => $size_logo,
        "background" => $background,
        "favicon" => $favicon,
        "logo" => $logo,
        "banner" => $banner,
        "navbar" => $navbar,
        "title" => $title,
        "author" => $author,
        "description" => $description,
        "keywords" => $keywords,
        "amount_mtv" => $amount_mtv,
        "thongbao" => $thongbao,
        "nd_thongbao" => $nd_thongbao
    );
    $where = 'id = 1';


    $table = "cvh_setting";
    $CVH->update($table, $data, $where);
    $CVH->Ex(true, "Cập nhật dữ liệu thành công!");
} else {
    $CVH->Ex(false, "Vui lòng nhập đầy đủ thông tin!");
}
?>
