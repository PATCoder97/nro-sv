<?php
$request = isset($_GET['request']) ? $_GET['request'] : '';
// echo '<div class="position-relative">';
// echo "Cannot GET /". $request;
// echo '</div>';
?>
<div class="bg-component error-page">
    <div class="error-page-content">
        <div class="error-img">
            <div class="error-img-code">404</div>
            <img src="/assets/img/page/404.svg" alt="">
        </div>
        <h1>Oops!</h1>
        <h3>Không thể tìm thấy!</h3>
        <a onclick="history.back();" class="btn btn-theme">Quay Lại</a>
    </div>
</div>