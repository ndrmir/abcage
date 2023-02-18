let i = 0;
let titleval = ''; // Переменная для хранения title
let bool = 0; // 1 - отправка завершена
let iteration = 0;// стадия работы прогрессбара анимация
let id = 0; // идентификаторы таймеров

// Обновляем стили progressBar---
function CSSProgessBar(style) {
    if (style === 'WidthAuto') {
        jQuery(function ($) {
            $('#progressBar').css({
                'width': 'auto',
                'max-width': '300px',
                'height': 'auto',
                'padding': '30px 5px'
            });
        })
    }
    else {
        jQuery(function ($) {
            $('#progressBar').css({
                'width': '150px',
                'height': 'auto',
                'padding': '30px 5px'
            });
        })
    }
}

function SubmitDownAjax() {
    titleval = window.document.title;
    window.document.title = 'Процесс...';

    bool = 0;
    id = setInterval('doAnimation()', 400);
    document.getElementById('progressBar').innerHTML = 'Процесс';
    CSSProgessBar();

    jQuery(function ($) {
        $('#progressBar').css({
            'visibility': 'visible',
            'opacity': '0'
        }).fadeTo(300, 1);
        $('#div').css({
            'visibility': 'visible',
            'opacity': '0'
        }).fadeTo(300, 0.5);
    })

    const date = document.getElementById('date').value;
    const data = {
        'date': date,
    };
    const url = 'scripts/cost.php';

    if (window.FormData === undefined) {
        alert('В вашем браузере FormData не поддерживается');
    } else {
        const formData = new FormData();

        // Логирование файлов в formData. Для загрузки в Linux нужны права!!!
        for (const value of formData.values()) {
            console.log(value);
        }

        $.each(data, function (key, input) {
            formData.append(key, input);
        });

        $.ajax({
            type: 'POST',
            url: url,
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function (data) {
                SubmitDownAjax_callBack(data);
            }
        });
    }
}
function SubmitDownAjax_callBack(responseText) {
    bool = 1;
    window.document.title = titleval;

    document.getElementById('info').innerHTML = responseText;
    CSSProgessBar('WidthAuto');
    focusPbDouble();
}

function doAnimation() {
    if (bool === 1) {
        clearInterval(id);
        return;
    }
    if (iteration === 0) {
        document.getElementById('progressBar').innerHTML = '&nbsp;Процесс.';
        iteration++;
        return;
    }
    if (iteration === 1) {
        document.getElementById('progressBar').innerHTML = '&nbsp;&nbsp;Процесс..';
        iteration++;
        return;
    }
    if (iteration === 2) {
        document.getElementById('progressBar').innerHTML = '&nbsp;&nbsp;&nbsp;Процесс...';
        iteration++;
        return;
    }
    if (iteration === 3) {
        document.getElementById('progressBar').innerHTML = 'Процесс';
        iteration = 0;
    }
}

function focusDiv() {
    if (bool === 1) {
        jQuery(function ($) {
            $('#progressBar').fadeOut(300);
            $('#div').fadeOut(300);
        });
    }
}
function focusPbDouble() {
    if (bool === 1) {
        jQuery(function ($) {
            $('#progressBar').fadeOut(300);
            $('#div').fadeOut(300);
        });
    }
}

