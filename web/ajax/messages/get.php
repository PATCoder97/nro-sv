<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";



$rows = $CVH->get_list("SELECT cm.*, p.name, p.head, a.is_admin FROM cvh_messages cm JOIN account a ON cm.username = a.username JOIN player p ON a.id = p.account_id ORDER BY cm.id ASC;");
$time2 = null;

if (!empty($rows)) {
    foreach ($rows as $row) {
        if ($user && $row['username'] == $user['username']) {
            echo '<div class="widget-chat-item reply">';
            echo '<div class="widget-chat-content">';
            echo '<div class="widget-chat-message last">' . htmlspecialchars($row["message"]) . '</div>';
            echo '<div class="widget-chat-status"><b>' . htmlspecialchars($CVH->time_Ago($row["created_at"])) . '</b></div>';
            echo '</div></div>';
        } else {
            
            echo '<div class="widget-chat-item">';
            echo '<div class="widget-chat-media"><img src="/images/avatar/'.htmlspecialchars($row['head']).'.png" alt="" /></div>';
            echo '<div class="widget-chat-content">';
            echo '<div class="widget-chat-name"><b class="' . ($row['is_admin'] ? 'text-info' : '') . '">' . htmlspecialchars($row["name"]) . ' ' . ($row['is_admin'] ? ' <i class="fa fa-check-circle"></i>' : '') . ' </b>  - ' . htmlspecialchars($CVH->time_Ago($row["created_at"])) . '</div>';
            echo '<div class="widget-chat-message last">' . htmlspecialchars($row["message"]) . '</div>';
            echo '</div></div>';
        }
    }
} else {
    echo '<div class="text-center mt-5 pt-5">
            <img src="/images/message.png" width="50" class="img-fluid">
            <p class="pt-3"><b>Chưa có tin nhắn nào được gửi</b></p>
         </div>';
}
?>