/**
 * 
 * @authors AZ (wkzeng@imscv.com)
 * @date    2018-08-21 23:48:46
 * @version $Id$
 */
var tables = null;
layui.use(['element', 'layer', 'form'], function() {
    var element = layui.element,
        form = layui.form,
        layer = layui.layer;

    $('#sn').on('focus', function() {
        $(this).select();
    }).trigger('focus');

    $('#uploadOQCSnExcel').on('click', function() {
        $('input[name="file"]').click();
        return false;
    });

    //自定义验证规则
    form.verify({
        sn: function(value) {
            if (value.length < 16) {
                return 'SN错误';
            }
        }
    });

    //监听提交
    form.on('submit(submit)', function(data) {
        var tip = $('.tip'),
            action = $('.layui-form').attr('action');
        $.ajax({
            url: action,
            data: data.field,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                tip.html('');
            },
            success: function(result) {
                tip.html(result.message);
                if (result.code == 'N00000') {
                    tables.ajax.reload();
                    tip.css({
                        'color': 'green'
                    });
                } else {
                    tip.css({
                        'color': 'red'
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                tip.html('网络错误');
                tip.css({
                    'color': 'red'
                });
            }
        });
        return false;
    });

    // 记录数据
    tables = $('#dataList').DataTable({
        'language': {
            'url': '/js/vendor/dataTables/Chinese.json'
        },
        'serverSide': false,
        'pageLength': 50,
        'ajax': {
            'url': '/OQC/getOQCList',
            'dataSrc': 'data'
        },
        'columnDefs': [{
            'title': 'SN',
            'data': 'sn',
            'render': function(data, type, row, meta) {
                return row.sn === null ? "" : row.sn;
            },
            'targets': 0
        }, {
            'title': '公司/型号ID',
            'data': 'companyId',
            'render': function(data, type, row, meta) {
                return row.companyId === null ? "" : row.companyId;
            },
            'targets': 1
        }, {
            'title': '时 间',
            'data': 'createTime',
            'render': function(data, type, row, meta) {
                return row.createTime === null ? "" : row.createTime;
            },
            'targets': 2
        }]
    });
});

$(function() {
    var $list = $("#fileList"),
        tip = $('.tip'),
        uploader;

    uploader = WebUploader.create({
        auto: true,
        swf: '/js/vendor/webuploader/0.1.5/Uploader.swf',
        // 文件接收服务端。
        server: '/OQC/uploadOQCSnExcel',

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
            extensions: 'csv,xls,xlsx',
            mimeTypes: 'application/vnd.ms-excel'
        }
    });

    // uploader.on("uploadAccept", function(object, response) {
    //     var data = JSON.parse(response._raw);
    // });

    uploader.on('fileQueued', function(file) {
        var $li = $(
            '<div id="' + file.id + '" class="item">' +
            '<p class="state">等待上传...</p>' +
            '</div>'
        );
        $list.append($li);
        tip.html('准备上传啦..');
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
        tip.html('上传' + percentage * 100 + '%');
    });

    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.on('uploadSuccess', function(file, response) {
        var data = JSON.parse(response._raw);
        tip.css({
            'color': 'green'
        }).html(data.message);
        tables.ajax.reload();
    });

    // 文件上传失败，显示上传出错。
    uploader.on('uploadError', function(file) {
        $('#' + file.id).addClass('upload-state-error').find(".state").text("上传出错");
        tip.css({
            'color': 'red'
        }).html('上传出错');
    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on('uploadComplete', function(file) {
        $('#' + file.id).find('.progress-box').fadeOut();
        // tip.html('正在操作..');
    });
});