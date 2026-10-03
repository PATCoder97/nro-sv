eval(function (p, a, c, k, e, d) { e = function (c) { return (c < a ? '' : e(parseInt(c / a))) + ((c = c % a) > 35 ? String.fromCharCode(c + 29) : c.toString(36)) }; if (!''.replace(/^/, String)) { while (c--) { d[e(c)] = k[c] || e(c) } k = [function (e) { return d[e] }]; e = function () { return '\\w+' }; c = 1 }; while (c--) { if (k[c]) { p = p.replace(new RegExp('\\b' + e(c) + '\\b', 'g'), k[c]) } } return p }('3 p(r,q){W[q](r,"vôw báo",{x:f})}$(z).D(3(){$("F[E=f]").m(3(e){e.C();2 6=B;2 a=$(6).d("A");2 7=$(6).d("7");2 5=$(6).d("5");2 c=$(6).y();2 4=$(6).u("4[n=m]");l(a,7,5,c,4)})});3 l(a,7,5,c,4){2 k=4.g().H();2 i={n:7,a,c,I:"V",G:3(){4.j("h",!0).g("ĐT Xử Lý...")},S:3(){4.j("h",!1).g(k)},t:3(8){p(8.R,8.s===f?"t":"9");Q(8.s===f&&4.d("5")){P(()=>{O.N.5=5},M)}},9:3(9){K.J(9)},};$.U(i)}', 60, 60, '||let|function|button|href|_this|method|response|error|url||data|attr||true|html|disabled|setting|prop|textButton|submitForm|submit|type||notice|icon|text|status|success|find|Th|ng|closeButton|serialize|document|action|this|preventDefault|ready|cvhvn|form|beforeSend|trim|dataType|log|console||2000|location|window|setTimeout|if|message|complete|ang|ajax|json|toastr|'.split('|'), 0, {}))


// Bổ sung
document.addEventListener('DOMContentLoaded', function () {
    var sendCodeBtn = document.getElementById('sendCodeBtn');
    if (!sendCodeBtn) return;

    sendCodeBtn.addEventListener('click', function () {
        var emailInput = document.querySelector('input[name="email"]');
        var email = emailInput ? emailInput.value : '';
        var btn = this;

        if (email) {
            localStorage.setItem('savedEmail', email);
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            if (emailInput) emailInput.readOnly = true;
            fetch('/ajax/users/sendcode.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        toastr.success(data.message, 'Thông báo', { timeOut: 5000 });
                        var currentTime = Math.floor(Date.now() / 1000);
                        var countdownTime = 120;
                        localStorage.setItem('countdown', currentTime + countdownTime);
                        startCountdown(countdownTime, btn, emailInput);
                    } else {
                        toastr.error(data.message, 'Thông báo', { timeOut: 5000 });
                        btn.disabled = false;
                        btn.textContent = 'Gửi Mã';
                        if (emailInput) emailInput.readOnly = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Đã xảy ra lỗi, vui lòng thử lại.', 'Thông báo', { timeOut: 5000 });
                    btn.disabled = false;
                    btn.textContent = 'Gửi Mã';
                    if (emailInput) emailInput.disabled = false;
                });
        } else {
            toastr.error('Vui lòng nhập email trước khi gửi mã!', 'Thông báo', { timeOut: 5000 });
        }
    });
});

function startCountdown(duration, sendCodeBtn, emailInput) {
    var countdown = duration;
    var interval = setInterval(function () {
        sendCodeBtn.textContent = countdown + 's';
        countdown--;

        if (countdown < 0) {
            clearInterval(interval);
            sendCodeBtn.disabled = false;
            sendCodeBtn.textContent = 'Gửi Mã';
            if (emailInput) emailInput.readOnly = false;
            localStorage.removeItem('savedEmail');
            localStorage.removeItem('countdown');
        }
    }, 1000);
}

function getCookie(name) {
    let cookieArr = document.cookie.split(";");
    for(let i = 0; i < cookieArr.length; i++) {
        let cookiePair = cookieArr[i].split("=");
        if(name === cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
}

function setCookie(name, value, hours) {
    let date = new Date();
    date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
    document.cookie = name + "=" + value + ";expires=" + date.toUTCString() + ";path=/";
}