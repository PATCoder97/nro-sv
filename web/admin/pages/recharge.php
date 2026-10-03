<?php
$kmess = 16; // Số phim hiện trong mỗi page
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
$result = mysqli_query($CVH->connect_db(), "SELECT cvh_recharge.* FROM cvh_recharge INNER JOIN account ON cvh_recharge.account_id = account.id ORDER BY time DESC LIMIT $start, $kmess");
$tong = mysqli_num_rows(mysqli_query($CVH->connect_db(), "SELECT cvh_recharge.* FROM cvh_recharge INNER JOIN account ON cvh_recharge.account_id = account.id"));

?>
<div id="content" class="app-content">
    <div class="row">
        <div class="col-xl-12 mb-3">

            <div class="card h-100">
                <div class="card card-body">
                    <div class="table-responsive">
                        <table class="table search-table align-middle text-nowrap">
                            <thead class="header-item">
                                <tr>
                                    <th>ID</th>
                                    <th>MÃ THẺ</th>
                                    <th>SERIAL</th>
                                    <th>MỆNH GIÁ</th>
                                    <th>LOẠI THẺ</th>
                                    <th>THỰC NHẬN</th>
                                    <th>TRẠNG THÁI</th>
                                    <th>THỜI GIAN</th>
                                </tr>
                            </thead>
                            <tbody id="result">
                                <?php
                        $i = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr class="search-items">
                                    <td>
                                        <span class="usr-email-addr">
                                            <?php echo $row["id"]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-email-addr">
                                            <?php echo $row["code"]; ?>đ
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-location">
                                            <?php echo $row["serial"]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-ph-no">
                                            <?php echo number_format($row['amount']); ?>đ
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-ph-no">
                                            <?php echo $row['type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-ph-no">
                                            <?php echo checkAmount($row['amount_real']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-ph-no">
                                            <?php echo getStatus($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-ph-no">
                                            <?php echo $row['time']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php }
                        } else { ?>
                                <tr class="text-center">
                                    <td colspan='8'>
                                        <img src="https://cdn-icons-png.flaticon.com/128/7466/7466139.png" width="50"
                                            class="img-fluid">
                                        <p class="pt-3"><b>Không có dữ liệu</b></p>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex align-items-center justify-content-end py-3">
                        <?php
                          if ($tong > $kmess) {
                             echo '<center>' . $CVH->phantrang('recharge?', $start, $tong, $kmess) . '</center>';
                         }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>