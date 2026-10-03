<?php
if (!$user['is_admin']) { echo '<div class="alert alert-danger">Bạn không có quyền truy cập!</div>'; exit; }
if (!$user['is_super_admin'] && empty($user['perm_maintenance_manager'])) { echo '<div class="alert alert-danger">Bạn không có quyền bảo trì server!</div>'; exit; }
$csrf_token = $CVH->getCSRFToken($user['id']);
?>
<div id="content" class="app-content">
  <div class="row">
    <div class="col-xl-6 mb-3">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0"><i class="fas fa-tools me-2"></i>Chế độ bảo trì</h4>
        </div>
        <div class="card-body">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Lưu ý:</strong> Chế độ bảo trì sẽ ngăn người chơi truy cập vào game server.
          </div>
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="maintToggle">
              <label class="form-check-label fw-bold" for="maintToggle">Bật chế độ bảo trì</label>
            </div>
            <span id="maintSaving" class="text-muted" style="display:none;">
              <i class="fas fa-spinner fa-spin me-1"></i>Đang cập nhật...
            </span>
          </div>
          <small id="maintUpdated" class="text-muted d-block"></small>
        </div>
      </div>
    </div>
    
    <div class="col-xl-6 mb-3">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0"><i class="fas fa-server me-2"></i>Điều khiển server</h4>
        </div>
        <div class="card-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Hướng dẫn:</strong> Gửi yêu cầu điều khiển server qua database. Cờ sẽ tự động reset sau 1 phút.
          </div>
          
          <div class="row g-3">
            <div class="col-md-6">
              <button class="btn btn-success w-100" id="btnRunServer">
                <i class="fas fa-play me-2"></i>Khởi động server
              </button>
            </div>
            <div class="col-md-6">
              <button class="btn btn-danger w-100" id="btnStopServer">
                <i class="fas fa-stop me-2"></i>Dừng server
              </button>
            </div>
          </div>
          
          <div class="mt-3">
            <div class="row g-2">
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <span class="badge bg-success me-2" id="startStatus">0</span>
                  <small>Khởi động</small>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex align-items-center">
                  <span class="badge bg-danger me-2" id="stopStatus">0</span>
                  <small>Dừng</small>
                </div>
              </div>
            </div>
            <small id="ctrlUpdated" class="text-muted d-block mt-2"></small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let __maintInit = false;
function loadMaint(){
  fetch('/ajax/admin/maintenance/get.php').then(r=>r.json()).then(d=>{
    if(d.status){
      __maintInit = true;
      document.getElementById('maintToggle').checked = d.data.status == 1;
      __maintInit = false;
      document.getElementById('maintUpdated').textContent = 'Cập nhật lần cuối: ' + (d.data.updated_at || 'N/A');
      
      // Cập nhật trạng thái server control
      document.getElementById('startStatus').textContent = d.data.start_request || 0;
      document.getElementById('stopStatus').textContent = d.data.stop_request || 0;
      document.getElementById('ctrlUpdated').textContent = 'Cập nhật lần cuối: ' + (d.data.ctrl_updated_at || 'N/A');
      
      // Cập nhật màu badge
      document.getElementById('startStatus').className = 'badge ' + (d.data.start_request == 1 ? 'bg-warning' : 'bg-success') + ' me-2';
      document.getElementById('stopStatus').className = 'badge ' + (d.data.stop_request == 1 ? 'bg-warning' : 'bg-danger') + ' me-2';
    }
  });
}

function saveMaint(){
  const fd = new FormData();
  fd.append('csrf_token','<?php echo htmlspecialchars($csrf_token); ?>');
  fd.append('status', document.getElementById('maintToggle').checked ? '1':'0');
  document.getElementById('maintSaving').style.display = 'inline';
  fetch('/ajax/admin/maintenance/set.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
    if(d.status){
      loadMaint();
      Swal.fire('Thành công', 'Đã cập nhật chế độ bảo trì', 'success');
    } else {
      Swal.fire('Lỗi', d.message || 'Không thể lưu', 'error');
    }
    document.getElementById('maintSaving').style.display = 'none';
  }).catch(()=> Swal.fire('Lỗi', 'Không thể kết nối', 'error'))
}

window.addEventListener('load', loadMaint);
document.addEventListener('DOMContentLoaded', function(){
  const toggle = document.getElementById('maintToggle');
  toggle.addEventListener('change', function(){
    if(__maintInit) return;
    saveMaint();
  });
  
  document.getElementById('btnRunServer').addEventListener('click', function(){
    const fd = new FormData(); 
    fd.append('csrf_token','<?php echo htmlspecialchars($csrf_token); ?>'); 
    fd.append('action','start');
    const btn = this; 
    btn.disabled = true; 
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';
    
    fetch('/ajax/admin/maintenance/queue.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
      if(d.status){ 
        Swal.fire('Thành công', d.message, 'success');
        loadMaint(); // Reload để cập nhật trạng thái
      } else { 
        Swal.fire('Lỗi', d.message || 'Không thể gửi yêu cầu', 'error'); 
      }
    }).catch(()=> Swal.fire('Lỗi', 'Không thể kết nối', 'error')).finally(()=>{ 
      btn.disabled=false; 
      btn.innerHTML='<i class="fas fa-play me-2"></i>Khởi động server'; 
    });
  });
  
  document.getElementById('btnStopServer').addEventListener('click', function(){
    const fd = new FormData(); 
    fd.append('csrf_token','<?php echo htmlspecialchars($csrf_token); ?>'); 
    fd.append('action','stop');
    const btn = this; 
    btn.disabled = true; 
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';
    
    fetch('/ajax/admin/maintenance/queue.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
      if(d.status){ 
        Swal.fire('Thành công', d.message, 'success');
        loadMaint(); // Reload để cập nhật trạng thái
      } else { 
        Swal.fire('Lỗi', d.message || 'Không thể gửi yêu cầu', 'error'); 
      }
    }).catch(()=> Swal.fire('Lỗi', 'Không thể kết nối', 'error')).finally(()=>{ 
      btn.disabled=false; 
      btn.innerHTML='<i class="fas fa-stop me-2"></i>Dừng server'; 
    });
  });
  
  // Auto refresh trạng thái mỗi 30 giây
  setInterval(loadMaint, 30000);
});
</script>
