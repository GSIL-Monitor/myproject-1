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
                adminUserId = $('#adminUserId').val(),
                userName = $('#userName').val(),
                password = $('#password').val();
            password = !password ? '' : md5(password);
            if (dis) {
                return false;
            }
            dis = true;
            $.ajax({
                type: 'post',
                data: {
                    'adminUserId': adminUserId,
                    'realName': realName,
                    'companyId': companyId,
                    'userName': userName,
                    'password': password
                },
                url: "/admin/editAdminUserSubmit",
                success: function(data) {
                    var data = JSON.parse(data);
                    if (data.code == 'N00000') {
                        layer.msg('保存成功!', {
                            icon: 1,
                            time: 1000
                        });
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