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
    cnContent.ready(function() {
        var html = $('#J-cnContent').html();
        html = UE.utils.html(html);
        cnContent.execCommand('insertHtml', html);
    });    

    $('form').on('submit', function() {
        var basicInfoId = $('#basicInfoId').val(),
            cnContentHtml = cnContent.getContent(),
            description = $('#description').val(),
            lang = $('#lang').val();
        // enDescription = $('#enDescription').val(),
        // enContentHtml = enContent.getContent();
        cnContentHtml = cnContentHtml.replace(/ height="\d+"/g, "");
        cnContentHtml = cnContentHtml.replace(/ width="\d+"/g, " width=\"100%\"");
        cnContentHtml = cnContentHtml.replace(/(width|height):\s(\d|\d.)+px;/g, "");
        // enContentHtml = enContentHtml.replace(/ height="\d+"/g, "");
        // enContentHtml = enContentHtml.replace(/ width="\d+"/g, " width=\"100%\"");
        // enContentHtml = enContentHtml.replace(/(width|height):\s(\d|\d.)+px;/g, "");

        if (dis) {
            return false;
        }

        dis = true;
        $.ajax({
            type: 'post',
            data: {
                content: cnContentHtml,
                basicInfoId: basicInfoId,
                description: description,
                lang: lang
            },
            url: "/admin/editUserProtocolSubmit",
            success: function(data) {
                var data = JSON.parse(data);
                if (data.code == 'N00000') {
                    layer.msg('保存成功!', {
                        icon: 1,
                        time: 1000
                    });
                    window.location.reload();
                    // setTimeout(function() {
                    //     $('#reset').trigger('click');
                    // }, 1200);
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