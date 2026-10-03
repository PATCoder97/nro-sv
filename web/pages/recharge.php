        <?php
        if (!$user) {
            echo '<div class="alert alert-danger m-3">Vui lòng đăng nhập để sử dụng chức năng nạp tiền.</div>';
            return;
        }
        ?>
        <?php
        $kmess = 5; // Số phim hiện trong mỗi page
        $page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
        $start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
        $result = mysqli_query($CVH->connect_db(), "SELECT cvh_recharge.* FROM cvh_recharge INNER JOIN account ON cvh_recharge.account_id = account.id WHERE cvh_recharge.account_id = '".$user['id']."' ORDER BY id DESC LIMIT $start, $kmess");
        $tong = mysqli_num_rows(mysqli_query($CVH->connect_db(), "SELECT cvh_recharge.* FROM cvh_recharge WHERE cvh_recharge.account_id = '".$user['id']."' ORDER BY id DESC"));
        $activeTab = (isset($_GET['tab']) && $_GET['tab'] === 'bank') ? 'bank' : 'card';
        ?>
        <div id="napthe" class="mb-5">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-3 justify-content-center recharge-tabs" id="rechargeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab==='card'?'active':''; ?>" id="card-tab" onclick="showTab('card')" type="button">
                        <i class="fas fa-credit-card me-2"></i>Nạp Thẻ
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab==='bank'?'active':''; ?>" id="bank-tab" onclick="showTab('bank')" type="button">
                        <i class="fas fa-university me-2"></i>Nạp Tiền Tự Động
                    </button>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content" id="rechargeTabContent">
                <!-- Tab 1: Nạp Thẻ -->
                <div class="tab-pane fade <?php echo $activeTab==='card'?'show active':''; ?>" id="card-recharge" role="tabpanel" style="<?php echo $activeTab==='card'?'display:block;':''; ?>">
                    <div class="card">
                        <div class="card-body pb-2 py-5">
                            <form cvhvn="true" method="POST" action="/ajax/users/recharge.php"
                                href="<?php echo FULL_URL('/nap-tien'); ?>">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="form-group mb-2">
                                            <select class="form-select" name="type" id="loaithe">
                                                <option>--Chọn loại thẻ--</option>
                                                <option value="VIETTEL">Viettel</option>
                                                <option value="VINAPHONE">Vinaphone</option>
                                                <option value="MOBIFONE">Mobifone</option>
                                                <option value="VNMOBI">Vietnamobile</option>
                                                <option value="ZING">Zing</option>
                                                <option value="GATE">Gate</option>
                                                <option value="GARENA">Garena</option>
                                                <option value="VCOIN">Vcoin (VTC)</option>
                                                <option value="ZINGCHAM">Zing (Chậm)</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <select class="form-select" name="amount" id="menhgia">
                                                <option>--Chọn mệnh giá--</option>
                                                <option value="10000">10.000đ</option>
                                                <option value="20000">20.000đ</option>
                                                <option value="30000">30.000đ</option>
                                                <option value="50000">50.000đ</option>
                                                <option value="100000">100.000đ</option>
                                                <option value="200000">200.000đ</option>
                                                <option value="300000">300.000đ</option>
                                                <option value="500000">500.000đ</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="number" class="form-control" name="code" id="mathe" placeholder="Nhập mã thẻ">
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="number" class="form-control" name="serial" id="seri" placeholder="Nhập serial">
                                        </div>
                                        <div class="form-group mb-2">
                                            <button type="submit" href="<?php echo FULL_URL('/nap-tien'); ?>" id="submit"
                                                class="btn btn-theme btn-md d-block w-100 fw-500 mb-3">Gửi
                                                Ngay</button>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div>
                                            <p>Nạp thẻ <b>10.000đ = 10.000đ</b>.</p>
                                            <b>Nếu thẻ duyệt chậm hơn 3p vui lòng liên hệ ADMIN để được xử lý trong thời gian sớm
                                                nhất.</b>
                                            <hr />
                                            <p class="mb-0"><b>Lưu ý:</b> Thẻ sai mệnh giá sẽ bị mất 100% giá trị của thẻ.</p>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="pb-2 m-3">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Serial/Mã Thẻ</th>
                                        <th scope="col">Loại Thẻ/Mệnh Giá</th>
                                        <th scope="col">Thời Gian/Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $i = 1;
                                        if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <th scope="row"><?php echo $row['serial']; ?> / <?php echo $row['code']; ?></th>
                                        <th scope="row"><?php echo $row['type']; ?> / <?php echo number_format($row['amount']); ?>đ</th>
                                        <th scope="row"><?php echo $row['time']; ?> / <?php echo getStatus($row['status']); ?></th>
                                    </tr>
                                    <?php }
                                        } else { ?>
                                    <tr class="text-center">
                                        <td colspan='3'>
                                            <img src="https://cdn-icons-png.flaticon.com/128/7466/7466139.png" width="50"
                                                class="img-fluid">
                                            <p class="pt-3"><b>Không có dữ liệu</b></p>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="d-flex align-items-center justify-content-center py-3">
                                <?php
                            if ($tong > $kmess) {
                                echo '' . $CVH->phantrang('/nap-tien?', $start, $tong, $kmess) . '';
                            }
                            ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab 2: Nạp Tiền Tự Động -->
                <div class="tab-pane fade <?php echo $activeTab==='bank'?'show active':''; ?>" id="bank-recharge" role="tabpanel" style="<?php echo $activeTab==='bank'?'display:block;':'display:none;'; ?>">
                    <div class="card">
                        <div class="card-body pb-2 py-5">
                                                <!-- QR Code và Hướng dẫn -->
                            <div class="row">
                                <div class="col-xl-6">
                                    <!-- QR Code Section -->
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-qrcode me-2"></i>
                                                Quét mã QR để chuyển khoản
                                            </label>
                                        </div>
                                        <div class="qr-code-container">
                                            <img src="https://files.catbox.moe/vkg7s5.png" 
                                                alt="QR Code Chuyển Khoản" 
                                                class="img-fluid rounded shadow-sm"
                                                style="max-width: 250px; border: 2px solid #e9ecef;">
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-mobile-alt me-1"></i>
                                                Quét mã QR bằng ứng dụng ngân hàng
                                            </small>
                                        </div>
                                        <div class="mt-2">
                                            <button class="btn btn-outline-primary btn-sm" onclick="downloadQR()">
                                                <i class="fas fa-download me-1"></i>
                                                Tải QR Code
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Hướng dẫn nạp tiền tự động:</h6>
                                                                            <ol class="mb-0">
                                            <li>Quét mã QR bằng ứng dụng ngân hàng</li>
                                            <li>Nhập số tiền muốn nạp</li>
                                            <li>Nhập nội dung chuyển khoản: <strong><span id="paymentCodeExample" style="color: #28a745; font-weight: bold;">
                                            <?php
                                                $code = '';
                                                try {
                                                    $connTmp = $CVH->connect_db();
                                                    $connTmp->query("CREATE TABLE IF NOT EXISTS `bank_payment_codes` (
                                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                                        `user_id` int(11) NOT NULL,
                                                        `code` varchar(50) NOT NULL,
                                                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                                        PRIMARY KEY (`id`),
                                                        UNIQUE KEY `uq_user` (`user_id`),
                                                        UNIQUE KEY `uq_code` (`code`)
                                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                                                    $uid = intval($user['id']);
                                                    $qr = $connTmp->query("SELECT code FROM bank_payment_codes WHERE user_id = {$uid} LIMIT 1");
                                                    if ($qr && $qr->num_rows === 1) {
                                                        $code = $qr->fetch_assoc()['code'];
                                                    }
                                                } catch (Exception $e) { }
                                                echo htmlspecialchars($code);
                                            ?>
                                            </span></strong></li>
                                            <li>Xác nhận chuyển khoản</li>
                                            <li>Hệ thống tự động xử lý và cộng tiền</li>
                                        </ol>
                                    </div>
                                                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Lưu ý:</strong> Vui lòng chuyển khoản chính xác nội dung để hệ thống tự động xử lý!
                                    </div>
                                    <div class="alert alert-primary">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Mã thanh toán:</strong> Được tạo động theo tài khoản của bạn. nếu chuyển sai sẽ không cộng tiền.
                                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyPaymentCode()">
                                            <i class="fas fa-copy me-1"></i>Copy mã
                                        </button>
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <!-- Thông tin chuyển khoản (hiển thị sau khi tạo giao dịch) -->
                            <div id="bankTransferInfo" class="mt-4" style="display: none;">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Thông Tin Chuyển Khoản
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Ngân hàng:</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="bank_name" value="Vietcombank" readonly>
                                                        <button class="btn btn-outline-secondary copy-btn" onclick="copyToClipboard('Vietcombank')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Số tài khoản:</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="account_number" value="0071000888888" readonly>
                                                        <button class="btn btn-outline-secondary copy-btn" onclick="copyToClipboard('0071000888888')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Tên tài khoản:</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="account_name" value="CONG TY TNHH SEPAY" readonly>
                                                        <button class="btn btn-outline-secondary copy-btn" onclick="copyToClipboard('CONG TY TNHH SEPAY')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Số tiền:</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="transfer_amount" readonly>
                                                        <button class="btn btn-outline-secondary copy-btn" onclick="copyToClipboard(document.getElementById('transfer_amount').value)">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nội dung chuyển khoản:</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="transfer_content" readonly>
                                                        <button class="btn btn-outline-secondary copy-btn" onclick="copyToClipboard(document.getElementById('transfer_content').value)">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mt-4">
                                                <!-- QR Code Section -->
                                                <div class="text-center">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">
                                                            <i class="fas fa-qrcode me-2"></i>
                                                            Quét mã QR để chuyển khoản
                                                        </label>
                                                    </div>
                                                    <div class="qr-code-container">
                                                        <img src="https://files.catbox.moe/vkg7s5.png" 
                                                            alt="QR Code Chuyển Khoản" 
                                                            class="img-fluid rounded shadow-sm"
                                                            style="max-width: 250px; border: 2px solid #e9ecef;">
                                                    </div>
                                                    <div class="mt-3">
                                                        <small class="text-muted">
                                                            <i class="fas fa-mobile-alt me-1"></i>
                                                            Quét mã QR bằng ứng dụng ngân hàng
                                                        </small>
                                                    </div>
                                                    <div class="mt-2">
                                                        <button class="btn btn-outline-primary btn-sm" onclick="downloadQR()">
                                                            <i class="fas fa-download me-1"></i>
                                                            Tải QR Code
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Lịch sử nạp tự động (SePay) + phân trang
        $history_rows = [];
        $perPage = 3;
        $pageBank = isset($_GET['page_bank']) && intval($_GET['page_bank']) > 0 ? intval($_GET['page_bank']) : 1;
        $offsetBank = ($pageBank - 1) * $perPage;
        $totalHist = 0;
        try {
            $connHistory = $CVH->connect_db();
            $userIdHist = intval($user['id']);
            $userCodeHist = '';
            $qrCode = $connHistory->query("SELECT code FROM bank_payment_codes WHERE user_id = {$userIdHist} LIMIT 1");
            if ($qrCode && $qrCode->num_rows === 1) {
                $userCodeHist = $qrCode->fetch_assoc()['code'];
            }
            if ($userCodeHist !== '') {
                // Đếm tổng
                $cntStmt = $connHistory->prepare("SELECT COUNT(*) AS cnt FROM tb_transactions WHERE LOWER(code) = LOWER(?) OR LOWER(transaction_content) LIKE CONCAT('%', LOWER(?), '%') OR LOWER(body) LIKE CONCAT('%', LOWER(?), '%')");
                if ($cntStmt) {
                    $cntStmt->bind_param("sss", $userCodeHist, $userCodeHist, $userCodeHist);
                    $cntStmt->execute();
                    $cntRes = $cntStmt->get_result();
                    if ($cntRes && $r = $cntRes->fetch_assoc()) { $totalHist = intval($r['cnt']); }
                    $cntStmt->close();
                }

                // Lấy trang hiện tại
                $stmtHist = $connHistory->prepare("SELECT gateway, amount_in, transaction_date, credited FROM tb_transactions WHERE LOWER(code) = LOWER(?) OR LOWER(transaction_content) LIKE CONCAT('%', LOWER(?), '%') OR LOWER(body) LIKE CONCAT('%', LOWER(?), '%') ORDER BY id DESC LIMIT ? OFFSET ?");
                if ($stmtHist) {
                    $stmtHist->bind_param("sssii", $userCodeHist, $userCodeHist, $userCodeHist, $perPage, $offsetBank);
                    $stmtHist->execute();
                    $resHist = $stmtHist->get_result();
                    while ($resHist && $row = $resHist->fetch_assoc()) {
                        $history_rows[] = $row;
                    }
                    $stmtHist->close();
                }
            }
        } catch (Exception $e) {
            // ignore
        }
        $pages = $perPage > 0 ? ceil(max(0, $totalHist) / $perPage) : 0;
        ?>

        <div class="card mt-4 mb-5 tab-pane fade" id="bank-history" role="tabpanel" style="<?php echo $activeTab==='bank'?'display:block;':'display:none;'; ?>">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-history me-2"></i>Lịch sử nạp tự động
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px">STT</th>
                                <th>Ngân hàng</th>
                                <th style="width:180px">Số tiền</th>
                                <th style="width:260px">Thời gian/Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history_rows)) { $i=1; foreach ($history_rows as $h) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($h['gateway']); ?></td>
                                <td><?php echo number_format((float)$h['amount_in']); ?>đ</td>
                                <td>
                                    <?php echo !empty($h['transaction_date']) ? htmlspecialchars($h['transaction_date']) : date('Y-m-d H:i:s'); ?>
                                    /
                                    <?php echo intval($h['credited']) === 1 ? '<span class="badge bg-success">Thành công</span>' : '<span class="badge bg-secondary">Chưa xử lý</span>'; ?>
                                </td>
                            </tr>
                            <?php } } else { ?>
                            <tr>
                                <td colspan="4" class="text-center">Chưa có giao dịch</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($pages > 1) { ?>
                <nav class="d-flex justify-content-center p-3">
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($p = 1; $p <= $pages; $p++) { $active = $p === $pageBank ? 'active' : ''; ?>
                        <li class="page-item <?php echo $active; ?>">
                            <a class="page-link" href="/nap-tien?tab=bank&page_bank=<?php echo $p; ?>#bank-history"><?php echo $p; ?></a>
                        </li>
                        <?php } ?>
                    </ul>
                </nav>
                <?php } ?>
            </div>
        </div>

        <script>
        // Đợi DOM load xong
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, checking elements...');
            // Clear placeholder in payment code example
            var exampleEl = document.getElementById('paymentCodeExample');
            if (exampleEl) { exampleEl.textContent = ''; }


            // Function copy to clipboard
            window.copyToClipboard = function(text) {
                navigator.clipboard.writeText(text).then(function() {
                    // Hiển thị thông báo
                    const btn = event.target.closest('.copy-btn');
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-success');
                    
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                    }, 1000);
                });
            }

            // Function hiển thị alert
            window.showAlert = function(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="fas ${icon} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                // Thêm alert vào đầu tab content
                const tabContent = document.getElementById('bank-recharge');
                if (tabContent) {
                    tabContent.insertAdjacentHTML('afterbegin', alertHtml);
                    
                    // Tự động ẩn alert sau 5 giây
                    setTimeout(() => {
                        const alert = tabContent.querySelector('.alert');
                        if (alert) {
                            alert.remove();
                        }
                    }, 5000);
                }
            }

            // Debug: Kiểm tra tab navigation
            console.log('Checking tab navigation...');
            const cardTab = document.getElementById('card-tab');
            const bankTab = document.getElementById('bank-tab');
            const cardRecharge = document.getElementById('card-recharge');
            const bankRecharge = document.getElementById('bank-recharge');

            if (cardTab) console.log('card-tab found');
            if (bankTab) console.log('bank-tab found');
            if (cardRecharge) console.log('card-recharge found');
            if (bankRecharge) console.log('bank-recharge found');

            // ĐÃ TẮT auto refresh để tránh tự load lại khi ở tab nạp tiền tự động

                // Function tải QR code
        window.downloadQR = function() {
            const link = document.createElement('a');
            link.href = 'https://files.catbox.moe/vkg7s5.png';
            link.download = 'qr-code-chuyen-khoan.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showAlert('success', 'Đang tải QR Code...');
        }
        
        // Lấy mã thanh toán cố định theo tài khoản và hiển thị
        function loadPaymentCodeStructure() {
            fetch('/ajax/users/get-payment-code.php', { method: 'GET' })
            .then(r => r.json())
            .then(data => {
                if (data && data.success && data.code) {
                    const code = data.code;
                    const exampleElement = document.getElementById('paymentCodeExample');
                    if (exampleElement) exampleElement.textContent = code;
                    window.currentPaymentCode = code;
                    // Nếu có input nội dung, điền sẵn
                    const transferContent = document.getElementById('transfer_content');
                    if (transferContent) transferContent.value = code;
                    // Scroll vào khu vực hướng dẫn để thấy rõ mã
                    const bankTab = document.getElementById('bank-recharge');
                    if (bankTab) bankTab.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    console.error('Cannot get payment code:', data);
                }
            })
            .catch(err => console.error('Error getting payment code:', err));
        }
        
        // Không còn tạo giao dịch tự động ở frontend; mã là cố định theo tài khoản
        
        // Load payment code structure on page load
        loadPaymentCodeStructure();
        
        // Function copy payment code (popup toastr)
        window.copyPaymentCode = function() {
            var code = window.currentPaymentCode || (document.getElementById('paymentCodeExample') ? document.getElementById('paymentCodeExample').textContent.trim() : '');
            if (code) {
                navigator.clipboard.writeText(code).then(function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.options.closeButton = true;
                        toastr.options.timeOut = 1500;
                        toastr.success('Đã copy mã thanh toán thành công!', 'Thông báo');
                    } else {
                        showAlert('success', 'Đã copy mã thanh toán vào clipboard!');
                    }
                });
            }
        }

        // Giữ nguyên tab 'Nạp Tiền Tự Động' khi phân trang
        (function ensureBankTabFromUrl(){
            try {
                const params = new URLSearchParams(window.location.search);
                const tab = params.get('tab');
                if (tab === 'bank' || window.location.hash === '#bank-history') {
                    // Mở tab bank và cuộn đến lịch sử nếu có
                    showTab('bank');
                    const hist = document.getElementById('bank-history');
                    if (hist) hist.scrollIntoView({behavior:'smooth', block:'start'});
                }
            } catch (_) {}
        })();
        });
        </script>