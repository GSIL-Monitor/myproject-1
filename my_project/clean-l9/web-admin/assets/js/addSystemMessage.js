"use strict";
$(function() {
    var dis = false,
        _messageContent = UE.getEditor('_messageContent', {
            toolbars: [
                ['fullscreen', 'source', '|', 'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'simpleupload', 'insertimage', 'emotion', 'scrawl', 'attachment', 'insertcode','|',
            'inserttable', 'deletetable']
            ]
        });
    $('#messageType').on('change', function() {
        var v = this.value;
        $('#messageType_1,#messageType_2').hide();
        _messageContent.reset();
        $('#messageType_' + v).show();
    });


    $('form').on('submit', function() {
        var companyId = $('#companyId').val(),
            messageType = $('#messageType').val(),
            messageTitle = $('#messageTitle').val(),
            messageContent = messageType == 2 ? _messageContent.getContent() : $('#messageContent').val();
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
        if (!messageTitle) {
            layer.msg('请填写标题', {
                icon: 2,
                time: 1000
            });
            return false;
        }
        dis = true;
        $.ajax({
            type: 'post',
            data: {
                messageType: messageType,
                messageContent: messageContent,
                companyId: companyId,
                messageTitle: messageTitle
            },
            url: "/admin/addSystemMessageSubmit",
            success: function(data) {
                var data = JSON.parse(data);
                if (data.code == 'N00000') {
                    layer.msg('保存成功!', {
                        icon: 1,
                        time: 1000
                    });
                    window.parent.location.reload();
                    //$('#form', window.parent.document).find('.btn-refresh').click();
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
        $('#form').trigger('submit');
    });
    $('#reset').on('click', function() {
        layer_close();
        return false;
    });
});