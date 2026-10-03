<div id="content" class="app-content">
    <div class="row">
        <style>
        .image-picker { display:flex; flex-wrap:wrap; gap:8px; }
        .image-card { width:140px; display:flex; flex-direction:column; align-items:center; gap:6px; position:relative; }
        .cvh-img { width: 120px; height: 60px; object-fit: contain; border-radius: 8px; border:1px solid #e9ecef; padding:6px; background:#fff; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .form-check-input.cvh-check:checked + .form-check-label.cvh-check .cvh-img { border-color:#0d6efd; box-shadow:0 0 0 3px rgba(13,110,253,.15); }
        .image-name { max-width:120px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:12px; color:#6c757d; }
        .img-actions { position:absolute; top:6px; right:6px; display:flex; gap:6px; opacity:0; transition:opacity .15s ease; }
        .image-card:hover .img-actions { opacity:1; }
        .img-actions .btn { padding:2px 6px; font-size:12px; }
        .toolbar { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
        .toolbar .form-control, .toolbar .form-select { max-width:240px; }
        </style>
        <div class="col-xl-12 mb-3">

            <div class="card">
                <div class="card-body">
                    <form class="form pt-3" cvhvn="true" method="POST" action="/ajax/admin/download/add.php"
                        href="<?php echo getCurrentURL(); ?>">
                        <div class="mb-3 row">
                            <label for="example-text-input" class="col-md-2 col-form-label">Phụ Đề</label>
                            <div class="col-md-5">
                                <input class="form-control" type="text" name="text" placeholder="Phụ Đề">
                            </div>
                            <div class="col-md-5">
                                <input class="form-control" type="text" name="url" placeholder="Link của tiêu đề">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="example-search-input" class="col-md-2 col-form-label">Navbar</label>
                            <div class="col-md-10">
                                <div class="small text-inverse text-opacity-50 mb-2"><b class="fw-bold">Chọn trạng thái
                                        hiển thị</b>
                                </div>
                                <div class="form-group mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" name="type" type="radio" value="download" checked>
                                        <label class="form-check-label">Download</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" name="type" type="radio" value="social">
                                        <label class="form-check-label">Social</label>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="example-text-input" class="col-md-2 col-form-label">Link Tải</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" name="link"
                                    placeholder="Nhập đường dẫn hoặc đường link">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="avatar" class="col-md-2 col-form-label">Ảnh đại diện <button type="button"
                                    class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#upAVT">UP
                                    IMG</button></label>
                            <div class="col-md-10">
                                <?php
                                $folderPath = $_SERVER['DOCUMENT_ROOT'] . '/images/'; 
                                if (is_dir($folderPath)) {
                                    $files = scandir($folderPath);
                                    echo '<div class="image-picker">';
                                    foreach ($files as $file) {
                                        if ($file !== '.' && $file !== '..' && is_file($folderPath . '/' . $file)) {
                                            $fileName = pathinfo($file, PATHINFO_FILENAME);
                                            $filePath = '/images/' . $file;
                                            echo '<div class="form-check cvh-check form-check-inline text-center">
                                                    <input class="form-check-input cvh-check" type="radio" name="image" id="image_' . htmlspecialchars($fileName) . '" value="' . htmlspecialchars($filePath) . '">
                                                    <label class="form-check-label cvh-check d-block" for="image_' . htmlspecialchars($fileName) . '">
                                                        <img src="' . htmlspecialchars($filePath) . '" class="cvh-img" alt="Image ' . htmlspecialchars($fileName) . '"> 
                                                        <div class="small text-muted mt-1" style="max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' . htmlspecialchars($file) . '</div>
                                                    </label>
                                                    <div class="mt-1 d-flex justify-content-center" style="gap:6px">
                                                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="copyImgPath(\'' . htmlspecialchars($filePath) . '\')"><i class="fa fa-copy"></i></button>
                                                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteImg(\'' . htmlspecialchars($filePath) . '\')"><i class="fa fa-trash"></i></button>
                                                    </div>
                                                  </div>';
                                        }
                                    }
                                    echo '</div>';
                                } else {
                                    echo '<p>Không có ảnh đại diện nào</p>';
                                }
                                ?>
                                <div id="imgPreviewBox" class="alert alert-secondary py-2 mt-2" style="display:none">Đã chọn: <code id="imgPath"></code></div>
                            </div>
                        </div>
                        <div class="mb-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-success" href="<?php echo getCurrentURL(); ?>">Lưu
                                Lại</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php
               $current_data = $CVH->get_row("SELECT download FROM cvh_setting WHERE id = 1");
               $current_download = !empty($current_data) ? json_decode($current_data['download'], true) : [];
             ?>
            <div class="card card-body mt-2">
                <div class="table-responsive">
                    <table class="table search-table align-middle text-nowrap">
                        <thead class="header-item">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Link</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="result">
                            <?php
                                if (!empty($current_download)) {
                                  foreach ($current_download as $item) {
                                $id = $item['id']; 
                            ?>
                           <tr
    data-id="<?php echo $id; ?>"
    data-text="<?php echo htmlspecialchars($item['description']['text']); ?>"
    data-url="<?php echo htmlspecialchars($item['description']['link']); ?>"
    data-link="<?php echo htmlspecialchars($item['link']); ?>"
    data-type="<?php echo htmlspecialchars($item['type']); ?>"
    data-image="<?php echo htmlspecialchars($item['image']); ?>"
>
    <td><?php echo htmlspecialchars($id); ?></td>
    <td>
        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Image" width="50">
    </td>
    <td>
        <a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo htmlspecialchars($item['link']); ?></a>
    </td>
    <td>
        <span><?php echo htmlspecialchars($item['description']['text']); ?></span>
    </td>
    <td>
        <div class="action-btn">
            <a href="javascript:void(0)" onclick="edit_(<?php echo $id; ?>);"
                class="btn btn-sm btn-warning ms-2">
                <i class="fa fa-edit fs-5"></i>
            </a>
            <div class="action-btn">
                <a href="javascript:void(0)" onclick="del_(<?php echo $id; ?>);"
                    class="btn btn-sm btn-danger ms-2">
                    <i class="fa fa-trash-alt fs-5"></i>
                </a>
            </div>
        </div>
    </td>
</tr>
                            <?php } } else { ?>
                            <tr class="text-center">
                                <td colspan='5'>
                                    <img src="https://cdn-icons-png.flaticon.com/128/7466/7466139.png" width="50"
                                        class="img-fluid">
                                    <p class="pt-3"><b>Không có dữ liệu</b></p>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
</div>
</div>
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Sửa Link Download</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3">
            <label>Phụ Đề</label>
            <input type="text" class="form-control" name="text" id="edit_text">
          </div>
          <div class="mb-3">
            <label>description</label>
            <input type="text" class="form-control" name="url" id="edit_url">
          </div>
            <div class="mb-3">
    <label>Loại</label>
    <select class="form-control" name="type" id="edit_type" required>
      <option value="">Chọn trạng thái hiển thị</option>
      <option value="download">Download</option>
      <option value="social">Social</option>
    </select>
  </div>
          <div class="mb-3">
            <label>Link Tải</label>
            <input type="text" class="form-control" name="link" id="edit_link">
          </div>
          <div class="mb-3">
            <label>Ảnh đại diện</label>
            <input type="text" class="form-control" name="image" id="edit_image">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="upAVT" tabindex="-1" aria-labelledby="upAVTLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="upAVTLabel">Tải lên ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="avatar-upload text-center">
                    <input type="file" id="avatarUpload" accept="image/*" name="avatar" style="display: none;"
                        onchange="previewImage(event)">
                    <label for="avatarUpload" class="avatar-label">Chọn ảnh cần tải lên</label>
                    <div class="text-center py-3">
                        <div id="previewContainer" class="preview-container"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="uploadAvatar()">Tải lên</button>
            </div>
        </div>
    </div>
</div>

<script>
function copyImgPath(path){
  if(navigator.clipboard){ navigator.clipboard.writeText(path).then(()=>{ toastr?.success('Đã copy đường dẫn ảnh'); }); }
}
function deleteImg(path){
  Swal.fire({ title:'Xóa ảnh?', text:path, icon:'warning', showCancelButton:true, confirmButtonText:'Xóa' })
  .then(res=>{ if(!res.isConfirmed) return; $.post('/ajax/admin/download/delete_image.php', {file:path}, function(r){ var d = r; if (typeof r === 'string') { try { d = JSON.parse(r); } catch(e) { d = null; } } if(d && d.success){ location.reload(); } else { Swal.fire('Lỗi', (d&&d.message)||'Không thể xóa', 'error'); } }, 'json').fail(function(){ Swal.fire('Lỗi', 'Không thể xóa', 'error'); }); });
}
// Hiển thị preview đường dẫn ảnh đã chọn
document.addEventListener('change', function(e){
  if(e.target && e.target.matches('input[name="image"]')){
    const p = document.getElementById('imgPath');
    const box = document.getElementById('imgPreviewBox');
    if(p&&box){ p.textContent = e.target.value; box.style.display='block'; }
  }
});
function previewImage(event) {
    const previewContainer = document.getElementById('previewContainer');
    previewContainer.innerHTML = '';

    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}

function uploadAvatar() {
    const fileInput = document.getElementById('avatarUpload');
    const file = fileInput.files[0];
    if (!file) {
        alert("Vui lòng chọn ảnh đại diện!");
        return;
    }
    const formData = new FormData();
    formData.append('avatar', file);

    fetch('/ajax/admin/download/upavt.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Tải lên thành công! File: ' + data.fileName);
                $('#upAVT').modal('hide');
            } else {
                alert('Tải lên thất bại: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra trong quá trình tải lên!');
        });
}



function del_(id) {
    Swal.fire({
        title: 'Thông Báo',
        text: 'Bạn có muốn xóa link tải có ID #' + id + ' không!',
        icon: 'warning',
        showDenyButton: true,
        confirmButtonText: 'Đồng Ý',
        denyButtonText: `Đóng`,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: '/ajax/admin/download/delete.php',
                data: {
                    type: 'Del_Post',
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
<script>
function edit_(id) {
    var row = $('#result tr').filter(function() {
        return $(this).data('id') == id;
    });
    $('#edit_id').val(row.data('id'));
    $('#edit_text').val(row.data('text'));
    $('#edit_url').val(row.data('url'));
    $('#edit_link').val(row.data('link'));
    $('#edit_type').val(row.data('type'));
    $('#edit_image').val(row.data('image'));
    $('#editModal').modal('show');
}

$('#editForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
        url: '/ajax/admin/download/edit.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            var data = JSON.parse(response);
            if (data.status) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        }
    });
});
</script>