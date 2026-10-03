<div id="livechat" class="py-3 bg-component">
    <div class="container-xxl p-3 p-lg-2">
        <div class="alert alert-secondary">
            <b>Tuân thủ nội quy phòng chat, không xúc phạm, gây rối, hoặc gửi những từ ngữ thô tục, nếu bị phát hiện bạn
                sẽ bị cấm vĩnh viễn và có thể bị khóa tài khoản.</b>
        </div>
        <div class="card mt-5">
            <div class="card-header bg-none fw-bold d-flex align-items-center">Trò chuyện trực tiếp</div>
            <div class="card-body bg-light cvh-chat" data-scrollbar="true" data-height="550px">
                <div class="widget-chat"></div>
            </div>
            <?php if($user){ ?>
            <div class="card-footer bg-none">
                <div class="input-group">
                    <input type="text" id="messages" class="form-control" placeholder="Nhập nội dung cần gửi">
                    <button class="btn btn-theme btn-sm w-50px fs-13px py-2px px-2" id="send" type="submit"><i
                            class="fab fa-telegram-plane" aria-hidden="true"></i></button>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>
const chatBox = document.querySelector(".cvh-chat");

function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function loadMessages() {
    $.ajax({
        url: '/ajax/messages/get.php',
        type: 'GET',
        success: function(data) {
            $('.widget-chat').html(data);
            scrollToBottom();
        }
    });
}


document.addEventListener("DOMContentLoaded", function() {
    // Load messages lần đầu
    loadMessages();
    
    // Chỉ auto-refresh nếu có user đăng nhập
    <?php if($user){ ?>
    setInterval(loadMessages, 2000);
    <?php } ?>
    
    setTimeout(function() {
        scrollToBottom();
    }, 1000);
});
<?php if($user){ ?>

function sendMessage() {
    const message = document.getElementById('messages').value;
    if (message !== '') {
        $.ajax({
            url: '/ajax/messages/send.php',
            type: 'POST',
            data: {
                message: message
            },
            success: function(response) {
                document.getElementById('messages').value = '';
                loadMessages();
            }
        });
    }
}

document.getElementById('send').addEventListener('click', function() {
    sendMessage();
});

document.getElementById('messages').addEventListener('keypress', function(e) {
    if (e.which === 13) {
        sendMessage();
    }
});
<?php } ?>
</script>