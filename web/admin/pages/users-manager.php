<?php
$kmess = 20; // Số phim hiện trong mỗi page
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
$result = mysqli_query($CVH->connect_db(), "SELECT player.*, account.*  FROM player INNER JOIN account ON player.account_id = account.id ORDER BY account.id DESC LIMIT $start, $kmess");
$tong = mysqli_num_rows(mysqli_query($CVH->connect_db(), "SELECT player.*, account.*  FROM player INNER JOIN account ON player.account_id = account.id"));
?>
<div id="content" class="app-content">
    <div class="row">
        <div class="col-xl-12 mb-3">

            <div class="card h-100">

                <div class="card card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">Quản lý thành viên</div>
                        <div>
                            <button class="btn btn-sm btn-danger" onclick="logoutAllSystem()">Đăng xuất TẤT CẢ phiên (toàn hệ thống)</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-xl-12">
                            <div class="position-relative">
                                <input type="text" class="form-control product-search ps-5" id="search"
                                    placeholder="Tìm kiếm thành viên...">
                                <i
                                    class="fa fa-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                            </div>
                            <script type="text/javascript">
                            // Debounce function
                            function debounce(func, wait) {
                                var timeout;
                                return function executedFunction(...args) {
                                    var later = function() {
                                        clearTimeout(timeout);
                                        func(...args);
                                    };
                                    clearTimeout(timeout);
                                    timeout = setTimeout(later, wait);
                                };
                            }

                            $(document).ready(function() {
                                var searchUsers = debounce(function(query) {
                                    if (query != '') {
                                        $('#result').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</td></tr>');
                                        $.ajax({
                                            url: '/ajax/admin/user/search.php',
                                            method: 'POST',
                                            data: {
                                                query: query,
                                                csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>'
                                            },
                                            success: function(data) {
                                                $('#result').html(data);
                                            },
                                            error: function() {
                                                $('#result').html('<tr><td colspan="7" class="text-center text-danger">Lỗi tìm kiếm</td></tr>');
                                            }
                                        });
                                    } else {
                                        // Load lại dữ liệu gốc khi xóa hết search
                                        window.location.reload();
                                    }
                                }, 300);

                                $("#search").keyup(function() {
                                    var query = $(this).val();
                                    searchUsers(query);
                                });
                            });
                            </script>
                        </div>
                    </div>
                </div>
                <div class="card card-body">
                    <div class="table-responsive">
                        <table class="table search-table align-middle text-nowrap">
                            <thead class="header-item">
                                <tr>
                                    <th>Information</th>
                                    <th>Wallet</th>
                                    <th>Banned</th>
                                    <th>Active</th>
                                    <th>IP</th>
                                    <th>Time</th>
                                    <th>Control</th>
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
                                        <div class="d-flex align-items-center">
                                            <div class="w-30px h-30px">
                                                <img src="/images/avatar/<?php echo $row["head"]; ?>.png" alt=""
                                                    class="ms-100 mh-100">
                                            </div>
                                            <div class="ms-3 flex-grow-1">
                                                <div class="fw-600 text-body">Tên:
                                                    <?php echo $row["name"]; ?></div>
                                                <div class="fs-13px">TK:
                                                    <?php echo $row["username"]; ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="usr-email-addr">
                                            <?php echo number_format($row["vnd"]); ?>đ
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-location">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input baner-check"
                                                data-id="<?php echo $row['id']; ?>"
                                                <?php echo ($row['ban'] == 1) ? 'checked' : ''; ?>>
                                        </div>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input hide-checkbox"
                                                data-id="<?php echo $row['id']; ?>"
                                                <?php echo ($row['active'] == 1) ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="usr-ph-no">
                                            <?php echo ($row["ip_address"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="usr-ph-no">
                                            <?php echo ($row["create_time"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btn">
                                            <a href="javascript:void(0)"
                                                onclick="del_(<?php echo $row['account_id']; ?>);"
                                                class="btn btn-sm btn-danger delete ms-2">
                                                <i class="fa fa-trash-alt fs-5"></i>
                                            </a>
                                            <button class="btn btn-sm btn-warning ms-2" onclick="logoutUser(<?php echo $row['account_id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>')">Đăng xuất tất cả phiên</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php }
                        } else { ?>
                                <tr class="text-center py-3">
                                    <td colspan='6'>
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
                             echo '<center>' . $CVH->phantrang('users-manager?', $start, $tong, $kmess) . '</center>';
                         }
                    ?>
                    </div>
                    <script>
                    function logoutAllSystem(){
                        Swal.fire({
                            title: 'Xác nhận',
                            text: 'Đăng xuất toàn bộ phiên của TẤT CẢ tài khoản?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Đồng ý',
                            cancelButtonText: 'Hủy'
                        }).then((r)=>{
                            if(r.isConfirmed){
                                $.post('/ajax/admin/user/logout_all.php', { all: '1', csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' })
                                 .done(function(res){ try{ var d = JSON.parse(res);}catch(e){var d={status:true};}
                                     Swal.fire('Thành công', 'Đã đăng xuất tất cả phiên!', 'success');
                                 }).fail(function(){ Swal.fire('Lỗi', 'Không thể xử lý', 'error'); });
                            }
                        });
                    }

                    function logoutUser(id, username){
                        Swal.fire({
                            title: 'Xác nhận',
                            text: 'Đăng xuất toàn bộ phiên của '+username+' (#'+id+')?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Đồng ý',
                            cancelButtonText: 'Hủy'
                        }).then((r)=>{
                            if(r.isConfirmed){
                                $.post('/ajax/admin/user/logout_all.php', { user_id: id, csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' })
                                 .done(function(res){ try{ var d = JSON.parse(res);}catch(e){var d={status:true};}
                                     Swal.fire('Thành công', 'Đã đăng xuất '+username, 'success');
                                 }).fail(function(){ Swal.fire('Lỗi', 'Không thể xử lý', 'error'); });
                            }
                        });
                    }
                    $(document).ready(function() {
                        $('.hide-checkbox').change(function() {
                            var status = $(this).is(':checked') ? 1 : 0;
                            var id = $(this).data('id');

                            $.ajax({
                                url: '/ajax/admin/user/active.php',
                                type: 'POST',
                                data: {
                                    status: status,
                                    id: id,
                                    csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>'
                                },
                                success: function(response) {
                                    console.log(response);
                                },
                                error: function(xhr, status, error) {}
                            });
                        });
                    });


                    $(document).ready(function() {
                        $('.baner-check').change(function() {
                            var status = $(this).is(':checked') ? 1 : 0;
                            var id = $(this).data('id');

                            $.ajax({
                                url: '/ajax/admin/user/baner.php',
                                type: 'POST',
                                data: {
                                    status: status,
                                    id: id,
                                    csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>'
                                },
                                success: function(response) {
                                    console.log(response);
                                },
                                error: function(xhr, status, error) {}
                            });
                        });
                    });


                    function del_(id) {
                        Swal.fire({
                            title: 'Thông Báo',
                            text: 'Bạn có muốn xóa tài khoản và nhân vật của #' + id + ' không!',
                            icon: 'warning',
                            showDenyButton: true,
                            confirmButtonText: 'Đồng Ý',
                            denyButtonText: `Đóng`,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: 'POST',
                                    url: '/ajax/admin/user/delete.php',
                                    data: {
                                        type: 'Del_Mem',
                                        id: id,
                                        csrf_token: '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>'
                                    },
                                    success: function(response) {
                                        var data = JSON.parse(response);
                                        if (data.status == true) {
                                            Swal.fire('Thông báo', data.message, 'success').then(
                                                () => {
                                                    window.location.reload();
                                                });
                                        } else {
                                            Swal.fire('Thông báo',
                                                'Có lỗi xảy ra vui lòng thử lại!',
                                                'error').then(() => {});
                                        }
                                    },

                                    error: function() {
                                        Swal.fire('Thông Báo',
                                            'Có lỗi xảy ra vui lòng thử lại sau!',
                                            'error');
                                    }
                                });
                            }
                        });
                    }
                    </script>
                </div>
            </div>
