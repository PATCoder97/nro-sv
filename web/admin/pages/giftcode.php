<?php
$kmess = 16; // Số phim hiện trong mỗi page
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
$result = mysqli_query($CVH->connect_db(), "SELECT * FROM `cvh_giftcode` ORDER BY `time` DESC LIMIT $start, $kmess");
$tong = mysqli_num_rows(mysqli_query($CVH->connect_db(), "SELECT * FROM `cvh_giftcode`"));
// Tạo CSRF token cho form admin
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div id="content" class="app-content">
    <div class="row">
        <div class="col-xl-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <style>
                    /* Giftcode layout & preview */
                    #gift-left .card-title { font-weight: 600; }
                    #gift-preview .card-title { font-weight: 600; }
                    .preview-card {
                        border: 1px solid #e5e7eb;
                        border-radius: 10px;
                        background: #fafafa;
                    }
                    .thumb-box {
                        width: 96px;
                        height: 96px;
                        border-radius: 10px;
                        background: linear-gradient(135deg,#f3f4f6 0%, #ffffff 100%);
                        border: 1px solid #e5e7eb;
                        display: flex; align-items: center; justify-content: center;
                        margin-right: 14px;
                        overflow: hidden;
                    }
                    .thumb-box img { width: 100%; height: 100%; object-fit: contain; }
                    .meta-line { color:#6b7280; font-size: 12px; }
                    .divider-soft { height:1px; background:#eee; margin:10px 0; }
                    </style>
                    <form class="row" cvhvn="true" method="POST" action="/ajax/admin/giftcode/addgift.php"
                        href="<?php echo getCurrentURL(); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
                        <div class="row g-3">
                            <div class="col-12 col-lg-8" id="gift-left">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title mb-3">Thiết lập Giftcode</div>
                        <div class="col-sm-12 col-md-6">
                            <div class="input-group mb-3">
                                <div class="w-100">
                                    <label class="form-label mb-1">Mã giftcode</label>
                                    <input type="text" class="form-control" name="code" placeholder="Nhập mã giftcode (ví dụ: SUMMER2025)">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="input-group mb-3">
                                <div class="w-100">
                                    <label class="form-label mb-1">Số lượt nhập (tối đa)</label>
                                    <input type="number" class="form-control" name="count" placeholder="Ví dụ: 1000" min="0" step="1" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="input-group mb-3">
                                <div class="w-100">
                                    <label class="form-label mb-1">Ngày hết hạn (HSD)</label>
                                    <input type="date" class="form-control" name="hsd">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="input-group mb-2">
                                <div class="w-100">
                                    <label class="form-label mb-1">Chọn vật phẩm (ID - Tên)</label>
                                    <div class="input-group">
                                        <select class="select2 form-control custom-select col-12" name="item" id="gift_item_select" data-live-search="true">
                                    <option value="">Chọn ID Vật Phẩm</option>
                                    <?php
                                        $query = $CVH->query("SELECT * FROM `item_template` ORDER BY `id` ASC");
                                        $i = 1;
                                        if (mysqli_num_rows($query) > 0) {
                                            while ($row = mysqli_fetch_assoc($query)) {
                                                ?>
                                    <option value="<?php echo $row['id']; ?>">
                                        <?php echo $row['id']; ?> -
                                        <?php echo $row['NAME']; ?>
                                    </option>
                                    <?php }
                                        } else { ?>
                                    <option value="">Không còn dữ liệu nào</option>
                                    <?php } ?>
                                        </select>
                                        <button type="button" class="btn btn-primary" id="btn-search-item" title="Tìm vật phẩm" onclick="openItemSearchGift()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="item-selling-inline" class="alert alert-warning d-none" role="alert" style="margin-top:4px;">
                                Vật phẩm đang bán trong shop. Không thể thêm vào giftcode.
                            </div>
                        </div>
                                        
                        <div class="col-sm-12 col-md-6">
                            <div class="input-group mb-3">
                                <div class="w-100">
                                    <label class="form-label mb-1">Số lượng vật phẩm nhận</label>
                                    <input type="number" class="form-control" name="soluong" placeholder="Ví dụ: 1, 5, 10..." min="1" step="1" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                            </div>
                        </div>
                        <div class="list_row">
                            <div class="row form_gift">
                                <div class="col-sm-12 col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="w-100">
                                            <label class="form-label mb-1">Chọn ID Option</label>
                                            <div class="input-group">
                                                <select class="select2 form-control custom-select col-12" name="option_id[]" id="gift_option_select">
                                            <option value="">Chọn ID Option</option>
                                            <?php
                                                $query = $CVH->query("SELECT * FROM `item_option_template`");
                                                $i = 1;
                                                if (mysqli_num_rows($query) > 0) {
                                                    while ($row = mysqli_fetch_assoc($query)) {
                                                        ?>
                                            <option value="<?php echo $row['id']; ?>">
                                                <?php echo $row['id']; ?> -
                                                <?php echo $row['NAME']; ?>
                                            </option>
                                            <?php }
                                                } else { ?>
                                            <option value="">Không còn dữ liệu nào</option>
                                            <?php } ?>
                                                </select>
                                                <button type="button" class="btn btn-primary" title="Tìm option" onclick="openOptionSearchGift(this)"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="w-100">
                                            <label class="form-label mb-1">Chỉ số (param) của Option</label>
                                            <input type="number" class="form-control" name="param_option[]"
                                                placeholder="Nhập chỉ số cần thêm (ví dụ: 10, 20...)" min="0" step="1" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                        </div>
                                        <button class="btn btn-info" onclick="addRow(this)" type="button">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                                        <div id="result_here"></div>
                                        <div class="col-sm-12 col-md-6">
                                            <button type="submit" href="<?php echo getCurrentURL(); ?>" class="btn btn-danger" id="btn-save-giftcode">Lưu
                                                Lại</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4" id="gift-preview">
                                <div class="card preview-card h-100">
                                    <div class="card-body">
                                        <div class="card-title mb-3">Xem trước vật phẩm</div>
                                        <div class="d-flex align-items-center">
                                            <div class="thumb-box">
                                                <img id="preview_icon" src="/assets/plugins/@fortawesome/fontawesome-free/svgs/solid/gift.svg" alt="icon" onerror="this.onerror=null;this.src='/assets/plugins/@fortawesome/fontawesome-free/svgs/solid/gift.svg';">
                                            </div>
                                            <div>
                                                <div id="preview_name" class="fw-bold">Chưa chọn vật phẩm</div>
                                                <div id="preview_gender" class="meta-line">Hành tinh áp dụng: <span id="preview_gender_val">-</span></div>
                                                <div class="meta-line">Số lượt nhập (tối đa): <span id="preview_count">-</span></div>
                                                <div class="meta-line">Ngày hết hạn (HSD): <span id="preview_hsd">-</span></div>
                                                <div class="meta-line">Số lượng vật phẩm được nhận: <span id="preview_qty">-</span></div>
                                            </div>
                                        </div>
                                        <div class="divider-soft"></div>
                                        <div class="mb-2">
                                            <div class="fw-semibold" style="font-size:12px;">Danh sách tùy chọn (Options)</div>
                                            <div id="preview_options" class="small text-muted">-</div>
                                        </div>
                                        <div id="preview-item-selling-alert" class="alert alert-warning d-none" role="alert">
                                            Vật phẩm đang bán trong shop. Không thể thêm vào giftcode.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div> </div>
            </div>
            <div class="accordion-item mb-3 shadow-none border rounded-top mt-3">
                <h2 class="accordion-header" id="flush-headingOne">
                    <button class="accordion-button fs-4 fw-semibold px-3 py-6 lh-base border-0 rounded-top collapsed"
                        type="button" data-bs-toggle="collapse" data-bs-target="#dscode" aria-expanded="false"
                        aria-controls="dscode"> Danh Sách Giftcode </button>
                </h2>
                <div id="dscode" class="accordion-collapse collapse show"
                    aria-labelledby="flush-headingOne" data-bs-parent="#dscode" style="">
                    <div class="accordion-body px-3 fw-normal">
                        <div class="table-responsive">
                            <table class="table search-table align-middle text-nowrap">
                                <thead class="header-item">
                                    <tr>
                                        <th>ID</th>
                                        <th>MÃ</th>
                                        <th>LƯỢT</th>
                                        <th>STATUS</th>
                                        <th>HSD</th>
                                        <th>TIME</th>
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
                                            <span>
                                                <?php echo $i++; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span>
                                                <?php echo $row["code"]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span id="text">
                                                <?php echo ($row["luot"]); ?>
                                            </span>
                                            <div class="col-4" id="input" style="display:none;">
                                                <input type="number" class="form-control form-control-sm" name="addLSD"
                                                    id="addLSD" data-id="<?php echo $row['id']; ?>">
                                            </div>
                                            <script>
                                            var textElement = document.getElementById("text");
                                            var inputContainer = document.getElementById("input");
                                            textElement.addEventListener("click", function() {
                                                textElement.hidden = true;
                                                inputContainer.style.display = "block";
                                            });

                                            $(document).ready(function() {
                                                $("#addLSD").on('change', function() {
                                                    var number = $(this).val();
                                                    var id = $(this).data("id");
                                                    if (number != '') {
                                                        $.ajax({
                                                            url: '/ajax/admin/giftcode/edit.php',
                                                            method: 'POST',
                                                            data: {
                                                                type: "Add",
                                                                id: id,
                                                                 number: number,
                                                                 csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                                                            },
                                                            success: function(data) {
                                                                if (data.status) {
                                                                    notice(data.message, 'success');
                                                                    window.location.reload();
                                                                } else {
                                                                    notice(data.message, 'error');
                                                                }
                                                            },
                                                            error: function() {
                                                                notice('Có lỗi xảy ra, vui lòng thử lại!', 'error');
                                                            }
                                                        });
                                                    }
                                                });
                                            });
                                            </script>
                                        </td>
                                        <td>
                                            <span>
                                                <?php echo ($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span>
                                                <?php echo ($row['hsd']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span>
                                                <?php echo $CVH->time_ago($row['time']); ?>
                                            </span>
                                        </td>
                                        <td>
                                        <div class="action-btn">
                                                    <a href="javascript:void(0)"
                                                        onclick="del_(<?php echo $row['id']; ?>);"
                                                        class="btn btn-sm btn-danger ms-2">
                                                        <i class="fa fa-trash fs-5"></i>
                                                    </a>
                                                </div>
                                        </td>
                                    </tr>
                                    <?php }
                                        } else { ?>
                                    <tr class="text-center">
                                        <td colspan='7'>
                                            <img src="https://cdn-icons-png.flaticon.com/128/7466/7466139.png"
                                                width="50" class="img-fluid">
                                            <p class="pt-3"><b>Không có dữ liệu</b></p>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                            if ($tong > $kmess) {
                                echo '<center>' . $CVH->phantrang('giftcode?', $start, $tong, $kmess) . '</center>';
                            }
                            ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script>
$(function(){
    const $select = $('#gift_item_select');
    const $alert = $('#preview-item-selling-alert');
    const $save = $('#btn-save-giftcode');
    const $pIcon = $('#preview_icon');
    const $pName = $('#preview_name');
    const $pGender = $('#preview_gender');
    const $pCount = $('#preview_count');
    const $pHsd = $('#preview_hsd');
    const $pQty = $('#preview_qty');
    const $pOpts = $('#preview_options');

    function renderGender(g){
        const map = {0:'Trái Đất',1:'Namek',2:'Xayda',3:'Tất cả'};
        if(g === null || g === undefined || g === '') return '-';
        const gi = parseInt(g,10);
        return (gi in map)? map[gi] : '-';
    }

    function checkItemSelling(itemId){
        if(!itemId){
            $alert.addClass('d-none').removeClass('alert-danger alert-warning').text('');
            $save.prop('disabled', false);
            $pIcon.attr('src','/images/logo/online.png');
            $pName.text('Chưa chọn vật phẩm');
            $pGender.text('Giới tính: -');
            $pCount.text('-');
            $pHsd.text('-');
            $pQty.text('-');
            $pOpts.text('-');
            return;
        }
        // Lấy đầy đủ thông tin + trạng thái bán
        $.getJSON('/ajax/admin/giftcode/item_info.php', { item: itemId })
            .done(function(res){
                if(res && res.status){
                    const d = res.data || {};
                    if(d.icon){ $pIcon.attr('src', d.icon); }
                    $pName.text(d.name || ('Item #' + itemId));
                    $('#preview_gender_val').text(renderGender(d.gender));
                }
                if(res && res.status && res.selling){
                    $alert.removeClass('d-none').addClass('alert-danger').text(res.message || 'Vật phẩm đang bán trong shop.');
                    $save.prop('disabled', true);
                } else {
                    $alert.addClass('d-none').removeClass('alert-danger alert-warning').text('');
                    $save.prop('disabled', false);
                }
            })
            .fail(function(){
                $alert.removeClass('d-none').addClass('alert-warning').text('Không kiểm tra được trạng thái vật phẩm.');
                $save.prop('disabled', true);
            });
    }

    $select.on('change', function(){
        const itemId = $(this).val();
        checkItemSelling(itemId);
    });

    // Cập nhật preview các trường: lượt nhập, HSD, số lượng, options
    function refreshMeta() {
        const count = $('input[name="count"]').val() || '-';
        const hsd = $('input[name="hsd"]').val() || '-';
        const qty = $('input[name="soluong"]').val() || '-';
        $pCount.text(count);
        $pHsd.text(hsd);
        $pQty.text(qty);
        // build options list
        let opts = [];
        $('.form_gift').each(function(){
            const id = $(this).find('select[name="option_id[]"]').val();
            const name = $(this).find('select[name="option_id[]"] option:selected').text();
            const param = $(this).find('input[name="param_option[]"]').val();
            if(id){
                opts.push(name + (param ? (': ' + param) : ''));
            }
        });
        $pOpts.html(opts.length ? ('• ' + opts.join('<br>• ')) : '-');
    }
    $(document).on('input change', 'input[name="count"], input[name="hsd"], input[name="soluong"], select[name="option_id[]"], input[name="param_option[]"]', refreshMeta);
    refreshMeta();

    // Kiểm tra ngay nếu đã có sẵn giá trị
    if($select.val()){
        checkItemSelling($select.val());
    }
});
function addRow(data) {
    if ($('.list_row .form_gift').length > 9) {
        notice("Chỉ được thêm tối đa 10 dòng", "info");
        return
    }
    $.ajax({
        url: '/ajax/admin/giftcode/addrow.php',
        beforeSend: function() {
            $(data).html(
                '<div class="spinner-border spinner-border-sm text-light" role="status"> <span class="visually-hidden"></span></div>'
                );
            $(data).attr('onclick', 'return false;')
        },
        success: function(res) {
            $(data).html('<i class="fa fa-plus"></i>');
            $(data).removeAttr('onclick').attr('onclick', 'addRow(this)')
            $('.list_row').append(res);
        }
    });
}

function removeChild__(code) {
    $(".form_gift[data-row=" + code + "]").remove()
}

function del_(id) {
    Swal.fire({
        title: 'Thông Báo',
        text: 'Bạn có muốn xóa giftcode có ID #' + id + ' không!',
        icon: 'warning',
        showDenyButton: true,
        confirmButtonText: 'Đồng Ý',
        denyButtonText: `Đóng`,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: '/ajax/admin/giftcode/delete.php',
                dataType: 'json',
                data: {
                    type: 'Del_Gift',
                         id: id,
                         csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                },
                success: function(data) {
                    if (data.status == true) {
                        Swal.fire('Thông báo', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Thông báo', data.message || 'Có lỗi xảy ra vui lòng thử lại!', 'error').then(
                        () => {});
                    }
                },

                error: function() {
                    Swal.fire('Thông Báo', 'Có lỗi xảy ra vui lòng thử lại sau!', 'error');
                }
            });
        }
    });
}
</script>
<!-- Modal tìm kiếm vật phẩm -->
<div class="modal fade" id="giftItemSearchModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Tìm kiếm vật phẩm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><input type="text" class="form-control" id="giftItemSearchInput" placeholder="Nhập tên hoặc ID..."></div>
        <div class="table-responsive" style="max-height:400px;overflow:auto">
          <table class="table table-hover"><thead><tr><th>ID</th><th>Tên</th><th>Chọn</th></tr></thead><tbody id="giftItemSearchResults"></tbody></table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal tìm kiếm option -->
<div class="modal fade" id="giftOptionSearchModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Tìm kiếm Option</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><input type="text" class="form-control" id="giftOptionSearchInput" placeholder="Nhập tên hoặc ID option..."></div>
        <div class="table-responsive" style="max-height:400px;overflow:auto">
          <table class="table table-hover"><thead><tr><th>ID</th><th>Tên</th><th>Chọn</th></tr></thead><tbody id="giftOptionSearchResults"></tbody></table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// AJAX search giống add-shop, dùng endpoint sẵn có
function openItemSearchGift(){
  $('#giftItemSearchModal').modal('show');
  // Chỉ load dữ liệu khi modal mở
  if ($('#giftItemSearchResults').is(':empty')) {
    loadGiftItems('');
  }
}
function openOptionSearchGift(btn){
  $('#giftOptionSearchModal').modal('show');
  $('#giftOptionSearchModal').data('targetSelect', $(btn).closest('.input-group').find('select'));
  // Chỉ load dữ liệu khi modal mở
  if ($('#giftOptionSearchResults').is(':empty')) {
    loadGiftOptions('');
  }
}

// Cache để lưu dữ liệu đã tải
var giftItemsCache = null;
var giftOptionsCache = null;

function loadGiftItems(search){
  // Kiểm tra cache trước
  if (giftItemsCache && !search) {
    $('#giftItemSearchResults').html(giftItemsCache);
    return;
  }
  
  $('#giftItemSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
  $.ajax({
    url: '/ajax/admin/shop/search_items.php',
    type: 'POST',
    data: { type: 'items', search: search||'' },
    success: function(response) {
      if (!search) {
        giftItemsCache = response; // Lưu vào cache
      }
      $('#giftItemSearchResults').html(response);
    },
    error: function() {
      $('#giftItemSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>');
    }
  });
}

function loadGiftOptions(search){
  // Kiểm tra cache trước
  if (giftOptionsCache && !search) {
    $('#giftOptionSearchResults').html(giftOptionsCache);
    return;
  }
  
  $('#giftOptionSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
  $.ajax({
    url: '/ajax/admin/shop/search_items.php',
    type: 'POST',
    data: { type: 'options', search: search||'' },
    success: function(response) {
      if (!search) {
        giftOptionsCache = response; // Lưu vào cache
      }
      $('#giftOptionSearchResults').html(response);
    },
    error: function() {
      $('#giftOptionSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>');
    }
  });
}

// Debounce function để tránh gọi AJAX quá nhiều
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

// Tìm kiếm vật phẩm với debounce
$(document).ready(function() {
  var searchGiftItems = debounce(function(searchTerm) {
    if (searchTerm.length > 0) {
      $('#giftItemSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</td></tr>');
      $.ajax({
        url: '/ajax/admin/shop/search_items.php',
        type: 'POST',
        data: { 
          type: 'items',
          search: searchTerm 
        },
        success: function(response) {
          $('#giftItemSearchResults').html(response);
        },
        error: function() {
          $('#giftItemSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tìm kiếm</td></tr>');
        }
      });
    } else {
      loadGiftItems('');
    }
  }, 300); // Chờ 300ms sau khi ngừng gõ

  $('#giftItemSearchInput').on('input', function() {
    var searchTerm = $(this).val();
    searchGiftItems(searchTerm);
  });

  // Tìm kiếm option với debounce
  var searchGiftOptions = debounce(function(searchTerm) {
    if (searchTerm.length > 0) {
      $('#giftOptionSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</td></tr>');
      $.ajax({
        url: '/ajax/admin/shop/search_items.php',
        type: 'POST',
        data: { 
          type: 'options',
          search: searchTerm 
        },
        success: function(response) {
          $('#giftOptionSearchResults').html(response);
        },
        error: function() {
          $('#giftOptionSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tìm kiếm</td></tr>');
        }
      });
    } else {
      loadGiftOptions('');
    }
  }, 300); // Chờ 300ms sau khi ngừng gõ

  $('#giftOptionSearchInput').on('input', function() {
    var searchTerm = $(this).val();
    searchGiftOptions(searchTerm);
  });
});

// Chọn vật phẩm (giống add-shop)
function selectItem(id, name) {
  $('#gift_item_select').val(id).trigger('change');
  $('#giftItemSearchModal').modal('hide');
}

// Chọn option (giống add-shop)
function selectOption(id, name) {
  var $target = $('#giftOptionSearchModal').data('targetSelect') || $('#gift_option_select');
  $target.val(id).trigger('change');
  $('#giftOptionSearchModal').modal('hide');
}
</script>