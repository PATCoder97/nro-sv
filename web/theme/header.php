<div id="header"
    class="app-header <?php echo ($setting['navbar'] == 'fixed') ? 'cvh-fixed' : 'cvh-absolute'; ?> navbar navbar-expand-lg p-0 cvhvn-rounded-header cvh-margin">
    <div class="container-xxl px-3 px-lg-5">
        <button class="navbar-toggler border-0 p-0 me-3 fs-24px shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarContent">
            <span class="h-2px w-25px bg-gray-500 d-block mb-1"></span>
            <span class="h-2px w-25px bg-gray-500 d-block"></span>
        </button>
        <a class="navbar-brand d-flex align-items-center position-relative me-auto" href="/">
            <img src="<?php echo $setting['logo']; ?>" class="invert-dark" alt
                height="<?php echo $setting['size_logo']; ?>px">
        </a>
        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="navbar-nav ms-auto mb-2 mb-lg-0 fw-500">
                <div class="nav-item me-2">
                    <a href="/" class="nav-link">Trang Chủ</a>
                </div>
                <div class="nav-item me-2">
                    <a href="/gioi-thieu" class="nav-link">Giới Thiệu</a>
                </div>
                <div class="nav-item me-2">
                    <a href="/dien-dan" class="nav-link">Diễn Đàn</a>
                </div>
            </div>
        </div>
        <div class="ms-3">
            <?php if ($user) { ?>
            <div class="menu-item dropdown">
                <a href="#" data-bs-toggle="dropdown" data-display="static" class="menu-link hii" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center me-1 w-40px h-40px p-5px rounded">
                            <img src="/images/avatar/<?php echo $player['head']; ?>.png" alt="" class="ms-100 mh-100">
                        </div>
                        <div class="menu-link ms-0 hii">
                            <h6 class="mb-0"><?php echo $player['name']; ?></h6>
                            <small class="fs-11px text-theme mt-0"><?php echo number_format($user['vnd']); ?>đ</small>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end me-lg-3" style="">
                    <?php if ($user['active'] == 0) { ?>
                    <a role="button" data-bs-toggle="modal" data-bs-target="#modalActive"
                        class="dropdown-item d-flex align-items-center">Tài khoản:<b class="text-danger ms-1"> Chưa kích
                            hoạt</b></a>
                    <?php } else { ?>
                    <a role="button" class="dropdown-item d-flex align-items-center" onclick="Active();">Tài
                        khoản:<b class="text-success ms-1"> Đã kích hoạt</b></a>
                    <?php } ?>
                    <?php if ($CVH->getEmail($user['email'], 'verify') === 'false') { ?>
                    <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal"
                        data-bs-target="#modalEmail">Email: <b class="text-danger ms-1"> Chưa liên kết</b></a>
                    <?php } else { ?>
                    <a class="dropdown-item d-flex align-items-center" onclick="Thayemail();">Email: <b
                            class="text-success ms-1"> <?php echo $CVH->getEmail($user['email'], 'email'); ?></b></a>
                    <?php } ?>
                    <a class="dropdown-item d-flex align-items-center" href="/nap-tien">Nạp tiền vào tài khoản</a>
                    <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal"
                        data-bs-target="#modalMK">Đổi mật khẩu</a>
                    <?php if($user['is_admin']){ ?>
                    <a class="dropdown-item d-flex align-items-center" href="/admin" target="_blank"><b
                            class="text-danger">ADMIN</b></a>
                    <?php
                }
                ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item d-flex align-items-center" onclick="Logout();">Đăng xuất <i
                            class="fa fa-toggle-off fa-fw ms-auto text-body text-opacity-50"></i></a>
                </div>
            </div>
            <?php } else { ?>
            <a href="/dang-nhap" class="btn btn-theme btn-sm fw-bold rounded-pill">Đăng Nhập<i
                    class="fa fa-arrow-right ms-1 opacity-5"></i></a>
            <?php } ?>
        </div>
    </div>
</div>
<?php if($user){
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<?php if($user['active'] == 0){ ?>
<div class="modal fade" id="modalActive" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kích hoạt tài khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form cvhvn="true" method="POST" action="/ajax/users/active.php" href="<?php echo getCurrentURL(); ?>"
                    class="was-validated">
                    <h4 class="text-center">Tài khoản của bạn: <b class="text-danger"> Chưa kích hoạt</b></h4>
                    <h5 class="text-center">Số tiền để kích hoạt: <b class="text-danger">
                            <?php echo number_format($setting['amount_mtv']); ?>đ</b></h5>
                    <p class="text-center">Bạn có thể mở khóa lại các chức năng đã bị khóa trên website lẫn trong game
                        như: bình luận, chức năng PK, trao đổi vật phẩm...</b></p>
                    <input name="username" type="hidden" value="<?php echo $user['username']; ?>" />
                    <input name="csrf_token" type="hidden" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
                    <div class="d-flex align-items-center justify-content-center py-2">
                        <button type="submit" href="<?php echo getCurrentURL(); ?>" class="btn btn-sm btn-theme">Kích
                            hoạt ngay</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>
<?php if($CVH->getEmail($user['email'], 'verify') !== 'true'){ ?>
<div class="modal fade" id="modalEmail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Liên kết email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form cvhvn="true" method="POST" action="/ajax/users/email.php" href="<?php echo getCurrentURL(); ?>"
                    class="was-validated">
                    <input name="csrf_token" type="hidden" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
                    <p class="text-center">Liên kết email để tránh trường hợp quên mật khẩu bạn có thể lấy lại.</b></p>
                    <div class="mb-3">
                        <input class="form-control" name="email" type="email" placeholder="Nhập email cần liên kết"
                            required />
                    </div>
                    <div class="mb-3">
                        <div class="d-flex">
                            <div class="input-group">
                                <input type="text" class="form-control" name="code" placeholder="Nhập mã kích hoạt"
                                    required>
                                <button type="button" id="sendCodeBtn" class="input-group-text">Gửi Mã</button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center py-2">
                        <button type="submit" href="<?php echo getCurrentURL(); ?>" class="btn btn-sm btn-theme">Liên
                            kết ngay</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>
<div class="modal fade" id="modalMK" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đổi Mật Khẩu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form cvhvn="true" method="POST" action="/ajax/users/changepass.php"
                    href="<?php echo getCurrentURL(); ?>">
                    <input name="csrf_token" type="hidden" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
                    <div class="mb-3">
                        <input class="form-control" name="mkcu" type="password" placeholder="Nhập mật khẩu cũ"
                            required />
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="mkmoi" type="password" placeholder="Nhập mật khẩu mới"
                            required />
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="remkmoi" type="password" placeholder="Nhập lại mật khẩu mới"
                            required />
                    </div>
                    <div class="d-flex align-items-center justify-content-center py-2">
                        <button type="submit" href="<?php echo getCurrentURL(); ?>" class="btn btn-md btn-theme">Đổi Mật
                            Khẩu</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
function Active() {
    toastr.options.closeButton = true;
    toastr.success('Tài khoản của đã được kích hoạt rồi!', 'Thông báo', {
        timeOut: 5000
    })
}

function Thayemail() {
    toastr.options.closeButton = true;
    toastr.success('Tài khoản của đã được thêm email!', 'Thông báo', {
        timeOut: 5000
    })
}

function Logout() {
    Swal.fire({
        title: 'Thông Báo',
        text: "Bạn có muốn đăng xuất khỏi tài khoản của mình không?",
        icon: 'warning',
        width: '300px',
        heightAuto: false,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Vâng',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/ajax/auth/logout.php',
                method: 'POST',
                success: function(res){
                    try { var data = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) { var data = {status:true}; }
                    toastr.options.closeButton = true;
                    toastr.success('Tài khoản của bạn sẽ được đăng xuất!', 'Thông báo', { timeOut: 2000 });
                    setTimeout(function(){ location.reload(); }, 1800);
                },
                error: function(){
                    toastr.error('Không thể đăng xuất, vui lòng thử lại!');
                }
            });
        }
    });
}
</script>
<?php } ?>
