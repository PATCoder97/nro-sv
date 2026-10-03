<?php
$kmess = 10; // Số phim hiện trong mỗi page
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
$result = mysqli_query($CVH->connect_db(), "SELECT cvh_baiviet.id, cvh_baiviet.title, cvh_baiviet.time, 
                      account.username, player.head, player.name
                      FROM cvh_baiviet
                      LEFT JOIN account ON cvh_baiviet.poster = account.id
                      LEFT JOIN player ON cvh_baiviet.poster = player.account_id
                      WHERE cvh_baiviet.status = 1
                      AND cvh_baiviet.role = 1
                      ORDER BY cvh_baiviet.time DESC
                      LIMIT $start, $kmess");
$tong = $CVH->get_value("SELECT COUNT(*) FROM cvh_baiviet WHERE cvh_baiviet.status = 1 AND cvh_baiviet.role = 1");
?>
<div id="blog1" class="py-3 bg-component">
    <style>
    a.hii {
        text-decoration: none;
    }
    </style>
    <style>
    /* Responsive fix for banner buttons on mobile */
    @media (max-width: 576px) {
        .banner-wrapper { flex-direction: column; align-items: flex-start !important; }
        .banner-actions { width: 100%; margin-left: 0 !important; gap: 10px !important; flex-direction: column; }
        .banner-actions .btn { width: 100%; padding: 12px 16px !important; font-size: 16px !important; white-space: normal !important; }
    }
    </style>
    <div class="container-xxl p-3 p-lg-2">
        <div class="alert border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; padding: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); margin-bottom: 25px;">
            <div class="d-flex justify-content-between align-items-center banner-wrapper">
                <div class="flex-grow-1">
                    <h5 class="mb-2" style="font-weight: bold; margin: 0; font-size: 16px;">
                        <i class="fas fa-comments me-2"></i>
                        Chào mừng bạn đã tới diễn đàn, tham gia kênh chat của chúng tôi để trò chuyện cùng mọi người
                    </h5>
                    <p class="mb-0" style="font-weight: bold; margin: 0; font-size: 16px;">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Mở bán trang bị có chỉ số cao, và tặng phật phẩm trong khung giờ vàng tại trang bán hàng tại website
                    </p>
                </div>
                <div class="d-flex gap-2 ms-3 banner-actions">
                    <a href="/chat" class="btn btn-light btn-sm px-3 py-2" style="font-weight: bold; border-radius: 25px; text-decoration: none; transition: all 0.3s ease; white-space: nowrap;">
                        <i class="fas fa-users me-1"></i>
                        Tham Gia Ngay!
                    </a>
                    <a href="/shop" class="btn btn-light btn-sm px-3 py-2" style="font-weight: bold; border-radius: 25px; text-decoration: none; transition: all 0.3s ease; white-space: nowrap;">
                        <i class="fas fa-store me-1"></i>
                        Truy Cập Ngay!
                    </a>
                </div>
            </div>
        </div>
        <div class="flex-fill text-theme fw-500 mb-3">➤ Thông báo từ Quản Trị Viên</div>
        <div class="card-body">
            <?php
                 $query = $CVH->query("SELECT cvh_baiviet.id, cvh_baiviet.title, account.username, player.head, player.name, cvh_baiviet.if_admin, cvh_baiviet.role 
                 FROM `cvh_baiviet` LEFT JOIN account ON cvh_baiviet.poster = account.id 
                 LEFT JOIN player ON cvh_baiviet.poster = player.account_id 
                 WHERE cvh_baiviet.role = 2 AND `if_admin` 
                 IS NOT NULL ORDER BY `time` DESC LIMIT 5");
				$i = 1;
				if (mysqli_num_rows($query) > 0) {
					while ($row = mysqli_fetch_assoc($query)) {
					$data = json_decode($row['if_admin'], true);
			?>
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center justify-content-center me-1 w-50px h-50px p-5px rounded">
                    <img src="<?php echo $data['avatar']; ?>" alt="" class="ms-100 mh-100">
                </div>
                <a href="/topic?id=<?php echo $row['id']; ?>" class="ms-0 hii">
                    <h6 class="mt-2 mb-0"><?php echo $row['title']; ?> <img src="/assets/img/Gif/hot.gif"></h6>
                    <small class="fs-11px text-theme mt-0">bởi <b class="text-danger"><?php echo $data['name']; ?></b>
                        <i class="fa fa-check-circle"></i></small>
                </a>

            </div>
            <?php } } ?>
        </div>
    </div>
    <div class="border-theme border-top"></div>
    <div class="container-xxl p-3 p-lg-2 ">
        <div class="flex-fill text-theme fw-500 mb-3">➤ Bài viết từ Thành Viên</div>
        <div class="card-body">
            <?php
                $i = 1;
                if (mysqli_num_rows($result) > 0) {
                while ($chvvn = mysqli_fetch_assoc($result)) {
            ?>
            <div class="d-flex align-items-center mb-2 justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center me-1 w-50px h-50px p-5px rounded">
                        <img src="/images/avatar/<?php echo $chvvn['head']; ?>.png" alt="" class="ms-100 mh-100">
                    </div>
                    <a href="/topic?id=<?php echo $chvvn['id']; ?>" class="ms-0 hii">
                        <h6 class="mt-2 mb-0"><?php echo $chvvn['title']; ?></h6>
                        <small class="fs-11px text-theme mt-0">bởi <b><?php echo $chvvn['name']; ?></b></small>
                    </a>
                </div>
                <div>
                    <span class="fs-10px text-muted"><?php echo $CVH->timeAgo($chvvn['time']); ?></span>
                </div>
            </div>

            <?php }
                } else { ?>
            <div class="d-flex align-items-center justify-content-center">
                <p class="pt-3"><b>Chưa có bài viết nào được đăng</b></p>
            </div>
            <?php } ?>
            <div class="d-md-flex align-items-center py-3 m-2">
                <?php if($user){ ?>
                <div class="me-md-auto text-md-left text-center mb-2 mb-md-0">
                    <a class="btn btn-theme btn-sm" data-bs-toggle="modal" data-bs-target="#modal">Đăng bài mới</a>
                </div>
                <div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                    aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Đăng Bài Viết</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form cvhvn="true" method="POST" action="/ajax/topic/addpost.php"
                                    href="<?php echo FULL_URL('/dien-dan'); ?>" class="was-validated">
                                    <div class="form-group mb-3">
                                        <input type="text" class="form-control" placeholder="Tiêu đề bài viết"
                                            name="title" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <textarea type="text" class="form-control" rows="8" placeholder="Nhập nội dung"
                                            name="content" required></textarea>
                                    </div>
                                    <div class="d-md-flex align-items-center justify-content-end py-2">
                                        <button type="submit" href="<?php echo FULL_URL('/dien-dan'); ?>"
                                            class="btn btn-sm btn-theme">Gửi</button>
                                    </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="d-flex align-items-center justify-content-center">
                    <?php
                  if ($tong > $kmess) {
                    echo '' . $CVH->phantrang('/dien-dan?', $start, $tong, $kmess) . '';
                  }
                ?>
                </div>
            </div>
        </div>
    </div>
</div>