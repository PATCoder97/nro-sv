<?php
//Load bài viết làm sơ sài nên rườm rà :))
$id = abs($_GET['id']);
if ($CVH->checkPost($id)) {
    $postData = $CVH->get_row("SELECT cvh_baiviet.*, account.username, account.point_post, player.head, player.name
                  FROM cvh_baiviet
                  LEFT JOIN account ON cvh_baiviet.poster = account.id
                  LEFT JOIN player ON cvh_baiviet.poster = player.account_id
                       WHERE cvh_baiviet.id = '" . $id . "'
                       ORDER BY cvh_baiviet.id DESC");
} else {
    echo "<script>window.location='/';</script>";
}
$table = 'cvh_baiviet';
$data = 'views';
$sodiem = 1;
$where = 'id = "' . $id . '"';
$CVH->cong($table, $data, $sodiem, $where);

//load cmt và phân trang cmt
$kmess = 8;
$page = max(1, isset($_GET['page']) ? intval($_GET['page']) : 1);
$start = ($page - 1) * $kmess;
$sql = "SELECT comments, poster FROM cvh_baiviet WHERE id = $id";
$result = $CVH->query($sql);
$row = $result->fetch_assoc(); // lấy dữ liệu
$cmtarr = json_decode($row["comments"], true);
$cmth = array_slice($cmtarr, $start, $kmess); 
$tong = count($cmtarr);
?>
<div id="blog1" class="py-3 bg-component">
    <style>
    a.hii {
        text-decoration: none;
    }
    </style>
    <div class="container-xxl p-3 p-lg-2">
        <a onclick="history.back();" href="#" class="flex-fill text-theme fw-700 fs-12px mb-3 hii">➤ Quay lại</a>
        <div class="card-body">

            <div class="d-flex align-items-start mt-3">
                <a class="hii text-center col-1" href="#">
                    <?php if ($postData['if_admin'] != NULL) {
                                        $data = json_decode($postData['if_admin'], true); ?>
                    <img src="<?php echo $data['avatar']; ?>" alt="" width="35" class="rounded">

                    <?php } else { ?>
                    <img src="/images/avatar/<?php echo $postData['head']; ?>.png" alt="" width="35" class="rounded">
                    <?php } ?>
                    <?php if ($postData['if_admin'] != NULL) { ?>
                    <div class="fs-10px mt-1"><b class="text-danger"><?php echo $data['name']; ?></b> <i
                            class="fa fa-check-circle"></i></div>
                    <?php } else { ?>
                    <div class="fs-10px mt-1"><b><?php echo $postData['name']; ?></b></div>
                    <?php } ?>
                </a>

                <div class="ms-2 card card-body bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="fs-9px mt-0">
                            <img style="vertical-align:middle;"
                                src="<?php $CVH->CheckOnline($postData['username']); ?>">
                            <?php echo $CVH->timeAgo($postData['time']); ?>
                        </small>
                        <small class="fs-9px mt-0">
                            #<?php echo $_GET['id']; ?>
                        </small>
                    </div>
                    <div class="border-danger border-top py-1"></div>
                    <h5 class="mt-0 mb-1"><?php echo $postData['title']; ?></h5>
                    <span>
                        <?php echo $postData['content']; ?>
                    </span>
                </div>
            </div>


        </div>
        <?php
           foreach ($cmth as $comment) {
               $pl = $CVH->player($comment["account_id"]);
               $comment["head"] = $pl["head"];
               $comment["name"] = $pl["name"];
           ?>
        <div class="d-flex align-items-start mt-3">
            <a class="hii text-center col-1 me-1" href="#">
                <img src="/images/avatar/<?php echo htmlspecialchars($comment['head']); ?>.png" alt="" width="35"
                    class="rounded">
                <div class="fs-10px mt-1"><b><?php echo htmlspecialchars($comment['name']); ?></b></div>
            </a>

            <div class="ms-1 card card-body bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="fs-9px mt-0">
                        <img style="vertical-align:middle;" src="<?php $CVH->CheckOnline($comment['account_id']); ?>">
                        <?php echo $CVH->timeAgo(htmlspecialchars($comment['time'])); ?>
                    </small>
                    <!-- <small class="fs-9px mt-0">
                        Điểm: 36
                    </small> -->
                </div>
                <span>
                    <p><?php echo htmlspecialchars($comment['noidung'], ENT_QUOTES, 'UTF-8'); ?></p>
                </span>
            </div>
        </div>
        <?php } ?>

        <?php if($user) { ?>
        <div class="d-flex align-items-start mt-3">
            <a class="hii text-center col-1">
                <img src="/images/avatar/<?php echo $player['head']; ?>.png" alt="" width="30" class="rounded">
                <div class="fs-10px mt-1"><?php echo $player['name']; ?></div>
            </a>

            <div class="col-10 ms-2">
                <form cvhvn="true" method="POST" action="/ajax/topic/addcmt.php"
                    href="<?php echo FULL_URL('/topic?id='.$id.'&page='.$page.''); ?>" class="was-validated">
                    <input name="id" value="<?php echo $id; ?>" hidden>
                    <textarea type="text" class="form-control" placeholder="Nhập nội dung" name="content"
                        required></textarea>
                    <div class="d-md-flex align-items-center justify-content-end py-2">
                        <button type="submit" href="<?php echo FULL_URL('/topic?id='.$id.'&page='.$page.''); ?>"
                            class="btn btn-sm btn-theme">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php } ?>
    <div class="container-xxl p-3 p-lg-2">
        <div class="card-body">
            <div class="d-md-flex align-items-center justify-content-end py-2">
                <?php
                  if ($tong > $kmess) {
                    echo '' . $CVH->phantrang('/topic?id='.$id.'&', $start, $tong, $kmess) . '';
                  }
                ?>
            </div>

        </div>
    </div>
</div>