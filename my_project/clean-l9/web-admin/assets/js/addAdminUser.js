"use strict";
$(function() {
    var dis = false;
    $("#form").validate({
        rules: {
            companyId: {
                required: true
            },
            userName: {
                required: true
            },
            password: {
                required: true,
                minlength: 6,
                maxlength: 16
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
            var companyId = $('#companyId').val(),
                realName = $('#realName').val(),
                userName = $('#userName').val(),
                password = $('#password').val();
            if (dis) {
                return false;
            }
            dis = true;
            $.ajax({
                type: 'post',
                data: {
                    'realName': realName,
                    'userName': userName,
                    'password': window.md5(password),
                    'companyId': companyId
                },
                url: "/admin/addAdminUserSubmit",
                success: function(data) {
                    var data = JSON.parse(data);
                    if (data.code == 'N00000') {
                        layer.msg('保存成功!', {
                            icon: 1,
                            time: 1000
                        });
                        window.parent.location.reload();
                        // $('#form', window.parent.document).find('.btn-refresh').click();
                        setTimeout(function() {
                            $('#reset').trigger('click');
                        }, 1200);
                    } else {
                        layer.msg(data.message, {
                            icon: 2,
                            time: 1000
                        });
                    }
                    dis = false;
                },
                error: function(XmlHttpRequest, textStatus, errorThrown) {
                    layer.msg('网络错误!', {
                        icon: 2,
                        time: 1000
                    });
                    dis = false;
                }
            });
        }
    });

    $('#reset').on('click', function() {
        layer_close();
        return false;
    });
});