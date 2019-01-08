"use strict";
$(function() {
    var dis = false;
    $("#form").validate({
        rules: {
            companyId: {
                required: true
            },
            code: {
                required: true
            },
            errType: {
                required: true
            },
            chMsg: {
                required: true
            },
            enMsg: {
                required: true
            },
            koMsg : {
                required: true
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
            var errorCodeId = $('#errorCodeId').val(),
                companyId = $('#companyId').val(),
                code = $('#code').val(),
                errType = $('#errType').val(),
                chMsg = $('#chMsg').val(),
                enMsg = $('#enMsg').val(),
                koMsg = $('#koMsg').val();
            if (dis) {
                return false;
            }
            dis = true;
            $.ajax({
                type: 'post',
                data: {
                    'errorCodeId': errorCodeId,
                    'companyId': companyId,
                    'code': code,
                    'errType': errType,
                    'chMsg': chMsg,
                    'enMsg': enMsg,
                    'koMsg': koMsg
                },
                url: "/admin/editErrorCodeSubmit",
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