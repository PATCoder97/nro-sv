<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $setting['title']; ?></title>
    <link href="/assets/css/vendor.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.6.1/tinymce.min.js"></script>
    
         <script>
     // Anti-Debug: Cảnh báo khi mở Developer Tools (Admin Panel - CHỈ ÁP DỤNG TRÊN DESKTOP)
     (function() {
         // Kiểm tra nếu là mobile/tablet thì bỏ qua anti-debug
         function isMobile() {
             return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                    window.innerWidth <= 768;
         }
         
         // Nếu là mobile thì không chạy anti-debug
         if (isMobile()) {
             console.log('Mobile device detected - Admin anti-debug disabled');
             return;
         }
                   // Thêm bảo vệ chống hack cho Mac
          document.addEventListener('keydown', function(e) {
              // Kiểm tra nếu đang có admin action thì bỏ qua
              if (window.adminActionInProgress) {
                  return;
              }
              
              // Windows: F12, Ctrl+Shift+I, Ctrl+U
              // Mac: Cmd+Option+I, Cmd+U, Cmd+Shift+I, Cmd+J, Cmd+Option+J, Cmd+Shift+J
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
                  (e.metaKey && e.ctrlKey && e.key === 'I')) {
                  e.preventDefault();
                  console.log('%cĐỒ CHÓ! Phím tắt này bị vô hiệu hóa rồi!', 'color: #ff6600; font-size: 14px; font-weight: bold;');
                  if (typeof window.showAdminWarningPopup === 'function') {
                 window.showAdminWarningPopup('ĐỒ CHÓ! Phím tắt này bị vô hiệu hóa rồi!<br><br>Bạn đang cố gắng mở Developer Tools?<br>Hệ thống đã ghi log hành động của bạn!');
             }
                  return false;
              }
          });
         
                   // Ngăn chặn right-click cho admin
          document.addEventListener('contextmenu', function(e) {
              // Kiểm tra nếu đang có admin action thì bỏ qua
              if (window.adminActionInProgress) {
                  return;
              }
              
              e.preventDefault();
              console.log('%cĐỒ CHÓ! Right-click bị vô hiệu hóa rồi!', 'color: #ff6600; font-size: 14px; font-weight: bold;');
              if (typeof window.showAdminWarningPopup === 'function') {
                 window.showAdminWarningPopup('ĐỒ CHÓ! Right-click bị vô hiệu hóa rồi!<br><br>Bạn đang cố gắng hack Admin Panel?<br>Hệ thống đã ghi log hành động của bạn!');
             }
          });
         // Hàm hiển thị popup cảnh báo cho admin
         window.showAdminWarningPopup = function(message) {
             // Tạo popup container
             const popupContainer = document.createElement('div');
             popupContainer.id = 'admin-warning-popup';
             popupContainer.style.cssText = `
                 position: fixed;
                 top: 0;
                 left: 0;
                 width: 100%;
                 height: 100%;
                 background: rgba(0, 0, 0, 0.9);
                 display: flex;
                 justify-content: center;
                 align-items: center;
                 z-index: 999999;
                 animation: fadeIn 0.3s ease;
             `;
             
             // Tạo popup content
             const popupContent = document.createElement('div');
             popupContent.style.cssText = `
                 background: linear-gradient(45deg, #ff0000, #cc0000);
                 color: white;
                 padding: 35px;
                 border-radius: 15px;
                 text-align: center;
                 max-width: 600px;
                 box-shadow: 0 15px 40px rgba(0,0,0,0.7);
                 animation: slideIn 0.3s ease;
                 border: 4px solid #ff6600;
             `;
             
             popupContent.innerHTML = `
                 <h3 style="margin: 0 0 25px 0; font-size: 28px; font-weight: bold;">🚨 CẢNH BÁO ADMIN PANEL 🚨</h3>
                 <p style="margin: 0 0 20px 0; font-size: 18px; line-height: 1.6;">${message}</p>
                 <p style="margin: 0 0 25px 0; font-size: 16px; opacity: 0.9; font-weight: bold;">MỌI HÀNH ĐỘNG ĐỀU ĐƯỢC GHI LOG!</p>
                 <button onclick="closeAdminWarningPopup()" style="
                     background: #fff;
                     color: #ff0000;
                     border: none;
                     padding: 15px 30px;
                     border-radius: 10px;
                     font-weight: bold;
                     cursor: pointer;
                     font-size: 18px;
                     transition: all 0.3s ease;
                 " onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#fff'">
                     ĐÓNG NGAY
                 </button>
             `;
             
             popupContainer.appendChild(popupContent);
             document.body.appendChild(popupContainer);
             
             // Tự động đóng sau 10 giây
             setTimeout(() => {
                 closeAdminWarningPopup();
             }, 10000);
         }
         
         // Hàm đóng popup admin
         window.closeAdminWarningPopup = function() {
             const popup = document.getElementById('admin-warning-popup');
             if (popup) {
                 popup.style.animation = 'fadeOut 0.3s ease';
                 setTimeout(() => {
                     if (popup.parentNode) {
                         popup.parentNode.removeChild(popup);
                     }
                 }, 300);
             }
         };
         
         // Thêm CSS animation cho admin
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
    
                   // Xử lý sự kiện khi mở Developer Tools (Admin - CHỈ ÁP DỤNG TRÊN DESKTOP)
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
              // Kiểm tra nếu đang có admin action thì bỏ qua
              if (window.adminActionInProgress) {
                  return;
              }
              
              console.log('%cCẢNH BÁO', 'color: red; font-size: 20px; font-weight: bold;');
              console.log('%ccút ngay, tao fix hết rồi! Mọi hành động đều được ghi log!', 'color: red; font-size: 14px;');
              console.log('%ccút ngay không tao ban!', 'color: orange; font-size: 12px;');
              console.log('%ctao fix hết rồi hack cái lồn nhé!', 'color: green; font-size: 16px; font-weight: bold;');
              
              // Hiển thị popup cảnh báo admin
              if (typeof window.showAdminWarningPopup === 'function') {
                  window.showAdminWarningPopup('ĐỒ CHÓ! Bạn đang truy cập Admin Panel!<br><br>út ngay, tao fix hết rồi! Mọi hành động đều được ghi log!<br><br>út ngay không tao ban!<br><br>tao fix hết rồi hack cái lồn nhé!');
              }
          }
     });
    </script>
    <style>
    .cvh-check .form-check {
        display: inline-block;
        margin-right: 15px;
    }

    .cvh-check .form-check-input {
        display: none;
    }

    .cvh-check .form-check-label {
        cursor: pointer;
    }

    .cvh-img {
        width: auto;
        min-width: 80px;
        height: 35px;
        border-radius: 10%;
        border: 3px solid transparent;
        transition: border-color 0.3s ease;
    }

    .avatar-img {
        width: 50px;
        height: 50px;
        border-radius: 10%;
        border: 3px solid transparent;
        transition: border-color 0.3s ease;
    }

    .cvh-check .form-check-input:checked+.form-check-label .avatar-img {
        border-color: #007bff;
    }

    .cvh-check .form-check-input:checked+.form-check-label {
        opacity: 0.7;
    }
    </style>
</head>

<body>