<?php
// Quyền
if (!$user['is_admin']) { echo '<div class="alert alert-danger">Bạn không có quyền truy cập!</div>'; exit; }
if (!$user['is_super_admin'] && empty($user['perm_item_template_manager'])) { echo '<div class="alert alert-danger">Bạn không có quyền quản lý Item Template!</div>'; exit; }

$csrf_token = $CVH->getCSRFToken($user['id']);
?>
<div id="content" class="app-content">
    <div class="row">
        <div class="col-xl-12 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-database me-2"></i>Quản lý Item Template</h4>
                    <button class="btn btn-primary btn-sm" onclick="showItemForm()"><i class="fas fa-plus"></i> Thêm item</button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12 d-flex align-items-center gap-2" id="topControls">
                            <div class="input-group" style="max-width: 640px;">
                                <input type="text" class="form-control" id="q" placeholder="Tìm theo ID hoặc tên... (TYPE mặc định 75)">
                                <span class="input-group-text">Từ ID</span>
                                <input type="number" class="form-control" id="minId" value="0" min="0">
                                <button class="btn btn-primary" onclick="loadItems()"><i class="fas fa-search"></i></button>
                            </div>
                            <div id="itemPaginationTop" class="ms-2"></div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>TYPE</th>
                                    <th>gender</th>
                                    <th>NAME</th>
                                    <th>description</th>
                                    <th>icon_id</th>
                                    <th>part</th>
                                    <th>is_up_to_up</th>
                                    <th>power_require</th>
                                    <th>gold</th>
                                    <th>gem</th>
                                    <th>head</th>
                                    <th>body</th>
                                    <th>leg</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalTitle">Thêm/Sửa Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="itemForm">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <div class="row g-3">
            <div class="col-md-2"><label class="form-label">ID (để trống để tự tăng)</label><input type="number" class="form-control" name="id" id="it_id"></div>
          </div>
          <div class="row g-3">
            <div class="col-md-2"><label class="form-label">TYPE</label><input type="number" class="form-control" name="TYPE" id="it_type" required></div>
            <div class="col-md-2"><label class="form-label">gender</label><input type="number" class="form-control" name="gender" id="it_gender" required></div>
            <div class="col-md-4"><label class="form-label">NAME</label><input type="text" class="form-control" name="NAME" id="it_name" required></div>
            <div class="col-md-4"><label class="form-label">description</label><input type="text" class="form-control" name="description" id="it_desc"></div>
            <div class="col-md-2"><label class="form-label">icon_id</label><input type="number" class="form-control" name="icon_id" id="it_icon"></div>
            <div class="col-md-2"><label class="form-label">part</label><input type="number" class="form-control" name="part" id="it_part"></div>
            <div class="col-md-2"><label class="form-label">is_up_to_up</label><input type="number" class="form-control" name="is_up_to_up" id="it_isup"></div>
            <div class="col-md-2"><label class="form-label">power_require</label><input type="number" class="form-control" name="power_require" id="it_power"></div>
            <div class="col-md-2"><label class="form-label">gold</label><input type="number" class="form-control" name="gold" id="it_gold"></div>
            <div class="col-md-2"><label class="form-label">gem</label><input type="number" class="form-control" name="gem" id="it_gem"></div>
            <div class="col-md-2"><label class="form-label">head</label><input type="number" class="form-control" name="head" id="it_head"></div>
            <div class="col-md-2"><label class="form-label">body</label><input type="number" class="form-control" name="body" id="it_body"></div>
            <div class="col-md-2"><label class="form-label">leg</label><input type="number" class="form-control" name="leg" id="it_leg"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-primary" onclick="saveItem()"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </div>
  </div>
</div>

<script>
function showItemForm(item){
  const m = new bootstrap.Modal(document.getElementById('itemModal'));
  document.getElementById('itemForm').reset();
  document.getElementById('it_id').value = item?.id || '';
  document.getElementById('it_type').value = item?.TYPE || '';
  document.getElementById('it_gender').value = item?.gender || '';
  document.getElementById('it_name').value = item?.NAME || '';
  document.getElementById('it_desc').value = item?.description || '';
  document.getElementById('it_icon').value = item?.icon_id || '';
  document.getElementById('it_part').value = item?.part || '';
  document.getElementById('it_isup').value = item?.is_up_to_up || '';
  document.getElementById('it_power').value = item?.power_require || '';
  document.getElementById('it_gold').value = item?.gold || '';
  document.getElementById('it_gem').value = item?.gem || '';
  document.getElementById('it_head').value = item?.head || '';
  document.getElementById('it_body').value = item?.body || '';
  document.getElementById('it_leg').value = item?.leg || '';
  m.show();
}

function loadItems(){
  const q = document.getElementById('q').value.trim();
  const minId = document.getElementById('minId').value || '';
  const params = new URLSearchParams();
  if(q) params.set('q', q);
  if(minId) params.set('min_id', minId);
  const page = window.currentPage || 1;
  params.set('page', page);
  fetch('/ajax/admin/item_template/list.php?' + params.toString())
  .then(r=>r.json()).then(d=>{
    const body = document.getElementById('itemsBody');
    if(!d.status){ body.innerHTML = '<tr><td colspan="15" class="text-center">Không có dữ liệu</td></tr>'; return; }
    let html = '';
    d.items.forEach(it=>{
      html += `<tr>
        <td>${it.id}</td>
        <td>${it.TYPE}</td>
        <td>${it.gender}</td>
        <td>${it.NAME||''}</td>
        <td>${it.description||''}</td>
        <td>${it.icon_id||0}</td>
        <td>${it.part||0}</td>
        <td>${it.is_up_to_up||0}</td>
        <td>${it.power_require||0}</td>
        <td>${it.gold||0}</td>
        <td>${it.gem||0}</td>
        <td>${it.head||0}</td>
        <td>${it.body||0}</td>
        <td>${it.leg||0}</td>
        <td>
          <button class="btn btn-sm btn-primary" onclick='showItemForm(${JSON.stringify(it)})'><i class="fas fa-edit"></i></button>
        </td>
      </tr>`;
    });
    body.innerHTML = html || '<tr><td colspan="15" class="text-center">Không có dữ liệu</td></tr>';

    // render pagination (simple prev/next)
    renderPagination(d.page, d.per_page, d.total);
  })
}

function renderPagination(page, perPage, total){
  const containerId = 'itemPagination';
  let cont = document.getElementById(containerId);
  if(!cont){
    // ưu tiên gắn lên khu vực topControls
    const top = document.getElementById('itemPaginationTop');
    if (top) {
      cont = top;
      cont.id = containerId;
      cont.className = 'd-flex justify-content-start align-items-center gap-2';
    } else {
      cont = document.createElement('div');
      cont.id = containerId;
      cont.className = 'd-flex justify-content-center align-items-center gap-2 mt-2';
      document.querySelector('#content .card-body').appendChild(cont);
    }
  }
  const totalPages = Math.max(1, Math.ceil(total / perPage));
  let prevDisabled = page<=1 ? 'disabled' : '';
  let nextDisabled = page>=totalPages ? 'disabled' : '';
  // build select options like the screenshot
  let opts = '';
  for(let i=1;i<=totalPages;i++){ opts += `<option value="${i}" ${i===page?'selected':''}>${i}</option>`; }
  cont.innerHTML = `
    <nav>
      <ul class="pagination mb-0">
        <li class="page-item ${prevDisabled}"><a class="page-link" href="#" onclick="changePage(1);return false;">««</a></li>
        <li class="page-item ${prevDisabled}"><a class="page-link" href="#" onclick="changePage(${page-1});return false;">«</a></li>
        <li class="page-item disabled"><span class="page-link">Trang</span></li>
      </ul>
    </nav>
    <select class="form-select form-select-sm" style="width:auto" onchange="changePage(parseInt(this.value))">${opts}</select>
    <nav>
      <ul class="pagination mb-0">
        <li class="page-item ${nextDisabled}"><a class="page-link" href="#" onclick="changePage(${page+1});return false;">»</a></li>
        <li class="page-item ${nextDisabled}"><a class="page-link" href="#" onclick="changePage(${totalPages});return false;">»»</a></li>
      </ul>
    </nav>
  `;
}

function changePage(p){ if(p<1) return; window.currentPage = p; loadItems(); }

function saveItem(){
  const fd = new FormData(document.getElementById('itemForm'));
  fetch('/ajax/admin/item_template/save.php', { method:'POST', body: fd })
  .then(async r=>{
    if(!r.ok){
      const text = await r.text();
      Swal.fire('Lỗi', text || ('Lỗi server ('+r.status+')'), 'error');
      throw new Error('HTTP '+r.status);
    }
    return r.json();
  })
  .then(d=>{
    if(!d) return;
    if(d.status){
      Swal.fire('Thành công', d.message, 'success');
      document.getElementById('itemForm').reset();
      bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
      loadItems();
    } else {
      Swal.fire('Lỗi', (d.message || 'Không thể lưu') + (d.mysql_error? '<br><small>'+d.mysql_error+'</small>' : ''), 'error');
    }
  }).catch(e=> Swal.fire('Lỗi', e.message || 'Không thể kết nối', 'error'))
}

window.addEventListener('load', loadItems);
</script>
