<?php
if (empty($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden: You don't have permission to access this resource.";
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/cvhvn/autoload.php";
$code = md5(rand_string(10));
?>
<div class="row form_gift" data-row="<?= $code; ?>">
    <div class="col-sm-12 col-md-6">
        <div class="input-group mb-3">
            <select class="select2 form-control custom-select col-12" data-row="<?= $code; ?>" name="option_id[]">
                <option selected="">Chọn ID Option</option>
                <?php
                $query = $CVH->query("SELECT * FROM `item_option_template`");
                $i = 1;
                if (mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        ?>
                        <option value="<?php echo $row['id']; ?>">
                        <?php echo $row['id']; ?> - <?php echo $row['NAME']; ?>
                        </option>
                    <?php }
                } else { ?>
                    <option value="">Không còn dữ liệu nào</option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="input-group mb-3">
            <input type="text" class="form-control" data-row="<?= $code; ?>" name="param_option[]"
                placeholder="Nhập chỉ số cần thêm">
            <button class="btn btn-info" onclick="removeChild__('<?= $code; ?>')" type="button">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
</div>
<script>
    $(".select2").select2();
</script>