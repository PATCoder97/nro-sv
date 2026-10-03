<?php
$kmess = 6; // Số phim hiện trong mỗi page
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
$result = mysqli_query($CVH->connect_db(), "SELECT item_template.*, cvh_sell_item.*  FROM item_template INNER JOIN cvh_sell_item ON item_template.id = cvh_sell_item.item WHERE `active` = 1 ORDER BY cvh_sell_item.id ASC LIMIT $start, $kmess");
$tong = mysqli_num_rows(mysqli_query($CVH->connect_db(), "SELECT item_template.*, cvh_sell_item.*  FROM item_template INNER JOIN cvh_sell_item ON item_template.id = cvh_sell_item.item WHERE `active` = 1"));

// Tạo CSRF token cho user (chỉ khi đã đăng nhập)
$csrf_token = '';
if ($user && isset($user['id'])) {
    $csrf_token = $CVH->getCSRFToken($user['id']);
}
?>
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

<?php if (!$user) { ?>
    <div class="py-3 bg-component">
        <div class="container-xxl p-3 p-lg-2">
            <div class="alert alert-warning alert-dismissable p-3">
                <h4 class="alert-heading">⚠️ Vui lòng đăng nhập!</h4>
                <p>Bạn cần đăng nhập để có thể mua vật phẩm trong shop.</p>
                <hr>
                <p class="mb-0">
                    <a href="/dang-nhap" class="btn btn-primary">Đăng nhập</a>
                    <a href="/dang-ky" class="btn btn-outline-primary">Đăng ký</a>
                </p>
            </div>
        </div>
    </div>
<?php } else { ?>

<div id="shop" class="py-3 bg-component">
    <div class="container-xxl p-3 p-lg-2">
        <style>
        .list-group-item .info-block,
        .list-group-item .additional-info {
            display: flex;
            flex-direction: column;
        }

        .info-block {
            flex-grow: 1;
        }

        .additional-info {
            flex-grow: 2;
            align-items: flex-start;
        }
        </style>
        <div class="alert alert-success alert-dismissable p-3">
            <h4 class="alert-heading">Bán Vật Phẩm Trong Game!</h4>
            <b class="text-danger">Để tránh phát sinh lỗi không mong muốn khi mua vật phẩm anh em vui lòng đăng xuất
                khỏi game và vào lại sau khi mua vật phẩm thành công, cảm ơn!</b>
            <hr>
            <p>Để xem trạng thái đơn hàng hãy truy cập <a href="/lich-su-mua">Lịch sử mua</a></p>
        </div>
        <div class="list-group">
            <?php
                $i = 1;
                if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $cvh_json = $row['options'];
                    $cvh_array = json_decode($cvh_json, true);
            ?>
                    <div class="list-group-item list-group-item-action d-flex align-items-center text-body">
            <div class="w-50px h-50px d-flex align-items-center justify-content-center bg-body rounded p-2">
                <img src="<?php echo getItemIcon($row['icon_id'] ?? 0); ?>" alt="" class="mw-100 mh-100">
            </div>
                <div class="d-flex flex-fill px-4 justify-content-between">
                    <div class="info-block">
                        <div class="fw-semibold"><b><?php echo $row['NAME']; ?></b></div>
                        <?php
                        // Tính tổng lượt mua từ cột users_buy (JSON)
                        $totalBuy = 0;
                        $userBuy = json_decode($row['users_buy'] ?? '[]', true);
                        if (is_array($userBuy)) { $totalBuy = count($userBuy); }

                        if (is_array($cvh_array)) {
                            foreach ($cvh_array as $option) {
                                $param = $option['param'];
                                $option_id = $option['id'];
                                $option_query = mysqli_query($CVH->connect_db(), "SELECT name FROM item_option_template WHERE id = '$option_id'");
                                if ($option_data = mysqli_fetch_assoc($option_query)) {
                                    $option_name = $option_data['name'];
                                    $option_display = str_replace('#', $param, $option_name);
                                    echo '<div class="small text-theme fs-10px">• <b>' . $option_display . '</b></div>';
                                } else {
                                    echo '<div class="small text-theme">• <b>(Tên không tìm thấy)</b></div>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="additional-info">
                        <div class="fw-semibold"><b>Thông tin:</b></div>
                        <div class="small text-body fs-10px">- Hành tinh: <b
                                class="text-success fs-10px"><?php echo getGender($row['gender']); ?></b></div>
                        <div class="small text-body fs-10px">- Giá tiền: <b
                                class="text-danger fs-10px"><?php echo number_format($row['price']); ?>đ</b></div>
                        <div class="small text-body fs-10px">- Đã mua: <b class="text-danger"> <?php echo number_format($totalBuy); ?></b></div>
                        <div class="small text-body fs-10px">- Còn lại: <b
                                class="text-danger"><?php echo number_format($row['slot']); ?></b></div>
                    </div>
                </div>
                <div>
                    <a onclick="BuyNow(<?php echo $row['id']; ?>);" href="javascript:;"><span
                            class="badge bg-theme hii">Mua Ngay</span></a>
                </div>
            </div>
            <?php } } else { ?>
            <div class="text-center py-5 mt-5">
                <img src="https://cdn-icons-png.flaticon.com/128/7466/7466139.png" width="50" class="img-fluid">
                <p class="pt-3"><b>Không có dữ liệu</b></p>
            </div>
            <?php } ?>
        </div>
        <div class="d-flex align-items-center justify-content-end py-5">
            <?php
                if ($tong > $kmess) {
                    echo '<center>' . $CVH->phantrang('shop?', $start, $tong, $kmess) . '</center>';
                }
            ?>
        </div>
    </div>
</div>
<?php } ?>