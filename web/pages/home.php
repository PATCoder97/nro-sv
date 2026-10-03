<div id="banner" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="<?php echo $setting['banner']; ?>" class="d-block w-100" alt="">
        </div>
    </div>
</div>
<div id="gioithieu" class="bg-component">

    <div class="col">
        <div class="text-center pt-2">
            <!-- PHIÊN BẢN 3 TAB -->
            <div class="mb-4">
                <h6 class="text-center mb-3" style="color: #ff6b35; font-weight: bold;">PHIÊN BẢN MOD</h6>
                <div class="text-center">
                    <?php
                       $current_data = $CVH->get_row("SELECT download FROM cvh_setting WHERE id = 1");
                       $current_download = !empty($current_data) ? json_decode($current_data['download'], true) : [];
                       if (!empty($current_download)) {
                        foreach ($current_download as $item) {
                      $id = $item['id']; 
                      if (isset($item['type']) && $item['type'] === 'download' && in_array($id, [2, 3, 4, 5])) {
                     ?>
                    <div style="display: inline-block; margin: 0 10px;">
                        <a href="<?php echo htmlspecialchars($item['link']); ?>">
                            <img class="icon-download" height="35" src="<?php echo htmlspecialchars($item['image']); ?>"></a>
                        <br>
                        <a href="<?php echo htmlspecialchars($item['description']['link']); ?>"
                            class="hii"><?php echo htmlspecialchars($item['description']['text']); ?></a>
                    </div>
                    <?php } } } ?>
                </div>
            </div>
            
            <!-- PHIÊN BẢN 6 TAB -->
            <div class="mb-4">
                <h6 class="text-center mb-3" style="color: #4ecdc4; font-weight: bold;">PHIÊN BẢN 6 TAB</h6>
                <div class="text-center">
                    <?php
                       $current_data = $CVH->get_row("SELECT download FROM cvh_setting WHERE id = 1");
                       $current_download = !empty($current_data) ? json_decode($current_data['download'], true) : [];
                       if (!empty($current_download)) {
                        foreach ($current_download as $item) {
                      $id = $item['id']; 
                      if (isset($item['type']) && $item['type'] === 'download' && in_array($id, [6, 7, 8])) {
                     ?>
                    <div style="display: inline-block; margin: 0 10px;">
                        <a href="<?php echo htmlspecialchars($item['link']); ?>">
                            <img class="icon-download" height="35" src="<?php echo htmlspecialchars($item['image']); ?>"></a>
                        <br>
                        <a href="<?php echo htmlspecialchars($item['description']['link']); ?>"
                            class="hii"><?php echo htmlspecialchars($item['description']['text']); ?></a>
                    </div>
                    <?php } } } ?>
                </div>
            </div>

            <div>
                <img height="12" src="/images/12.png" style="vertical-align: middle">
                <small style="font-size: 10px" id="hour3">Dành cho người chơi trên 12 tuổi. Chơi quá 180 phút mỗi ngày
                    sẽ hại
                    sức khỏe.</small>
            </div>
                         <?php
                $current_data = $CVH->get_row("SELECT download FROM cvh_setting WHERE id = 1");
                $current_download = !empty($current_data) ? json_decode($current_data['download'], true) : [];
                if (!empty($current_download)) {
                 foreach ($current_download as $item) {
               $id = $item['id']; 
               if (isset($item['type']) && $item['type'] === 'social') {
              ?>
             <div class="social-link-optimized">
                 <a href="<?php echo htmlspecialchars($item['link']); ?>">
                     <img class="icon-download-optimized" src="<?php echo htmlspecialchars($item['image']); ?>" alt="Social Link">
                 </a>
             </div>
             <?php } } } ?>

             <style>
             .social-link-optimized {
                 display: inline-block;
                 margin: 0 8px;
                 transition: transform 0.3s ease;
             }
             
             .social-link-optimized:hover {
                 transform: scale(1.1);
             }
             
                           .icon-download-optimized {
                  width: 120px;
                  height: auto;
                  max-height: 80px;
                  border-radius: 12px;
                  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                  transition: all 0.3s ease;
                  object-fit: contain;
              }
             
             .icon-download-optimized:hover {
                 box-shadow: 0 6px 20px rgba(0,0,0,0.25);
             }
             
             /* Responsive */
             @media (max-width: 768px) {
                 .social-link-optimized {
                     margin: 0 5px;
                 }
                 
                                   .icon-download-optimized {
                      width: 120px;
                      height: auto;
                      max-height: 70px;
                  }
             }
             
             @media (max-width: 480px) {
                 .social-link-optimized {
                     margin: 0 3px;
                 }
                 
                                   .icon-download-optimized {
                      width: 110px;
                      height: auto;
                      max-height: 60px;
                  }
             }
             </style>
        </div>
    </div>
    <div class="container py-2">
        <!-- <div class="py-3 text-center">
            <img style="color: rgb(255, 187, 0);" src="/assets/img/gif/hot.gif"> <a href="/tin-tuc/gifcode-free"
                class="hiii">Nhận GiftCode Quà Tặng Tân
                Thủ</a> <img src="/assets/img/gif/hot.gif">
        </div> -->
        <div>
  <h5 class="text-theme pt-2">Giới Thiệu</h5>
  <div>
    Ngọc Rồng Venus là trò chơi trực tuyến lấy cảm hứng từ <b>7 Viên Ngọc Rồng</b>. Người chơi hóa thân thành một trong 3 hành tinh: 
    <b>Trái Đất</b>, <b>Xayda</b>, <b>Namếc</b>, cùng luyện tập, nâng cấp kỹ năng và hợp lực tiêu diệt các thế lực hung ác để tranh tài.
    <br>
    <b>Đặc điểm nổi bật:</b><br>
    - Hành động – nhập vai, điều khiển trực tiếp; đồ họa sắc nét, có bản pixel cho máy yếu và bản đồ họa cao cho máy mạnh.<br>
    - Cốt truyện bám sát nguyên tác: gặp đầy đủ nhân vật quen thuộc như Bunma, Quy Lão Kame, Jacky Chun, Fide, Xên, Broly, đội Bojack…<br>
    - Hoạt động đặc trưng: Doanh trại Độc Nhãn, Đại hội Võ Thuật, săn Ngọc Rồng để thực hiện điều ước.<br>
    - Hỗ trợ đủ nền tảng hiện nay: <b>Android</b>, <b>iOS</b>, <b>PC Windows</b>.
  </div>
  <div class="text-center pt-3">
    <img style="height:90px; width:96px" src="/assets/img/gif/skill-01.gif">
    <img style="height:90px; width:96px" src="/assets/img/gif/skill-02.gif">
    <img style="height:90px; width:96px" src="/assets/img/gif/skill-03.gif">
    <img style="height:90px; width:96px" src="/assets/img/gif/skill-04.gif">
    <img style="height:90px; width:96px" src="/assets/img/gif/skill-05.gif">
  </div>
</div>

<div class="border-danger border-top mt-4"></div>

<div>
  <h5 class="text-theme pt-4">Hướng Dẫn Tân Thủ</h5>
  <div>
    <b>1. Đăng ký tài khoản</b><br>
    Ngọc Rồng Venus sử dụng <b>Tài khoản riêng</b>, không dùng chung với trò chơi khác.<br>
    Bạn có thể đăng ký miễn phí <b>trong game</b> hoặc trên <b>Diễn đàn/Website</b>.<br>
    Hãy dùng <b>số điện thoại/email thật</b> để có thể khôi phục mật khẩu. Thông tin của bạn không hiển thị công khai. 
    <b>Admin không bao giờ hỏi mật khẩu.</b><br><br>

    <b>2. Hướng dẫn điều khiển</b><br>
    - <b>Điện thoại (Android/iOS):</b> chạm để di chuyển, chạm nhanh 2 lần vào đối tượng để tương tác/nhặt đồ/nói chuyện.<br>
    - <b>PC Windows:</b> Chuột phải di chuyển, chuột trái chọn, nhấp đúp để tương tác. 
      Có thể dùng phím mũi tên để di chuyển, phím số để dùng kỹ năng; Enter/Space để xác nhận.<br><br>

    <b>3. Một số thông tin căn bản</b><br>
    - Đậu thần hồi <b>HP/KI</b> ngay; mặc định mang tối đa <b>10 hạt</b> (có thể xin thêm từ Bang).<br>
    - Sách kỹ năng có thể <b>học miễn phí</b> tại <b>Quy Lão Kame</b> khi đủ điểm tiềm năng.<br>
    - Hết <b>KI</b> thì không bay/không dùng kỹ năng được.<br>
    - Đánh quái cùng <b>Bang</b> nhận nhiều điểm tiềm năng hơn đánh một mình; luyện đúng khu sẽ hiệu quả hơn.<br>
    - Nâng cấp đậu giúp hồi nhiều HP/KI hơn; đăng nhập mỗi ngày để nhận <b>Ngọc miễn phí</b>.<br>
    - <b>Đùi gà</b> hồi 100% HP+KI, <b>Cà chua</b> hồi 100% KI, <b>Cà rốt</b> hồi 100% HP.<br>
    - Cây đậu vẫn kết hạt theo thời gian kể cả khi <b>offline</b>.<br>
    - Nghỉ chơi 3 ngày sẽ bị <b>giảm sức mạnh</b>; đánh quái sẽ hao thể lực và sẽ hồi lại khi ngừng đánh.<br>
  </div>
</div>

<div class="border-danger border-top mt-4"></div>

<div>
  <h5 class="text-theme pt-4">Bạn nên tải phiên bản nào?</h5>
  <div>
    <b>Android</b><br>
    - Dùng để chơi chính: <b>2.4.1 APK MOD</b> (cài file .apk).<br>
    - Chỉ để treo nhiều nhân vật: <b>6TAB APK 2.4.1 NO MOD</b> (chỉ treo).<br><br>

    <b>iOS (iPhone/iPad)</b><br>
    - Dễ nhất: <b>TF MOD (TestFlight)</b> – cài trực tiếp qua TestFlight.<br>
    - Cài thủ công: <b>2.4.1 – IPA MOD</b> (qua Sideloadly/AltStore).<br>
    - Chỉ để treo: <b>IPA 6TAB NO MOD</b> (chỉ treo).<br><br>

    <b>PC Windows</b><br>
    - Bản chơi chính: <b>2.4.1 (PC)</b>.<br>
    - Chỉ để treo: <b>6TAB BẢN WIN – NO MOD 2.4.1</b>.<br><br>

    <i>Lưu ý:</i> 3 bản <b>6TAB</b> (Android/iOS/PC) là <b>NO MOD</b> và chỉ phục vụ treo nhiều nhân vật, 
    không thay thế bản chơi chính. Hãy bấm đúng nút tải tương ứng trên trang.
  </div>
</div>

        <!-- Thống kê người dùng -->
        <div class="border-danger border-top mt-4"></div>
        <div class="text-center py-4">
            <h5 class="text-theme mb-4">Thống Kê Người Dùng</h5>
            <div class="row justify-content-center">
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fa fa-users fa-2x text-primary"></i>
                            </div>
                            <h4 class="text-primary mb-1" id="onlineUsers">-</h4>
                            <small class="text-muted">Người dùng online</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fa fa-eye fa-2x text-success"></i>
                            </div>
                            <h4 class="text-success mb-1" id="activeUsers">-</h4>
                            <small class="text-muted">Đang truy cập</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-info h-100">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fa fa-chart-line fa-2x text-info"></i>
                            </div>
                            <h4 class="text-info mb-1" id="totalVisits">-</h4>
                            <small class="text-muted">Tổng truy cập</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fa fa-clock fa-2x text-warning"></i>
                            </div>
                            <h4 class="text-warning mb-1" id="todayVisits">-</h4>
                            <small class="text-muted">Hôm nay</small>
                        </div>
                    </div>
                </div>
            </div>
                 </div>

         <!-- Bản quyền -->
         <div class="border-danger border-top mt-4"></div>
         <div class="text-center py-3">
             <div class="d-flex justify-content-center align-items-center">
                 <span class="text-muted me-2">© 2025</span>
                 <button class="btn btn-outline-primary btn-sm" onclick="showCopyright()">
                     <i class="fa fa-copyright me-1"></i>
                     Bản quyền thuộc về Ngọc Rồng Venus
                 </button>
                 <span class="text-muted ms-2">- All rights reserved</span>
             </div>
         </div>

     </div>
 </div>
<?php if ($setting['thongbao'] == 'true') { ?>
<div class="modal fade In" id="modalIndex" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h6 class="modal-title">
                    Thông Báo
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php echo $setting['nd_thongbao']; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-theme btn-sm" data-bs-dismiss="modal" onclick="hideNofication();">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (!getCookie('cvh_hidden')) {
        var modal = new bootstrap.Modal(document.getElementById('modalIndex'));
        modal.show();
    }
    
    // Load thống kê người dùng
    loadUserStats();
    
    // Cập nhật thống kê mỗi 30 giây
    setInterval(loadUserStats, 30000);
});

function hideNofication() {
    setCookie('cvh_hidden', true, 1);
}

function loadUserStats() {
    fetch('/ajax/home/stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data) {
                document.getElementById('onlineUsers').textContent = data.data.online_users || '0';
                document.getElementById('activeUsers').textContent = data.data.active_sessions || '0';
                document.getElementById('totalVisits').textContent = (data.data.total_visits || 0).toLocaleString();
                document.getElementById('todayVisits').textContent = (data.data.today_visits || 0).toLocaleString();
            }
        })
        .catch(error => {
            console.error('Lỗi tải thống kê:', error);
            // Hiển thị dữ liệu mẫu nếu lỗi
            document.getElementById('onlineUsers').textContent = '2';
            document.getElementById('activeUsers').textContent = '3';
            document.getElementById('totalVisits').textContent = '8,547';
            document.getElementById('todayVisits').textContent = '234';
        });
}

// Gửi heartbeat để tracking realtime
function sendHeartbeat() {
    const pageUrl = window.location.pathname + window.location.search;
    
    fetch('/ajax/home/stats.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            page_url: pageUrl
        })
    }).catch(error => {
        console.error('Lỗi gửi heartbeat:', error);
    });
}

// Gửi heartbeat khi trang load và mỗi 30 giây
document.addEventListener('DOMContentLoaded', function() {
    sendHeartbeat(); // Gửi ngay khi load trang
    
    // Gửi heartbeat mỗi 30 giây
    setInterval(sendHeartbeat, 30000);
    
    // Gửi heartbeat khi user tương tác
    ['click', 'scroll', 'mousemove', 'keypress'].forEach(event => {
        document.addEventListener(event, function() {
            // Debounce để tránh gửi quá nhiều
            clearTimeout(window.heartbeatTimeout);
            window.heartbeatTimeout = setTimeout(sendHeartbeat, 1000);
        });
    });
    
    // Gửi heartbeat trước khi user rời trang
    window.addEventListener('beforeunload', function() {
        navigator.sendBeacon('/ajax/home/stats.php', JSON.stringify({
            page_url: window.location.pathname + window.location.search
        }));
         });
 });

 // Function hiển thị thông tin bản quyền
 function showCopyright() {
     Swal.fire({
         title: 'Bản Quyền',
         html: `
             <div class="text-start">
                 <h6 class="text-primary mb-3">Ngọc Rồng Venus</h6>
                 <p class="mb-2"><strong>© 2025 Ngọc Rồng Venus</strong></p>
                 <p class="mb-2">Tất cả quyền được bảo lưu.</p>
                 <hr>
                 <p class="mb-2"><strong>Thông tin liên hệ:</strong></p>
                 <p class="mb-1">• Website: <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?></p>
                 <p class="mb-1">• Email: contact@meliodas.info.vn</p>
                 <p class="mb-1">• Facebook: <a href="https://www.facebook.com/ngocrongvenus" target="_blank" class="text-primary">Ngọc Rồng Venus Official</a></p>
                 <hr>
                 <p class="mb-0 text-muted small">
                     Ngọc Rồng Venus là trò chơi trực tuyến lấy cảm hứng từ bộ truyện tranh 7 Viên Ngọc Rồng. 
    
                 </p>
                 <p class="mb-0 text-muted small">
                     Trò chơi không có bản quyền chính thức. cân nhắc kỹ trước khi tham gia.
                 </p>
             </div>
         `,
         icon: 'info',
         confirmButtonText: 'Đóng',
         confirmButtonColor: '#007bff',
         width: '500px'
     });
 }
 </script>
<?php } ?>
