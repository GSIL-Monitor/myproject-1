"use strict";
$(function() {
    var dis = false;
    $("#form").validate({
        rules: {
            companyId: {
                required: true
            },
            goodsUrlId: {
                required: true
            },
            url: {
                required: true,
                url: true
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
            var companyId = $('#companyId').val(),
                goodsUrlId = $('#goodsUrlId').val(),
                url = $('#url').val();
            if (dis) {
                return false;
            }
            dis = true;
            $.ajax({
                type: 'post',
                data: {
                    'goodsUrlId': goodsUrlId,
                    'url': url,
                    'companyId': companyId
                },
                url: "/admin/editGoodsUrlSubmit",
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