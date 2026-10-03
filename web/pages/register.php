<?php
if ($user) {
    header("Location: /");
    exit();
}
?>

<div class="login">

    <div class="login-content py-5">
        <form cvhvn="true" action="/ajax/auth/register.php"  method="POST" href="/">
            <h1 class="text-center">Đăng Ký</h1>
            <div class="text-muted text-center mb-4">
                đăng ký tài khoản mới
            </div>
            <div class="mb-3">
                <label class="form-label">Tài khoản</label>
                <input type="text" name="username" class="form-control form-control-lg fs-15px" value=""
                    minlength="4" maxlength="9" autocomplete="username" required placeholder="Nhập tài khoản (4-9 ký tự)">
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control form-control-lg fs-15px" value=""
                    minlength="4" maxlength="9" autocomplete="new-password" required placeholder="Nhập mật khẩu (4-9 ký tự)">
            </div>
            <div class="mb-3">
                <label class="form-label">Nhập lại mật khẩu</label>
                <input type="password" name="repassword" class="form-control form-control-lg fs-15px" value=""
                    minlength="4" maxlength="9" autocomplete="new-password" required placeholder="Nhập lại mật khẩu">
            </div>
            <?php if (!empty($config['recaptcha_site_key'])): ?>
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>"></div>
            </div>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <?php endif; ?>
            <button type="submit" href="/" class="btn btn-theme btn-md d-block w-100 fw-500 mb-3">Đăng Ký Ngay</button>
            <div class="text-center text-muted">
                Bạn đã có tài khoản? <a href="/dang-nhap">Đăng nhập</a>.
            </div>
        </form>
    </div>

</div>
