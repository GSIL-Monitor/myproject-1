//"use strict";
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
            firmwareName: {
                required: true
            },
            url: {
                required: true
            },
            checkCode: {
                required: true
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
            var firmwareId = $('#firmwareId').val(),
                companyId = $('#companyId').val(),
                versionCode = $('#versionCode').val(),
                firmwareName = $('#firmwareName').val(),
                url = $('#url').val(),
                displayVersionCode = $('#displayVersionCode').val(),
                whiteGroupIds = [],
                autoUpdate = $('#autoUpdate').is(':checked') ? 1 : 0,
                description = $('#description').val(),
                enDescription = $('#enDescription').val(),
                checkCode = $('#checkCode').val();
            $('input[name="whiteGroupId"]').each(function() {
                if (this.checked === true) {
                    whiteGroupIds.push(this.value);
                }
            });

            if (dis) {
                return false;
            }
            dis = true;

            if ($('input[name="whiteGroupId"][value=0]').prop("checked") == true ||
                $('#autoUpdate').prop("checked") == true) {
                layer.open({
                        type: 1,
                        area: ['800px', '326px'],
                        fix: false, //不固定
                        maxmin: true,
                        shade: 0.4,
                        btn: ['确认', '取消'],
                        title: "您已勾选所有SN 或自动升级，确定发布？",
                        content:'<form class="form1 form-horizontal" id="form1" action="/admin/checkFirmwarePassword" method="post"><div class="row cl" style="width: 100%;padding: 4%;"><label class="form-label col-xs-4 col-sm-2">发布密码：</label><div class="formControls col-xs-8 col-sm-9"><input type="text"class="input-text"value=""placeholder="发布密码："id="checkFirmwarePassword"name="checkFirmwarePassword" required="required"></div></div><div class="row cl" style="width: 100%;padding: 4%;display:block;"><label class="form-label col-xs-4 col-sm-2">发布人：</label><div class="formControls col-xs-8 col-sm-9"><input type="text"class="input-text"value=""placeholder="发布人："id="checkFirmwareName"name="checkFirmwareName"  required="required"></div></div></form>',
                        success: function(layero,index) {
                            $('.layui-layer-btn0').click(function(){
                                    var password = $('#checkFirmwarePassword').val();
                                     name     = $('#checkFirmwareName').val();
                                        $.ajax({
                                            url : 'checkFirmwarePassword',
                                            type : 'get',
                                            data : {
                                                'firmwarePassword' : window.md5(password),
                                                'name' : name
                                            },

                                            success: function(data) {
                                                var data = JSON.parse(data);
                                                
                                                submits();
                                            }
                                                
                                        });
                                    return false;
                                });
                            },
                });
                return false;
            } else {
                submits();
            }

            function submits() {
                $.ajax({
                    type: 'post',
                    data: {
                        'firmwareId': firmwareId,
                        'companyId': companyId,
                        'versionCode': versionCode,
                        'firmwareName': firmwareName,
                        'url': url,
                        'displayVersionCode': displayVersionCode,
                        'whiteGroupIds': whiteGroupIds.toString(),
                        'autoUpdate': autoUpdate,
                        'description': description,
                        'enDescription': enDescription,
                        'checkCode': checkCode,
                        'name' : name
                    },
                    url: '/admin/addFirmwareSubmit',
                    success: function(data) {
                        var data = JSON.parse(data);
                        if (data.code == 'N00000') {
                            layer.msg('保存成功!', {
                                icon: 1,
                                time: 1000
                            });
                            window.parent.location.reload();
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

        }
    });

    // 修改sn勾选 
    $('input[name= "whiteGroupId"]').click(function() {

        if ($(this).prop("checked") == true) {
            var whiteGroupIdVal = $(this).val();
            if (whiteGroupIdVal == 0) {
                $(this).parent().siblings().children().attr("checked", false);
                $(this).parent().siblings().children().attr("disabled", "disabled");
            }

        } else {
            $(this).parent().siblings().children().removeAttr("disabled");
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
        server: '/admin/uploadFirmwareFile',

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
            extensions: 'CleanPack',
            mimeTypes: '.CleanPack'
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
                time: 2 * 1000
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