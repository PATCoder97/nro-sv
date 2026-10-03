<?php
$kmess = 6; // Số phim hiện trong mỗi page
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
$result = mysqli_query($CVH->connect_db(), "SELECT item_template.*, cvh_sell_item.*  FROM item_template INNER JOIN cvh_sell_item ON item_template.id = cvh_sell_item.item ORDER BY cvh_sell_item.id ASC LIMIT $start, $kmess");
$tong = mysqli_num_rows(mysqli_query($CVH->connect_db(), "SELECT item_template.*, cvh_sell_item.*  FROM item_template INNER JOIN cvh_sell_item ON item_template.id = cvh_sell_item.item"));
?>
<style>
.list-group-item .info-block,
.list-group-item .additional-info {
    display: flex;
    flex-direction: column;
}

.info-block {
    flex-grow: 1;
}

.additional-info {
    flex-grow: 6;
    align-items: flex-start;
}

/* CSS cho form compact */
.add-item-form {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.form-section {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
}

.section-title {
    color: #495057;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 8px;
}

.section-title i {
    color: #6c757d;
    font-size: 12px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 10px;
}

.form-group {
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
    color: #495057;
    font-size: 12px;
}

.form-control {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 13px;
    transition: all 0.2s ease;
    background: white;
}

 .form-control:focus {
     border-color: #80bdff;
     box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.25);
 }
 
 /* CSS cho dropdown đẹp hơn */
 select.form-control {
     background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
     background-position: right 0.5rem center;
     background-repeat: no-repeat;
     background-size: 1.5em 1.5em;
     padding-right: 2.5rem;
     -webkit-appearance: none;
     -moz-appearance: none;
     appearance: none;
 }
 
 select.form-control option {
     padding: 8px;
     font-size: 13px;
 }

.btn-primary {
    background: #007bff;
    border: 1px solid #007bff;
    border-radius: 4px;
    padding: 6px 12px;
    font-weight: 500;
    font-size: 12px;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
}

.btn-success {
    background: #28a745;
    border: 1px solid #28a745;
    border-radius: 4px;
    padding: 6px 12px;
    font-weight: 500;
    font-size: 12px;
    transition: all 0.2s ease;
}

.btn-success:hover {
    background: #1e7e34;
    border-color: #1e7e34;
}

.btn-danger {
    background: #dc3545;
    border: 1px solid #dc3545;
    border-radius: 4px;
    padding: 8px 16px;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.2s ease;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #c82333;
}

.option-row {
    background: #f8f9fa;
    border-radius: 4px;
    padding: 8px;
    margin-bottom: 8px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.option-row:hover {
    border-color: #80bdff;
    background: #f0f8ff;
}

.option-row .btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 10px;
}

.option-row .btn-remove:hover {
    background: #c82333;
    transform: scale(1.05);
}

.preview-section {
    background: #f8f9fa;
    border-radius: 4px;
    padding: 10px;
    margin-top: 10px;
    border: 1px solid #dee2e6;
}

.preview-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: white;
    border-radius: 4px;
    margin-bottom: 6px;
}

.preview-icon {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    object-fit: cover;
    border: 1px solid #dee2e6;
}

.preview-info h6 {
    margin: 0;
    color: #495057;
    font-weight: 600;
    font-size: 13px;
}

.preview-info p {
    margin: 2px 0 0 0;
    color: #6c757d;
    font-size: 11px;
}

.planet-badge {
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
}

 .planet-0 { background: #28a745; color: white; }
 .planet-1 { background: #17a2b8; color: white; }
 .planet-2 { background: #ffc107; color: #212529; }
 .planet-3 { background: #6f42c1; color: white; }
  .planet-default { background: #6c757d; color: white; }

 /* CSS cho layout 2 cột */
 .preview-sidebar {
     position: sticky;
     top: 20px;
     height: fit-content;
 }

 .preview-sidebar .preview-section {
     margin-top: 0;
 }

 .preview-sidebar .preview-item {
     flex-direction: column;
     text-align: center;
     padding: 15px;
 }

 .preview-sidebar .preview-icon {
     width: 48px;
     height: 48px;
     margin-bottom: 10px;
 }

 .preview-sidebar .preview-info h6 {
     font-size: 14px;
     margin-bottom: 8px;
 }

 .preview-sidebar .preview-info p {
     font-size: 12px;
 }

 /* Responsive cho mobile */
 @media (max-width: 768px) {
     .preview-sidebar {
         position: static;
         margin-top: 20px;
     }
     
     .preview-sidebar .preview-item {
         flex-direction: row;
         text-align: left;
         padding: 8px;
     }
     
     .preview-sidebar .preview-icon {
         width: 32px;
         height: 32px;
         margin-bottom: 0;
         margin-right: 10px;
     }
 }

 .alert-custom {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 15px;
}

.alert-custom h5 {
    color: #856404;
    margin-bottom: 6px;
    font-size: 14px;
}

.alert-custom .text-danger {
    color: #721c24 !important;
    font-size: 11px;
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .add-item-form {
        padding: 10px;
    }
    
    .form-section {
        padding: 8px;
    }
}
</style>


<div id="content" class="app-content">
    <!-- Hướng dẫn -->
    <div class="alert-custom">
        <h5><i class="fas fa-info-circle"></i> Hướng Dẫn</h5>
                 <div class="text-danger">
             <strong>Quy trình:</strong> Chọn item → Thiết lập giá/số lượng → Options → Lưu | 
             <strong>Mặc định:</strong> Option Không Thể GD | 
             <strong>Hành tinh:</strong> Tự động từ item | 
             <strong>Xem trước:</strong> Bên phải | 
             <strong>Tìm kiếm:</strong> Gõ tên/ID
         </div>
    </div>

         <!-- Form thêm item mới -->
     <div class="add-item-form">
                   <form id="addItemForm" cvhvn="true" method="POST" action="/ajax/admin/shop/add.php" href="<?php echo getCurrentURL(); ?>">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
             <div class="row">
                 <!-- Cột trái: Form chọn và setup (2/3) -->
                 <div class="col-md-8">
                     <!-- Section 1: Chọn Item -->
                     <div class="form-section">
                         <div class="section-title">
                             <i class="fas fa-box"></i>
                             1. Chọn Item
                         </div>
                         <div class="form-row">
                             <div class="form-group">
                                 <label for="item_select">Tìm Item</label>
                                 <div class="input-group">
                                     <input type="hidden" name="gender" id="gender" value="">
                                     <select class="form-control" name="item" id="item_select" required>
                                         <option value="">Chọn item...</option>
                                        <?php
                                            $query = $CVH->query("SELECT * FROM `item_template` ORDER BY id ASC");
                                            if (mysqli_num_rows($query) > 0) {
                                                while ($row = mysqli_fetch_assoc($query)) {
                                                    ?>
                                         <option value="<?php echo $row['id']; ?>" data-icon="<?php echo getItemIcon($row['icon_id'] ?? 0); ?>" data-gender="<?php echo $row['gender'] ?? 0; ?>">
                                             <?php echo $row['id']; ?> - <?php echo $row['NAME']; ?>
                                         </option>
                                        <?php }
                                            } ?>
                                     </select>
                                     <button type="button" class="btn btn-primary" onclick="openItemSearch()">
                                         <i class="fas fa-search"></i>
                                     </button>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Section 2: Thiết Lập -->
                     <div class="form-section">
                         <div class="section-title">
                             <i class="fas fa-cog"></i>
                             2. Thiết Lập
                         </div>
                         <div class="form-row">
                             <div class="form-group">
                                 <label for="price">Giá Tiền</label>
                                 <input type="number" class="form-control" name="price" id="price" placeholder="Giá..." required>
                             </div>
                             
                             <div class="form-group">
                                 <label for="slot">Số Lượng</label>
                                 <input type="number" class="form-control" name="slot" id="slot" placeholder="SL..." required>
                             </div>
                         </div>
                     </div>

                     <!-- Section 3: Options -->
                     <div class="form-section">
                         <div class="section-title">
                             <i class="fas fa-magic"></i>
                             3. Options
                         </div>
                         <div id="optionsContainer" class="border rounded p-2 bg-light">
                             <!-- Options sẽ được thêm vào đây -->
                         </div>
                         <button type="button" class="btn btn-success mt-2" onclick="addOptionRow()">
                             <i class="fas fa-plus"></i> Thêm Option
                         </button>
                     </div>

                     <!-- Section 5: Lưu -->
                     <div class="form-section">
                         <div class="section-title">
                             <i class="fas fa-save"></i>
                             4. Lưu Item
                         </div>
                         <button type="submit" class="btn btn-danger">
                             <i class="fas fa-save"></i> Lưu Vào Shop
                         </button>
                     </div>
                 </div>

                 <!-- Cột phải: Preview (1/3) -->
                 <div class="col-md-4">
                     <div class="form-section preview-sidebar">
                         <div class="section-title">
                             <i class="fas fa-eye"></i>
                             Xem Trước
                         </div>
                         <div id="itemPreview" class="preview-section" style="display: none;">
                             <div class="preview-item">
                                 <img id="previewIcon" src="" alt="" class="preview-icon">
                                 <div class="preview-info">
                                     <h6 id="previewName">Tên Item</h6>
                                     <div id="previewDetails">Chi tiết item</div>
                                 </div>
                             </div>
                         </div>
                         <div id="emptyPreview" class="text-center text-muted py-4">
                             <i class="fas fa-eye-slash fa-2x mb-2"></i>
                             <p class="mb-0">Chọn item để xem trước</p>
                         </div>
                     </div>
                 </div>
             </div>
         </form>
     </div>

    <!-- Danh sách items hiện tại -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-list"></i> Danh Sách Items Trong Shop</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group">
        <?php
                $i = 1;
                if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $cvh_json = $row['options'];
                    $cvh_array = json_decode($cvh_json, true);
            ?>
        <div class="list-group-item list-group-item-action d-flex align-items-center text-body">
            <div class="w-50px h-50px d-flex align-items-center justify-content-center bg-body rounded p-2">
                <img src="<?php echo getItemIcon($row['icon_id'] ?? 0); ?>" alt="" class="mw-100 mh-100">
            </div>
            <div class="d-flex flex-fill px-5 justify-content-between">
                <div class="info-block">
                    <div class="fw-semibold"><b><?php echo $row['NAME']; ?></b></div>
                    <?php
                        // Tính tổng lượt mua từ cột users_buy (JSON)
                        $totalBuy = 0;
                        $userBuy = json_decode($row['users_buy'] ?? '[]', true);
                        if (is_array($userBuy)) { $totalBuy = count($userBuy); }

                        if (is_array($cvh_array)) {
                            foreach ($cvh_array as $option) {
                                $param = $option['param'];
                                $option_id = $option['id'];
                                $option_query = mysqli_query($CVH->connect_db(), "SELECT name FROM item_option_template WHERE id = '$option_id'");
                                if ($option_data = mysqli_fetch_assoc($option_query)) {
                                    $option_name = $option_data['name'];
                                    $option_display = str_replace('#', $param, $option_name);
                                    echo '<div class="small text-theme fs-10px">• <b>' . $option_display . '</b></div>';
                                } else {
                                    echo '<div class="small text-theme">• <b>(Tên không tìm thấy)</b></div>';
                                }
                            }
                        }
                        ?>
                </div>
                <div class="additional-info">
                    <div class="fw-semibold"><b>Thông tin:</b></div>
                    <div class="small text-body fs-10px">- Hành tinh: <b
                            class="text-success fs-10px"><?php echo getGender($row['gender']); ?></b></div>
                    <div class="small text-body fs-10px">- Giá tiền: <b
                            class="text-danger fs-10px"><?php echo number_format($row['price']); ?>đ</b></div>
                    <div class="small text-body fs-10px">- Đã mua: <b class="text-danger"><?php echo number_format($totalBuy); ?></b></div>
                    <div class="small text-body fs-10px">- Còn lại: <b
                            class="text-danger"><?php echo number_format($row['slot']); ?></b></div>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-end">
                <?php if ($user['is_super_admin'] ?? false): ?>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input hide-checkbox" data-id="<?php echo $row['id']; ?>"
                            <?php echo ($row['active'] == 1) ? 'checked' : ''; ?>>
                    </div>
                    <button href="javascript:void(0)" onclick="editPrice(<?php echo $row['id']; ?>, '<?php echo $row['NAME']; ?>', <?php echo $row['price']; ?>);"
                        class="btn btn-sm btn-warning me-1" title="Sửa giá"><i class="fa fa-edit"></i></button>
                    <button href="javascript:void(0)" onclick="del_(<?php echo $row['id']; ?>);"
                        class="btn btn-sm btn-theme"><i class="fa fa-trash"></i></button>
                <?php else: ?>
                    <div class="text-muted small">
                        <i class="fas fa-lock"></i> Bạn Không Có Quyền Sửa
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php } } else { ?>
        <div class="text-center py-5 mt-5">
            <img src="https://cdn-icons-png.flaticon.com/128/7466/7466139.png" width="50" class="img-fluid">
            <p class="pt-3"><b>Không có dữ liệu</b></p>
        </div>
        <?php } ?>
            </div>
        </div>
    </div>
    
    <div class="d-flex align-items-center justify-content-end py-5">
        <?php
                if ($tong > $kmess) {
                    echo '<center>' . $CVH->phantrang('add-shop?', $start, $tong, $kmess) . '</center>';
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
</div>

<!-- Modal Tìm kiếm Vật Phẩm -->
<div class="modal fade" id="itemSearchModal" tabindex="-1" aria-labelledby="itemSearchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="itemSearchModalLabel">Tìm Kiếm Vật Phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" class="form-control" id="itemSearchInput" placeholder="Nhập tên hoặc ID vật phẩm để tìm kiếm...">
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Tên Vật Phẩm</th>
                <th>Thao Tác</th>
              </tr>
            </thead>
            <tbody id="itemSearchResults">
              <!-- Kết quả tìm kiếm sẽ hiển thị ở đây -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tìm kiếm Option -->
<div class="modal fade" id="optionSearchModal" tabindex="-1" aria-labelledby="optionSearchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="optionSearchModalLabel">Tìm Kiếm Option</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" class="form-control" id="optionSearchInput" placeholder="Nhập tên hoặc ID option để tìm kiếm...">
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Tên Option</th>
                <th>Thao Tác</th>
              </tr>
            </thead>
            <tbody id="optionSearchResults">
              <!-- Kết quả tìm kiếm sẽ hiển thị ở đây -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Sửa Giá Tiền -->
<div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPriceModalLabel">Sửa Giá Tiền</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editPriceForm">
          <input type="hidden" id="editItemId" name="item_id">
          <div class="mb-3">
            <label for="editItemName" class="form-label">Tên Item</label>
            <input type="text" class="form-control" id="editItemName" readonly>
          </div>
          <div class="mb-3">
            <label for="editItemPrice" class="form-label">Giá Tiền Mới</label>
            <div class="input-group">
              <input type="number" class="form-control" id="editItemPrice" name="price" required min="0">
              <span class="input-group-text">VNĐ</span>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-primary" onclick="savePrice()">Lưu Thay Đổi</button>
      </div>
    </div>
  </div>
</div>
<script>
// Khởi tạo form khi trang load
$(document).ready(function() {
    // Bỏ Select2, dùng dropdown thông thường
    
    // Thêm option mặc định
    addOptionRow();
    
    // Cập nhật preview khi thay đổi item
    $('#item_select').change(function() {
        updateItemPreview();
    });
    
         // Cập nhật preview khi thay đổi thông tin cơ bản
     $('#price, #slot').on('input change', function() {
         updateItemPreview();
     });
    
    // Xử lý checkbox active
    $('.hide-checkbox').change(function() {
        var active = $(this).is(':checked') ? 1 : 0;
        var id = $(this).data('id');

        $.ajax({
            url: '/ajax/admin/shop/update_status.php',
            type: 'POST',
            data: {
                active: active,
                id: id
            },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, active, error) {}
        });
    });
});

// Thêm option row
function addOptionRow() {
    var optionCount = $('.option-row').length;
    if (optionCount >= 10) {
        Swal.fire('Thông báo', 'Chỉ được thêm tối đa 10 options!', 'info');
        return;
    }
    
    var optionHtml = `
        <div class="option-row" data-row="${optionCount}">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-cog"></i> Option ID</label>
                    <div class="input-group">
                                                 <select class="form-control" name="option_id[]">
                             <option value="">Chọn option...</option>
                            <?php
                                $query = $CVH->query("SELECT * FROM `item_option_template` ORDER BY id ASC");
                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        ?>
                            <option value="<?php echo $row['id']; ?>">
                                <?php echo $row['id']; ?> - <?php echo $row['NAME']; ?>
                            </option>
                            <?php }
                                } ?>
                        </select>
                        <button type="button" class="btn btn-primary" onclick="openOptionSearch(this)">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sliders-h"></i> Chỉ Số</label>
                    <input type="text" class="form-control" name="param_option[]" placeholder="Nhập chỉ số...">
                </div>
                <div class="form-group d-flex align-items-end">
                    <button type="button" class="btn btn-remove" onclick="removeOptionRow(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#optionsContainer').append(optionHtml);
    
         // Bỏ Select2 cho options
}

// Xóa option row
function removeOptionRow(button) {
    $(button).closest('.option-row').remove();
    updateItemPreview();
}

 // Cập nhật preview item
 function updateItemPreview() {
     var itemId = $('#item_select').val();
     var itemName = $('#item_select option:selected').text();
     var selectedOption = $('#item_select option:selected');
           var gender = selectedOption.data('gender') || '0'; // Lấy gender từ data attribute
     var price = $('#price').val();
     var slot = $('#slot').val();
     
     // Cập nhật hidden input gender
     $('#gender').val(gender);
     
     if (itemId && itemName !== 'Chọn item...') {
         var genderText = '';
         var genderClass = '';
         
                   // Chuyển gender về string để so sánh
          var genderStr = String(gender);
         
         switch(genderStr) {
             case '0': genderText = 'Trái Đất'; genderClass = 'planet-0'; break;
             case '1': genderText = 'Namek'; genderClass = 'planet-1'; break;
             case '2': genderText = 'Xayda'; genderClass = 'planet-2'; break;
             case '3': genderText = 'All'; genderClass = 'planet-3'; break;
             case '': genderText = 'Chưa chọn'; genderClass = 'planet-default'; break;
             case 'null': genderText = 'Chưa chọn'; genderClass = 'planet-default'; break;
             case 'undefined': genderText = 'Chưa chọn'; genderClass = 'planet-default'; break;
             default: genderText = 'Chưa chọn'; genderClass = 'planet-default';
         }
        
                 var details = '';
         if (price) details += `Giá: ${Number(price).toLocaleString()}đ | `;
         if (slot) details += `SL: ${slot} | `;
         details += `<span class="planet-badge ${genderClass}">${genderText}</span>`;
        
        // Thêm thông tin options
        var optionsHtml = '';
        $('.option-row').each(function() {
            var optionId = $(this).find('select').val();
            var optionParam = $(this).find('input').val();
            if (optionId) {
                var optionText = $(this).find('select option:selected').text();
                optionsHtml += `<div class="small text-muted">• ${optionText}${optionParam ? ': ' + optionParam : ''}</div>`;
            }
        });
        
                 $('#previewName').text(itemName);
         $('#previewDetails').html(details + optionsHtml);
         $('#previewIcon').attr('src', $('#item_select option:selected').data('icon') || '/images/default-item.png');
         $('#itemPreview').show();
         $('#emptyPreview').hide();
     } else {
         $('#itemPreview').hide();
         $('#emptyPreview').show();
     }
}

// Hàm cũ để tương thích
function addRow(data) {
    if ($('.list_row .form_gift').length > 9) {
        notice("Chỉ được thêm tối đa 10 dòng", "info");
        return
    }
    $.ajax({
        url: '/ajax/admin/shop/addrow.php',
        beforeSend: function() {
            $(data).html(
                '<div class="spinner-border spinner-border-sm text-light" role="active"> <span class="visually-hidden"></span></div>'
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

// Hàm mở modal tìm kiếm vật phẩm
function openItemSearch() {
    $('#itemSearchModal').modal('show');
    // Chỉ load dữ liệu khi modal mở
    if ($('#itemSearchResults').is(':empty')) {
        loadAllItems();
    }
}

// Hàm mở modal tìm kiếm option
function openOptionSearch(button) {
    $('#optionSearchModal').modal('show');
    // Lưu reference đến button để biết option nào đang được chọn
    $('#optionSearchModal').data('currentButton', button);
    // Chỉ load dữ liệu khi modal mở
    if ($('#optionSearchResults').is(':empty')) {
        loadAllOptions();
    }
}

// Cache để lưu dữ liệu đã tải
var itemsCache = null;
var optionsCache = null;

// Load tất cả vật phẩm
function loadAllItems() {
    // Kiểm tra cache trước
    if (itemsCache) {
        $('#itemSearchResults').html(itemsCache);
        return;
    }
    
    $('#itemSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
    $.ajax({
        url: '/ajax/admin/shop/search_items.php',
        type: 'POST',
        data: { type: 'items' },
        success: function(response) {
            itemsCache = response; // Lưu vào cache
            $('#itemSearchResults').html(response);
        },
        error: function() {
            $('#itemSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>');
        }
    });
}

// Load tất cả options
function loadAllOptions() {
    // Kiểm tra cache trước
    if (optionsCache) {
        $('#optionSearchResults').html(optionsCache);
        return;
    }
    
    $('#optionSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
    $.ajax({
        url: '/ajax/admin/shop/search_items.php',
        type: 'POST',
        data: { type: 'options' },
        success: function(response) {
            optionsCache = response; // Lưu vào cache
            $('#optionSearchResults').html(response);
        },
        error: function() {
            $('#optionSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>');
        }
    });
}

// Chọn vật phẩm
function selectItem(id, name) {
    $('#item_select').val(id).trigger('change');
    $('#itemSearchModal').modal('hide');
    updateItemPreview();
}

// Chọn option
function selectOption(id, name) {
    var button = $('#optionSearchModal').data('currentButton');
    var select = $(button).closest('.input-group').find('select');
    select.val(id).trigger('change');
    $('#optionSearchModal').modal('hide');
    updateItemPreview();
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
    var searchItems = debounce(function(searchTerm) {
        if (searchTerm.length > 0) {
            $('#itemSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</td></tr>');
            $.ajax({
                url: '/ajax/admin/shop/search_items.php',
                type: 'POST',
                data: { 
                    type: 'items',
                    search: searchTerm 
                },
                success: function(response) {
                    $('#itemSearchResults').html(response);
                },
                error: function() {
                    $('#itemSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tìm kiếm</td></tr>');
                }
            });
        } else {
            loadAllItems();
        }
    }, 300); // Chờ 300ms sau khi ngừng gõ

    $('#itemSearchInput').on('input', function() {
        var searchTerm = $(this).val();
        searchItems(searchTerm);
    });

    // Tìm kiếm option với debounce
    var searchOptions = debounce(function(searchTerm) {
        if (searchTerm.length > 0) {
            $('#optionSearchResults').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</td></tr>');
            $.ajax({
                url: '/ajax/admin/shop/search_items.php',
                type: 'POST',
                data: { 
                    type: 'options',
                    search: searchTerm 
                },
                success: function(response) {
                    $('#optionSearchResults').html(response);
                },
                error: function() {
                    $('#optionSearchResults').html('<tr><td colspan="3" class="text-center text-danger">Lỗi tìm kiếm</td></tr>');
                }
            });
        } else {
            loadAllOptions();
        }
    }, 300); // Chờ 300ms sau khi ngừng gõ

    $('#optionSearchInput').on('input', function() {
        var searchTerm = $(this).val();
        searchOptions(searchTerm);
    });
    
         // Cập nhật preview khi thay đổi options
     $(document).on('change', 'select[name="option_id[]"]', function() {
         updateItemPreview();
     });
    
    $(document).on('input', 'input[name="param_option[]"]', function() {
        updateItemPreview();
    });
});

// Hàm mở modal sửa giá
function editPrice(id, name, currentPrice) {
    $('#editItemId').val(id);
    $('#editItemName').val(name);
    $('#editItemPrice').val(currentPrice);
    $('#editPriceModal').modal('show');
}

// Hàm lưu giá mới
function savePrice() {
    var itemId = $('#editItemId').val();
    var newPrice = $('#editItemPrice').val();
    
    if (!newPrice || newPrice < 0) {
        Swal.fire('Thông báo', 'Vui lòng nhập giá tiền hợp lệ!', 'error');
        return;
    }
    
    $.ajax({
        type: 'POST',
        url: '/ajax/admin/shop/update_price.php',
        data: {
            item_id: itemId,
            price: newPrice
        },
        success: function(response) {
            try {
                var data = JSON.parse(response);
                if (data.status == true) {
                    Swal.fire('Thành công', data.message, 'success').then(() => {
                        $('#editPriceModal').modal('hide');
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra!', 'error');
                }
            } catch (e) {
                Swal.fire('Lỗi', 'Có lỗi xảy ra khi xử lý phản hồi!', 'error');
            }
        },
        error: function() {
            Swal.fire('Lỗi', 'Có lỗi xảy ra khi kết nối server!', 'error');
        }
    });
}

function del_(id) {
    Swal.fire({
        title: 'Thông Báo',
        text: 'Bạn có muốn xóa sản phẩm có ID #' + id + ' không!',
        icon: 'warning',
        showDenyButton: true,
        confirmButtonText: 'Đồng Ý',
        denyButtonText: `Đóng`,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: '/ajax/admin/shop/delete.php',
                data: {
                    type: 'Del_Gift',
                    id: id
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status == true) {
                        Swal.fire('Thông báo', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Thông báo', 'Có lỗi xảy ra vui lòng thử lại!', 'error').then(
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