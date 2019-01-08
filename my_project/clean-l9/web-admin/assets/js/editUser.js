"use strict";
$(function() {
    var dis = false;
    $('.skin-minimal input').iCheck({
        checkboxClass: 'icheckbox-blue',
        radioClass: 'iradio-blue',
        increaseArea: '20%'
    });
    $('#avatar').on('click', function() {
        $('input[type=file]').trigger('click');
    });
    $.validator.addMethod("isPhone", function(value, element) {
        var tel = /(\+)([\d]*)(\s)([\d]*)/g;
        return this.optional(element) || ((tel.test(value) && value.match(tel).length == 1) ? true : false); //(tel.test(value));
    }, "请正确填写手机号码格式示例：+86 13800138000");
    $("#form").validate({
        rules: {
            userName: {
                required: true
            },
            password: {
                minlength: 6,
                maxlength: 16
            },
            sex: {
                required: true
            },
            phone: {
                required: true,
                isPhone: true
            },
            email: {
                email: true
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
            var companyId = $('#companyId').val(),
                userId = $('#userId').val(),
                userName = $('#userName').val(),
                password = $('#password').val(),
                avatar = $('#avatar').val(),
                sex = 0,
                radio = $('.iradio-blue'),
                email = $('#email').val(),
                phone = $('#phone').val();
            if (dis) {
                return false;
            }
            dis = true;
            radio.each(function(i) {
                var checked = $(this).hasClass('checked');
                sex = checked ? i : sex;
            });
            $.ajax({
                type: 'post',
                data: {
                    'userId': userId,
                    'companyId': companyId,
                    'userName': userName,
                    'password': !password ? '' : window.md5(password),
                    'avatar': avatar,
                    'sex': sex,
                    'email': email,
                    'phone': phone
                },
                url: "/admin/editUserSubmit",
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

    var $list = $("#fileList"),
        $btn = $("#btn-star"),
        state = "pending",
        uploader,
        // 优化retina, 在retina下这个值是2
        ratio = window.devicePixelRatio || 1,
        // 缩略图大小
        thumbnailWidth = 110 * ratio,
        thumbnailHeight = 110 * ratio;

    var uploader = WebUploader.create({
        auto: true,
        swf: '/assets/lib/webuploader/0.1.5/Uploader.swf',
        // 文件接收服务端。
        server: '/admin/uploadAvatarFile',

        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        duplicate: true,
        fileVar: 'file',
        pick: {
            id: '#filePicker',
            name: "file",
            label: '点击上传',
            multiple: false //默认为true，true表示可以多选文件，HTML5的属性
        },

        // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
        resize: false,
        // 只允许选择图片文件。
        accept: {
            title: 'Files',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/*'
        }
    });

    // uploader.on("uploadAccept", function(object, response) {
    //     var data = JSON.parse(response._raw);
    // });

    uploader.on('fileQueued', function(file) {
        var $li = $(
                '<div id="' + file.id + '" class="item">' +
                '<div class="pic-box"><img></div>' +
                //'<div class="info">' + file.name + '</div>' +
                '<p class="state">等待上传...</p>' +
                '</div>'
            ),
            $img = $li.find('img');
        $list.append($li);

        // 创建缩略图
        // 如果为非图片文件，可以不用调用此方法。
        // thumbnailWidth x thumbnailHeight 为 100 x 100
        uploader.makeThumb(file, function(error, src) {
            if (error) {
                $img.replaceWith('<span>不能预览</span>');
                return;
            }
            $img.attr('src', src);
        }, thumbnailWidth, thumbnailHeight);
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
            $('#' + file.id).find(".state").text("上传成功");
            $('#avatar').val(data.data.fileName);
        } else {
            $('#' + file.id).remove();
            layer.msg(data.message, {
                icon: 2,
                time: 2 * 1500
            });
        }
    });

    // 文件上传失败，显示上传出错。
    uploader.on('uploadError', function(file) {
        $('#' + file.id).remove();
        layer.msg('上传出错', {
            icon: 2,
            time: 2 * 1500
        });
    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on('uploadComplete', function(file) {
        $('#' + file.id).remove();
    });
});