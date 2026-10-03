<div id="content" class="app-content">
    <style>
    .kpi-card { border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.06); transition: transform .18s ease, box-shadow .18s ease; }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.10); }
    .kpi-value { font-size: 32px; font-weight: 700; letter-spacing: .2px; }
    .kpi-sub { color:#6c757d; font-size: 13px; }
    .kpi-badge-up { background:#e6f4ea;color:#137333;padding:2px 8px;border-radius:999px;font-size:12px; }
    .kpi-badge-down { background:#fde8e8;color:#b42318;padding:2px 8px;border-radius:999px;font-size:12px; }
    .skeleton { background: linear-gradient(90deg, rgba(0,0,0,.06) 25%, rgba(0,0,0,.1) 37%, rgba(0,0,0,.06) 63%); background-size: 400% 100%; animation: shine 1.2s ease-in-out infinite; border-radius:8px; height: 18px; }
    @keyframes shine { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
    .filter-wrap { display:flex; gap:8px; align-items:center; justify-content:flex-end; margin-bottom:12px; }
    </style>
    <div class="row">

        <div class="col-xl-12">
            <div class="row">

                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" id="kpi-acc">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Tổng Tài Khoản</h5>
                                    <div class="kpi-sub">Tài khoản đã đăng ký</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="kpi-value mb-1"><?php echo fNumber($CVH->count('account')); ?></div>
                                    <span class="kpi-sub">tài khoản</span>
                                </div>
                                <div
                                    class="w-50px h-50px bg-primary bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-user fa-lg text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" id="kpi-player">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Tổng nhân vật</h5>
                                    <div class="kpi-sub">Người dùng đã tạo nhân vật</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="kpi-value mb-1"><?php echo fNumber($CVH->count('player')); ?></div>
                                    <span class="kpi-sub">nhân vật</span>
                                </div>
                                <div
                                    class="w-50px h-50px bg-success bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-address-card fa-lg text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" id="kpi-revenue">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Tổng doanh thu</h5>
                                    <div class="kpi-sub">Tổng doanh thu nạp thẻ</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="kpi-value mb-1"><?php echo number_format($CVH->tongdoanhthu()); ?>đ</div>
                                </div>
                                <div
                                    class="w-50px h-50px bg-success bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-dollar fa-lg text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" id="kpi-active">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Đã kích hoạt</h5>
                                    <div>Người dùng đã kích hoạt</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="kpi-value mb-1"><?php echo fNumber($CVH->count('account', 'active = 1')); ?></div>
                                    <span class="kpi-sub">tài khoản</span>
                                </div>
                                <div
                                    class="w-50px h-50px bg-success bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-check-circle fa-lg text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" id="kpi-today-acc">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Tài khoản hôm nay</h5>
                                    <div>Tài khoản đăng ký hôm nay</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="kpi-value mb-1"><?php echo fNumber($CVH->TKhomnay()); ?></div>
                                    <span class="kpi-sub">tài khoản</span>
                                </div>
                                <div
                                    class="w-50px h-50px bg-success bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-user-plus fa-lg text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" id="kpi-today-revenue">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Doanh thu hôm nay</h5>
                                    <div>Doanh thu ngày hôm nay</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="kpi-value mb-1"><?php echo number_format($CVH->DThomnay()); ?>đ</div>
                                </div>
                                <div
                                    class="w-50px h-50px bg-success bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-info-circle fa-lg text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Quản lý phân quyền -->
                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1" style="color: white;">Quản lý phân quyền</h5>
                                    <div style="color: rgba(255,255,255,0.8);">Hệ thống phân quyền admin</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <?php if ($user['is_super_admin'] ?? false): ?>
                                    <div class="mb-2">
                                        <a href="/admin?request=admin-permissions" class="btn btn-light btn-sm">
                                            <i class="fa fa-users-cog"></i> Quản lý quyền
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($user['is_super_admin'] ?? false): ?>
                                    <div class="mb-2">
                                        <a href="/?request=setup-permissions" class="btn btn-outline-light btn-sm" target="_blank">
                                            <i class="fa fa-cog"></i> Setup hệ thống
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <div class="mb-2">
                                        <a href="/admin?request=my-permissions" class="btn btn-outline-light btn-sm">
                                            <i class="fa fa-user-shield"></i> Quyền của tôi
                                        </a>
                                    </div>
                                </div>
                                <div
                                    class="w-50px h-50px bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-shield-alt fa-lg text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Quản lý IP -->
                <?php if ($user['is_super_admin'] ?? false): ?>
                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white;">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1" style="color: white;">Quản lý IP</h5>
                                    <div style="color: rgba(255,255,255,0.8);">Ban/Unban IP & Thống kê tài khoản</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="mb-2">
                                        <a href="/admin?request=ip-manager" class="btn btn-light btn-sm">
                                            <i class="fa fa-shield-alt"></i> Quản lý IP
                                        </a>
                                    </div>
                                </div>
                                <div
                                    class="w-50px h-50px bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-network-wired fa-lg text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Card Quản lý VND -->
                <?php if ($user['is_super_admin'] ?? false): ?>
                <div class="col-sm-4 mb-3">
                    <div class="card mb-3 flex-1 kpi-card" style="background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); color: white;">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1" style="color: white;">Quản lý VND</h5>
                                    <div style="color: rgba(255,255,255,0.8);">Buff/Trừ/Sửa số dư tài khoản</div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="mb-2">
                                        <a href="/admin?request=vnd-manager" class="btn btn-light btn-sm">
                                            <i class="fa fa-coins"></i> Vào VND Manager
                                        </a>
                                    </div>
                                </div>
                                <div
                                    class="w-50px h-50px bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa fa-coins fa-lg text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                </div>
                
                <div class="filter-wrap">
                <div class="kpi-sub me-2">Thống kê:</div>
                <a href="/admin/?request=home&range=today" class="btn btn-sm btn-outline-primary">Hôm nay</a>
                <a href="/admin/?request=home&range=7d" class="btn btn-sm btn-outline-secondary">7 ngày</a>
                <a href="/admin/?request=home&range=30d" class="btn btn-sm btn-outline-secondary">30 ngày</a>
                <button id="refreshKpi" class="btn btn-sm btn-primary ms-2"><i class="fa fa-rotate"></i> Làm mới</button>
            </div>
            <script>
            // Auto refresh KPI nhẹ nhàng
            (function(){
                const btn = document.getElementById('refreshKpi');
                function fetchKpi(){
                    if(!window.fetch) return;
                    btn?.classList.add('disabled');
                    fetch('/ajax/home-kpi.php').then(r=>r.json()).then(function(d){
                        if(!d||!d.success) return;
                        const map = {
                            acc: d.data.total_accounts,
                            player: d.data.total_players,
                            revenue: d.data.total_revenue,
                            active: d.data.active_accounts,
                            today_acc: d.data.today_accounts,
                            today_rev: d.data.today_revenue
                        };
                        if(document.querySelector('#kpi-acc .kpi-value')) document.querySelector('#kpi-acc .kpi-value').textContent = map.acc;
                        if(document.querySelector('#kpi-player .kpi-value')) document.querySelector('#kpi-player .kpi-value').textContent = map.player;
                        if(document.querySelector('#kpi-revenue .kpi-value')) document.querySelector('#kpi-revenue .kpi-value').textContent = map.revenue + 'đ';
                        if(document.querySelector('#kpi-active .kpi-value')) document.querySelector('#kpi-active .kpi-value').textContent = map.active;
                        if(document.querySelector('#kpi-today-acc .kpi-value')) document.querySelector('#kpi-today-acc .kpi-value').textContent = map.today_acc;
                        if(document.querySelector('#kpi-today-revenue .kpi-value')) document.querySelector('#kpi-today-revenue .kpi-value').textContent = map.today_rev + 'đ';
                    }).finally(()=>btn?.classList.remove('disabled'));
                }
                btn?.addEventListener('click', fetchKpi);
                setInterval(fetchKpi, 60000);
            })();
            </script>
        </div>
    </div>
</div>