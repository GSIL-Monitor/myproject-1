"use strict";
$(function() {
    var cnContent = UE.getEditor('cnContent', {
            allowDivTransToP: false
        }),
    // enContent = UE.getEditor('enContent', {
    //     allowDivTransToP: false
    // }),
        type = $('#type').val(),
        dis = false;

    $('#type').on('change', function() {
        var v = this.value;
        if (v == 1) {
            $('#description').prop('disabled', true);
        } else {
            $('#description').removeAttr('disabled');
        }
    });

    $('form').on('submit', function() {
        var companyId = $('#companyId').val(),
            type = $('#type').val(),
            lang = $('#lang').val(),
            description = $('#description').val(),
            //enDescription = $('#enDescription').val(),
            cnContentHtml = cnContent.getContent();
            //enContentHtml = enContent.getContent();
        cnContentHtml = cnContentHtml.replace(/ height="\d+"/g, "");
        cnContentHtml = cnContentHtml.replace(/ width="\d+"/g, " width=\"100%\"");
        cnContentHtml = cnContentHtml.replace(/(width|height):\s(\d|\d.)+px;/g, "");
        // enContentHtml = enContentHtml.replace(/ height="\d+"/g, "");
        // enContentHtml = enContentHtml.replace(/ width="\d+"/g, " width=\"100%\"");
        // enContentHtml = enContentHtml.replace(/(width|height):\s(\d|\d.)+px;/g, "");
        if (dis) {
            return false;
        }
        if (!companyId) {
            layer.msg('请选择公司或添加公司', {
                icon: 2,
                time: 1000
            });
            return false;
        }
        if (type == 2) {
            if (!description) {
                layer.msg('请填写选项描述', {
                    icon: 2,
                    time: 1000
                });
                return false;
            }
        } else {
            description = '';
            //enDescription = '';
        }

        dis = true;
        $.ajax({
            type: 'post',
            data: {
                cnContent: cnContentHtml,
                //enContent: enContentHtml,
                companyId: companyId,
                type: type,
                description: description,
                lang:lang
                //enDescription: enDescription
            },
            url: "/admin/addProductionSubmit",
            success: function(data) {
                var data = JSON.parse(data);
                if (data.code == 'N00000') {
                    layer.msg('保存成功!', {
                        icon: 1,
                        time: 1000
                    });
                    window.parent.location.reload();
                    // $('#formPro', window.parent.document).find('.btn-refresh').click();
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
        return false;
    });
    $('#submit').on('click', function() {
        $('#formPro').trigger('submit');
    });
    $('#reset').on('click', function() {
        layer_close();
        return false;
    });
});