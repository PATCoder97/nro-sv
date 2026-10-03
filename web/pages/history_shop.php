<?php
if (!$user) {
    header("Location: /");
    exit();
}

$kmess = 8;
$page = max(1, isset($_GET['page']) ? intval($_GET['page']) : 1);
$start = ($page - 1) * $kmess;

// Lấy tất cả các vật phẩm người dùng đã mua (dựa vào users_buy)
$sql = "SELECT cvh_sell_item.*, item_template.NAME 
        FROM cvh_sell_item 
        LEFT JOIN item_template ON cvh_sell_item.item = item_template.id";
$result = $CVH->query($sql);

$player = $CVH->player($user['id']);
$allUsers = [];

while ($row = $result->fetch_assoc()) {
    $usersBuyArray = json_decode($row["users_buy"], true);
    if (is_array($usersBuyArray)) {
        foreach ($usersBuyArray as $userBuy) {
            if ($userBuy['uid'] == $player['id']) {
                // Dữ liệu status và time đã tách riêng
                $allUsers[] = [
                    'item_name' => $row['NAME'],
                    'status' => $row['status'],
                    'time' => $row['time']
                ];
            }
        }
    }
}

$tong = count($allUsers);
$urth = array_slice($allUsers, $start, $kmess);
?>


<div id="shop" class="py-3 bg-component">
    <div class="container-xxl p-3 p-lg-2">
        <div class="alert alert-success alert-dismissable p-3">
            <h4 class="alert-heading">Lịch sử mua vật phẩm!</h4>
            <b class="text-danger">Để tránh phát sinh lỗi không mong muốn khi mua vật phẩm anh em vui lòng đăng xuất
                khỏi game và vào lại sau khi mua vật phẩm thành công, cảm ơn!</b>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Vật Phẩm</th>
                            <th scope="col">Trạng Thái</th>
                            <th scope="col">Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($urth as $index => $cvh) {
                            $status = $cvh['status'] == 0 ? '<span class="badge bg-primary">Chưa nhận</span>' : '<span class="badge bg-success">Đã nhận</span>';
                            $time = date('Y-m-d H:i:s', $cvh['time']);
                        ?>
                        <tr>
                            <td><?php echo $start + $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($cvh['item_name']); ?></td>
                            <td><?php echo $status; ?></td>
                            <td><?php echo htmlspecialchars($time); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-md-flex align-items-center justify-content-end py-2">
            <?php
                  if ($tong > $kmess) {
                    echo '' . $CVH->phantrang('/lich-su-mua?', $start, $tong, $kmess) . '';
                  }
                ?>
        </div>
    </div>
</div>