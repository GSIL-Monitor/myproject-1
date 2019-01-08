"use strict";
$(function() {
    var _bool = false;

    $("#form").validate({
        rules: {
            placeName: {
                required: true
            },
            flag: {
                required: true
            },
            placeHeight: {
                required: true
            },
            placeWidth: {
                required: true
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
        var _placeName = $('#placeName').val(),
            _flag = $('#flag').val(),
            _placeHeight = $('#placeHeight').val(),
            _placeWidth = $('#placeWidth').val(),
            _description = $('#description').val();
            if (_bool) {
                return false;
            }
            _bool = true;
            $.ajax({
                type: 'post',
                data: {
                    placeName: _placeName,
                    flag: _flag,
                    placeHeight: _placeHeight,
                    placeWidth: _placeWidth,
                    description: _description
                },
                url: "/admin/editAdvertisementPlaceSubmit?advertisementPlaceId={{entity.advertisementPlaceId}}",/* 获取不到 entity.advertisementPlaceId 将文件迁移到HTML中了 2018-11-16*/
                success: function(data) {
                    var data = JSON.parse(data);
                    if (data.code == 'N00000') {
                        layer.msg('保存成功!', {
                            icon: 1,
                            time: 2000
                        });
                        window.parent.location.reload();
                        // $('#form', window.parent.document).find('.btn-refresh').click();
                        setTimeout(function() {
                            $('#reset').trigger('click');
                        }, 2000);
                    } else {
                        layer.msg(data.message, {
                            icon: 2,
                            time: 2000
                        });
                    }
                    _bool = false;
                },
                error: function(XmlHttpRequest, textStatus, errorThrown) {
                    layer.msg('网络错误!', {
                        icon: 2,
                        time: 2000
                    });
                    _bool = false;
                }
            });
        }
    });

    $('#reset').on('click', function() {
        layer_close();
        return false;
    });

});