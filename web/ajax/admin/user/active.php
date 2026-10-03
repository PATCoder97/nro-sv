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
        $status = isset($_POST['status']) ? intval($_POST['status']) : 0;
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (isset($id)) {
            mysqli_query($CVH->connect_db(), "UPDATE account SET active = $status WHERE id = $id");
        } else {
        }
}
?>