"use strict";
$(function() {
    var dis = false;

    $("#form").validate({
        rules: {
            companyId: {
                required: true
            },
            versionCode: {
                required: true
            },
            appName: {
                required: true
            },
            url: {
                required: true
            },
            appType: {
                required: true
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
            var companyId = $('#companyId').val(),
                versionCode = $('#versionCode').val(),
                appName = $('#appName').val(),
                url = $('#url').val(),
                description = $('#description').val(),  
                appType = $('#appType').val();
            if (dis) {
                return false;
            }
            dis = true;
            $.ajax({
                type: 'post',
                data: {
                    'companyId': companyId,
                    'versionCode': versionCode,
                    'appName': appName,
                    'url': url,
                    'description': description,
                    'appType': appType
                },
                url: "/admin/addAppVersionSubmit",
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

    var $list = $("#fileList"),
        $btn = $("#btn-star"),
        state = "pending",
        uploader;

    var uploader = WebUploader.create({
        auto: true,
        swf: '/assets/lib/webuploader/0.1.5/Uploader.swf',
        // 文件接收服务端。
        server: '/admin/uploadAppVersionFile',

        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        duplicate: true,
        fileVar: 'file',
        pick: {
            id: '#filePicker',
            name: "file",
            //label: '点击选择文件',
            multiple: false //默认为true，true表示可以多选文件，HTML5的属性
        },

        // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
        resize: false,
        // 只允许选择图片文件。
        accept: {
            title: 'Files',
            extensions: 'apk,ipa',
            mimeTypes: '.apk,.ipa'
        }
    });

    // uploader.on("uploadAccept", function(object, response) {
    //     var data = JSON.parse(response._raw);
    // });

    uploader.on('fileQueued', function(file) {
        var $li = $(
                '<div id="' + file.id + '" class="item">' +
                '<div class="pic-box"><img></div>' +
                '<div class="info">' + file.name + '</div>' +
                '<p class="state">等待上传...</p>' +
                '</div>'
            ),
            $img = $li.find('img');
        $list.append($li);
    });
    // 文件上传过程中创建进度条实时显示。
    uploader.on('uploadProgress', function(file, percentage) {
        var $li = $('#' + file.id),
            $percent = $li.find('.progress-box .sr-only');
        // 避免重复创建
        if (!$percent.length) {
            $percent = $('<div class="progress-box"><span class="progress-bar radius"><span class="sr-only" style="width:0%"></span></span></div>').appendTo($li).find('.sr-only');
        }
        $li.find(".state").text("上传中..");
        $percent.css('width', percentage * 100 + '%');
    });

    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.on('uploadSuccess', function(file, response) {
        var data = JSON.parse(response._raw);
        if (data.code == 'N00000') {
            $('#' + file.id).addClass('upload-state-success').find(".state").remove();
            $('#url').val(data.data.fileName);
        } else {
            $('#' + file.id).addClass('upload-state-error').find(".state").text("上传出错");
            layer.msg(data.message, {
                icon: 2,
                time: 2*1000
            });
        }
    });

    // 文件上传失败，显示上传出错。
    uploader.on('uploadError', function(file) {
        $('#' + file.id).addClass('upload-state-error').find(".state").text("上传出错");
    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on('uploadComplete', function(file) {
        $('#' + file.id).find('.progress-box').fadeOut();
    });
});