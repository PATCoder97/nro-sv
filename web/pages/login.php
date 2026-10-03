<?php
if ($user) {
    header("Location: /");
    exit();
}
?>

<div class="login">

    <div class="login-content py-5">
        <form cvhvn="true" method="POST" action="/ajax/auth/login.php" href="/">
            <h1 class="text-center">Đăng Nhập</h1>
            <div class="text-muted text-center mb-4">
                đăng nhập vào tài khoản
            </div>
            <div class="mb-3">
                <label class="form-label">Tài khoản</label>
                <input type="text" class="form-control form-control-lg fs-15px" name="username"
                    placeholder="Nhập tài khoản">
            </div>
            <div class="mb-3">
                <div class="d-flex">
                    <label class="form-label">Mật khẩu</label>
                    <a href="/quen-mat-khau" class="ms-auto text-muted">Quên mật khẩu?</a>
                </div>
                <input type="password" class="form-control form-control-lg fs-15px" name="password"
                    placeholder="Nhập mật khẩu">
            </div>
            <?php if (!empty($config['recaptcha_site_key'])): ?>
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>"></div>
            </div>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <?php endif; ?>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="customCheck1">
                    <label class="form-check-label fw-500" for="customCheck1">Ghi nhớ?</label>
                </div>
            </div>
            <button type="submit" href="/" class="btn btn-theme btn-md d-block w-100 fw-500 mb-3">Đăng Nhập
                Ngay</button>
            <div class="text-center text-muted">
                Bạn chưa có tài khoản? <a href="/dang-ky">Đăng ký</a>.
            </div>
        </form>
    </div>
</div>