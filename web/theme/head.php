<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $setting['title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $setting['description']; ?>">
    <meta name="author" content="<?php echo $setting['author']; ?>">
    <meta name="keywords" content="<?php echo $setting['keywords']; ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo $setting['favicon']; ?>">
    <link href="/assets/css/vendor.min.css" rel="stylesheet">
    <link href="/assets/css/app.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="/assets/plugins/lity/dist/lity.min.css" rel="stylesheet">
    <style>
    .cvhvn-rounded-header {
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }

    .cvhvn-rounded-footer {
        border-top-left-radius: 0rem;
        border-top-right-radius: 0rem;
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
    }

    .cvh-margin {
        margin: 10px 4px;
    }

    ::-webkit-scrollbar {
        width: 3px;
        height: 3px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    @keyframes cvhRGB {
        0% {
            color: red;
        }

        33% {
            color: green;
        }

        66% {
            color: blue;
        }

        100% {
            color: red;
        }
    }

    a.hiii {
        text-decoration: none;
        animation: cvhRGB 1.5s infinite;
        font-size: 1.7em;
    }

    a.hii {
        text-decoration: none;
        font-size: 0.7em;
    }

    /* Custom Tab Styling for Recharge Page
    .tab-pane {
        display: none !important;
    }

    .tab-pane.active,
    .tab-pane.show.active {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    } */

    /* Gỡ override riêng cho #bank-recharge để tránh che menu header */

    /* Override Bootstrap fade */
    .tab-pane.fade {
        opacity: 1 !important;
        transition: none !important;
    }

    .tab-pane.fade.show {
        opacity: 1 !important;
    }
    /* Tùy biến tab nạp cho rõ hơn và căn giữa */
    .recharge-tabs .nav-link {
        font-weight: 600;
        font-size: 1.05rem;
        padding: 10px 20px;
    }
    .recharge-tabs .nav-link i { opacity: 0.9; }
    .recharge-tabs .nav-link.active {
        color: #0d6efd !important;
        border-bottom: 3px solid #0d6efd !important;
        background: #f8f9fa !important;
    }
    .recharge-tabs .nav-link:not(.active) {
        color: #6c757d !important;
    }
    </style>

         <script>
     // Anti-Debug: Cảnh báo khi mở Developer Tools (CHỈ ÁP DỤNG TRÊN DESKTOP)
     (function() {
         // Kiểm tra nếu là mobile/tablet thì bỏ qua anti-debug
         function isMobile() {
             return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                    window.innerWidth <= 768;
         }
         
         // Nếu là mobile thì không chạy anti-debug
         if (isMobile()) {
             console.log('Mobile device detected - Anti-debug disabled');
             return;
         }
        let devtools = {
            open: false,
            orientation: null
        };
        
        const threshold = 160;
        
        const emitEvent = (isOpen, orientation) => {
            window.dispatchEvent(new CustomEvent('devtoolschange', {
                detail: {
                    isOpen,
                    orientation
                }
            }));
        };
        
        setInterval(() => {
            const widthThreshold = window.outerWidth - window.innerWidth > threshold;
            const heightThreshold = window.outerHeight - window.innerHeight > threshold;
            const orientation = widthThreshold ? 'vertical' : 'horizontal';
            
            if (
                !(heightThreshold && widthThreshold) &&
                ((window.Firebug && window.Firebug.chrome && window.Firebug.chrome.isInitialized) || widthThreshold || heightThreshold)
            ) {
                if ((!devtools.open) || (devtools.orientation !== orientation)) {
                    emitEvent(true, orientation);
                }
                devtools.open = true;
                devtools.orientation = orientation;
            } else {
                if (devtools.open) {
                    emitEvent(false, null);
                }
                devtools.open = false;
                devtools.orientation = null;
            }
        }, 500);
        
        if (typeof module !== 'undefined' && module.exports) {
            module.exports = devtools;
        } else {
            window.devtools = devtools;
        }
    })();
    
                   // Thêm bảo vệ chống hack bổ sung (CHỈ ÁP DỤNG TRÊN DESKTOP)
      (function() {
          // Kiểm tra nếu là mobile/tablet thì bỏ qua anti-debug
          function isMobile() {
              return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                     window.innerWidth <= 768;
          }
          
          // Nếu là mobile thì không chạy anti-debug
          if (isMobile()) {
              console.log('Mobile device detected - Anti-debug disabled');
              return;
          }
         // Hàm hiển thị popup cảnh báo
         function showWarningPopup(message) {
             // Tạo popup container
             const popupContainer = document.createElement('div');
             popupContainer.id = 'warning-popup';
             popupContainer.style.cssText = `
                 position: fixed;
                 top: 0;
                 left: 0;
                 width: 100%;
                 height: 100%;
                 background: rgba(0, 0, 0, 0.8);
                 display: flex;
                 justify-content: center;
                 align-items: center;
                 z-index: 999999;
                 animation: fadeIn 0.3s ease;
             `;
             
             // Tạo popup content
             const popupContent = document.createElement('div');
             popupContent.style.cssText = `
                 background: linear-gradient(45deg, #ff0000, #ff6600);
                 color: white;
                 padding: 30px;
                 border-radius: 15px;
                 text-align: center;
                 max-width: 500px;
                 box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                 animation: slideIn 0.3s ease;
                 border: 3px solid #fff;
             `;
             
             popupContent.innerHTML = `
                 <h3 style="margin: 0 0 20px 0; font-size: 24px; font-weight: bold;">CẢNH BÁO</h3>
                 <p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.5;">${message}</p>
                 <p style="margin: 0 0 20px 0; font-size: 14px; opacity: 0.9;">Hệ thống đã ghi log hành động của bạn!</p>
                 <button onclick="closeWarningPopup()" style="
                     background: #fff;
                     color: #ff0000;
                     border: none;
                     padding: 12px 25px;
                     border-radius: 8px;
                     font-weight: bold;
                     cursor: pointer;
                     font-size: 16px;
                     transition: all 0.3s ease;
                 " onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#fff'">
                     ĐÓNG NGAY
                 </button>
             `;
             
             popupContainer.appendChild(popupContent);
             document.body.appendChild(popupContainer);
             
             // Tự động đóng sau 8 giây
             setTimeout(() => {
                 closeWarningPopup();
             }, 8000);
         }
         
         // Hàm đóng popup
         window.closeWarningPopup = function() {
             const popup = document.getElementById('warning-popup');
             if (popup) {
                 popup.style.animation = 'fadeOut 0.3s ease';
                 setTimeout(() => {
                     if (popup.parentNode) {
                         popup.parentNode.removeChild(popup);
                     }
                 }, 300);
             }
         };
         
         // Thêm CSS animation
         const style = document.createElement('style');
         style.textContent = `
             @keyframes fadeIn {
                 from { opacity: 0; }
                 to { opacity: 1; }
             }
             @keyframes fadeOut {
                 from { opacity: 1; }
                 to { opacity: 0; }
             }
             @keyframes slideIn {
                 from { transform: translateY(-50px); opacity: 0; }
                 to { transform: translateY(0); opacity: 1; }
             }
         `;
         document.head.appendChild(style);
         
         // Ngăn chặn right-click
         document.addEventListener('contextmenu', function(e) {
             e.preventDefault();
             console.log('%cThằng Ngu! Right-click bị vô hiệu hóa rồi!', 'color: #ff6600; font-size: 14px; font-weight: bold;');
             showWarningPopup('ĐỒ NGU! Right-click bị vô hiệu hóa rồi!<br><br>Bạn đang cố gắng hack website?<br>Hệ thống đã ghi log hành động của bạn!');
         });
         
                   // Ngăn chặn tất cả phím tắt Developer Tools (Windows và Mac)
          document.addEventListener('keydown', function(e) {
              // Windows: F12, Ctrl+Shift+I, Ctrl+U
              // Mac: Cmd+Option+I, Cmd+U, Cmd+Shift+I, Cmd+J, Cmd+Option+J, Cmd+Shift+J, Cmd+Option+C
              if (e.key === 'F12' || 
                  (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                  (e.ctrlKey && e.key === 'u') ||
                  (e.metaKey && e.altKey && e.key === 'I') ||
                  (e.metaKey && e.key === 'u') ||
                  (e.metaKey && e.shiftKey && e.key === 'I') ||
                  (e.metaKey && e.key === 'j') ||
                  (e.metaKey && e.key === 'J') ||
                  (e.metaKey && e.altKey && e.key === 'j') ||
                  (e.metaKey && e.altKey && e.key === 'J') ||
                  (e.metaKey && e.shiftKey && e.key === 'j') ||
                  (e.metaKey && e.shiftKey && e.key === 'J') ||
                  (e.metaKey && e.ctrlKey && e.key === 'i') ||
                  (e.metaKey && e.ctrlKey && e.key === 'I') ||
                  (e.metaKey && e.altKey && e.key === 'c') ||
                  (e.metaKey && e.altKey && e.key === 'C') ||
                  (e.metaKey && e.shiftKey && e.key === 'c') ||
                  (e.metaKey && e.shiftKey && e.key === 'C')) {
                  e.preventDefault();
                  console.log('%cThằng ăn hại! Phím tắt này bị vô hiệu hóa rồi!', 'color: #ff6600; font-size: 14px; font-weight: bold;');
                  showWarningPopup('ĐỒ ĂN HẠI! Phím tắt này bị vô hiệu hóa rồi!<br><br>Bạn đang cố gắng mở Developer Tools?<br>Hệ thống đã ghi log hành động của bạn!');
                  return false;
              }
          });
        
                 // Ngăn chặn view source (Windows và Mac)
         document.addEventListener('keydown', function(e) {
             if ((e.ctrlKey && e.key === 'u') || (e.metaKey && e.key === 'u')) {
                 e.preventDefault();
                 return false;
             }
         });
        
                 // Cảnh báo khi có người cố gắng inspect element
         let devtoolsOpen = false;
         setInterval(function() {
             if (window.outerHeight - window.innerHeight > 200 || window.outerWidth - window.innerWidth > 200) {
                 if (!devtoolsOpen) {
                     devtoolsOpen = true;
                     console.log('%cPHÁT HIỆN THẰNG HACKER Lỏ!', 'color: #ff0000; font-size: 18px; font-weight: bold;');
                     console.log('%cThằng Ngu! Bạn đang cố gắng hack website?', 'color: #ff6600; font-size: 16px;');
                     console.log('%cHệ thống đã ghi log hành động của bạn!', 'color: #00cc66; font-size: 14px;');
                     
                     // Hiển thị popup cảnh báo
                     showWarningPopup('PHÁT HIỆN THẰNG HACKER LỎ!<br><br>ĐỒ CHÓ! Bạn đang cố gắng hack website?<br>Hệ thống đã ghi log hành động của bạn!<br><br>ĐÓNG NGAY Developer Tools nếu không muốn bị ban!');
                 }
             } else {
                 devtoolsOpen = false;
             }
         }, 1000);
    })();
    
                   // Xử lý sự kiện khi mở Developer Tools (CHỈ ÁP DỤNG TRÊN DESKTOP)
      window.addEventListener('devtoolschange', function(e) {
          // Kiểm tra nếu là mobile/tablet thì bỏ qua
          function isMobile() {
              return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                     window.innerWidth <= 768;
          }
          
          if (isMobile()) {
              return;
          }
         if (e.detail.isOpen) {
             console.log('%cCẢNH BÁO!', 'color: red; font-size: 20px; font-weight: bold;');
             console.log('%cĐỒ NGU! Nếu ai đó bảo bạn copy/paste code vào đây, họ đang cố gắng hack tài khoản của bạn!', 'color: red; font-size: 14px;');
             console.log('%cĐây là một trình duyệt dành cho các nhà phát triển. Nếu bạn không biết mình đang làm gì, hãy đóng tab này ngay!', 'color: orange; font-size: 12px;');
             console.log('%cBảo mật tài khoản của bạn!', 'color: green; font-size: 16px; font-weight: bold;');
             
             // Thêm cảnh báo bổ sung
             console.log('%cĐỒ CON LỢN! KHÔNG copy/paste bất kỳ code nào vào đây!', 'color: #ff0066; font-size: 16px; font-weight: bold;');
             console.log('%cHACKER LỎ thường dụ dỗ bạn paste code để hack tài khoản!', 'color: #ff3300; font-size: 14px;');
             console.log('%cWebsite này được bảo vệ bởi hệ thống chống hack!', 'color: #00cc66; font-size: 14px; font-weight: bold;');
             console.log('%cNếu cần hỗ trợ, liên hệ admin thay vì tự sửa!', 'color: #0066ff; font-size: 12px;');
             
             // Thêm ASCII art cảnh báo
             console.log('%c' + `
     ╔══════════════════════════════════════════════════════════════╗
     ║                    CẢNH BÁO BẢO MẬT                        ║
     ║                                                              ║
     ║  ĐỒ ĂN HẠI! Nếu ai đó bảo bạn paste code vào đây, họ đang hack bạn!    ║
     ║  ĐÓNG NGAY Developer Tools để bảo vệ tài khoản!             ║
     ║                                                              ║
     ║  Website được bảo vệ bởi hệ thống chống hack              ║
     ║  Mọi hành động đều được ghi log và theo dõi              ║
     ║  Không copy/paste bất kỳ code nào vào console!          ║
     ╚══════════════════════════════════════════════════════════════╝
             `, 'color: #ff0000; font-family: monospace; font-size: 12px;');
            
            // Thêm cảnh báo trên trang web
            const warningDiv = document.createElement('div');
            warningDiv.id = 'devtools-warning';
            warningDiv.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                background: linear-gradient(45deg, #ff0000, #ff6600);
                color: white;
                padding: 10px;
                text-align: center;
                font-weight: bold;
                font-size: 14px;
                z-index: 999999;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            `;
                         warningDiv.innerHTML = 'CẢNH BÁO: Developer Tools đã được mở! HACKER LỎ! Hãy đóng ngay để bảo vệ tài khoản!';
            document.body.appendChild(warningDiv);
            
            // Tự động ẩn sau 5 giây
            setTimeout(() => {
                if (warningDiv.parentNode) {
                    warningDiv.parentNode.removeChild(warningDiv);
                }
            }, 5000);
        } else {
            // Ẩn cảnh báo khi đóng Developer Tools
            const warningDiv = document.getElementById('devtools-warning');
            if (warningDiv && warningDiv.parentNode) {
                warningDiv.parentNode.removeChild(warningDiv);
            }
        }
    });
    
         // Thông báo bảo mật khi trang web load
     function isMobile() {
         return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                window.innerWidth <= 768;
     }
     
     if (isMobile()) {
         console.log('%c📱 MOBILE MODE - Anti-debug disabled', 'color: #00cc66; font-size: 14px; font-weight: bold;');
         console.log('%cWebsite vẫn được bảo vệ bởi hệ thống chống hack!', 'color: #0066ff; font-size: 12px;');
     } else {
         console.log('%cCHÀO MỪNG ĐẾN VỚI WEBSITE BẢO MẬT!', 'color: #00cc66; font-size: 16px; font-weight: bold;');
         console.log('%cWebsite này được bảo vệ bởi hệ thống chống hack!', 'color: #0066ff; font-size: 14px;');
         console.log('%cKHÔNG copy/paste code vào console nếu ai đó yêu cầu!', 'color: #ff6600; font-size: 14px; font-weight: bold;');
         console.log('%cNếu cần hỗ trợ, hãy liên hệ admin thay vì tự sửa!', 'color: #666666; font-size: 12px;');
     }
    
    // Function chuyển đổi tab - Load sớm để tránh conflict
    window.showTab = function(tabName) {
        console.log('showTab called with:', tabName);
        
        // Ẩn tất cả tab content
        const allTabs = document.querySelectorAll('.tab-pane');
        allTabs.forEach(tab => {
            tab.classList.remove('active', 'show');
            tab.style.display = 'none';
        });
        
        // Bỏ active tất cả tab buttons
        const allButtons = document.querySelectorAll('.nav-link');
        allButtons.forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Hiển thị tab được chọn
        if (tabName === 'card') {
            document.getElementById('card-recharge').classList.add('active', 'show');
            document.getElementById('card-recharge').style.display = 'block';
            document.getElementById('card-tab').classList.add('active');
            document.getElementById('bank-history').style.display = 'none';
        } else if (tabName === 'bank') {
            document.getElementById('bank-recharge').classList.add('active', 'show');
            document.getElementById('bank-recharge').style.display = 'block';
            // Hiển thị lịch sử trong cùng tab
            var history = document.getElementById('bank-history');
            if (history) { history.style.display = 'block'; history.classList.add('active','show'); }
            document.getElementById('bank-tab').classList.add('active');
            console.log('Bank tab activated!');
        }

        // Đảm bảo dropdown menu hoạt động: ngăn overlay/tooltip cũ
        const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
        openDropdowns.forEach(d => d.classList.remove('show'));
    }

    // Function BuyNow - Load sớm để tránh lỗi
    window.BuyNow = function(id) {
        // Kiểm tra user đã đăng nhập chưa
        var csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
        if (!csrfToken) {
            Swal.fire({
                title: '⚠️ Vui lòng đăng nhập!',
                text: "Bạn cần đăng nhập để có thể mua vật phẩm.",
                icon: 'warning',
                confirmButtonText: "Đăng nhập",
                showCancelButton: true,
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/dang-nhap';
                }
            });
            return;
        }
        
        if (typeof Swal === 'undefined') {
            alert('Đang tải thư viện, vui lòng thử lại!');
            return;
        }
        
        Swal.fire({
            title: 'Thông Báo',
            text: "Bạn có chắc muốn mua vật phẩm này không?",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Mua Ngay",
            cancelButtonText: "Hủy!"
        }).then((result) => {
            if (result.isConfirmed) {
                // CSRF token đã được lấy ở trên
                
                $.ajax({
                    type: "POST",
                    url: "/ajax/shop/buy.php",
                    data: {
                        Tempid: id,
                        csrf_token: csrfToken
                    },
                    success: function(response) {
                        try {
                            var cvhvn = JSON.parse(response);
                            if (cvhvn.status === true) {
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(cvhvn.message, 'Thông báo', {
                                        timeOut: 5000
                                    });
                                } else {
                                    alert(cvhvn.message);
                                }
                                // Reload trang sau khi mua thành công
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                if (typeof toastr !== 'undefined') {
                                    toastr.error(cvhvn.message, 'Thông báo', {
                                        timeOut: 5000
                                    });
                                } else {
                                    alert('Lỗi: ' + cvhvn.message);
                                }
                            }
                        } catch (e) {
                            if (typeof toastr !== 'undefined') {
                                toastr.error("Dữ liệu phản hồi không hợp lệ.", 'Thông báo', {
                                    timeOut: 5000
                                });
                            } else {
                                alert("Dữ liệu phản hồi không hợp lệ.");
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        if (typeof toastr !== 'undefined') {
                            toastr.error("Đã xảy ra lỗi trong quá trình gửi dữ liệu.", 'Thông báo', {
                                timeOut: 5000
                            });
                        } else {
                            alert("Đã xảy ra lỗi trong quá trình gửi dữ liệu.");
                        }
                    }
                });
            }
        });
    }
    </script>
</head>

<body class="pace-done app-with-bg" style="background-image: url(<?php echo $setting['background']; ?>);">