<div class="login">

    <div class="login-content py-5">
        <form cvhvn="true" method="POST" action="/ajax/users/forgot.php" href="/">
            <h1 class="text-center">Quên Mật Khẩu</h1>

            <div class="mb-3">
                <label class="form-label">Tài khoản</label>
                <input type="text" name="username" class="form-control form-control-lg fs-15px" value=""
                    placeholder="Nhập tài khoản">
            </div>
            <div class="mb-3">
                <label class="form-label">Nhập email</label>
                <input type="email" name="email" class="form-control form-control-lg fs-15px" value=""
                    placeholder="Nhập email">
            </div>
            <div class="mb-3 text-center">
  <?php if (!empty($config['recaptcha_site_key'])): ?>
  <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>"></div>
  <?php endif; ?>
</div>
            <button type="submit" href="/" class="btn btn-theme btn-md d-block w-100 fw-500 mb-3">Quên Mật Khẩu</button>
        </form>
    </div>

</div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
