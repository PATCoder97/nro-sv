<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";

$message = loc($CVH->FormatString($_POST['message']));
if($user){
     if($message){

        $data = array(
            "id" => null,
            "username" => $user['username'],
            "message" => $message,
            "created_at" => time()
        );

      $CVH->insert("cvh_messages" , $data);   
    }
}
?>