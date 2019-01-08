"use strict";
$(function() {
    var dis = false;
    if (window.top != window.self) {
        window.top.location = "/admin/login";
    }
    $("#login").validate({
        rules: {
            userName: {
                required: true,
                minlength: 4,
                maxlength: 16
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 16
            },
            verifyCode: {
                required: true
            },
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
            if (dis) {
                return false;
            }
            dis = true;
            var load = layer.load(2, {
                shade: [0.8, '#000']
            });
            $.ajax({
                type: 'post',
                data: {
                    'userName': function() {
                        return $('#userName').val();
                    },
                    'password': function() {
                        return window.md5($('#password').val());
                    },
                    'verifyCode': function() {
                        return $('#verifyCode').val();
                    }
                },
                url: "/admin/loginSubmit",
                success: function(data) {
                    var data = JSON.parse(data);
                    layer.close(load);
                    if (data.code == 'N00000') {
                        layer.msg('登录成功!', {
                            icon: 1,
                            time: 1000
                        });
                        setTimeout(function() {
                            location.href = data.message;
                        }, 1200);
                    } else {
                        layer.msg(data.message, {
                            icon: 2,
                            time: 1000
                        });
                        $('#J-verifyCode').trigger('click');
                    }
                    dis = false;
                },
                error: function(XmlHttpRequest, textStatus, errorThrown) {
                    layer.close(load);
                    layer.msg('网络错误!', {
                        icon: 2,
                        time: 1000
                    });
                    dis = false;
                }
            });
        }
    });

    $('#J-verifyCode').on('click', function() {
        var src = '/admin/verifyCodeImage';
        src += '?rnd=' + (new Date()).getTime();
        $('#imgSrc').attr({
            'src': src
        });
    }).trigger('click');

});