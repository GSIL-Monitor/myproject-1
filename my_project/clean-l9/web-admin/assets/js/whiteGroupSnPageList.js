"use strict";
$(function() {
    delAllSelect('whiteGroupSnIdList', '/admin/deleteWhiteGroupSnList');
    $('#search').on('click', function() {
        loadDataList("/admin/getWhiteGroupSnPageList", 1);
    }).trigger('click');

    $('#uploadWhiteGroupSnExcel').on('click', function() {
        $('input[name="file"]').click();
        return false;
    })
    /**
     * [loadDataList description]
     * @param  {[type]} urlString [请求地址]
     * @param  {[type]} page      [当前分页]
     */
    function loadDataList(urlString, page) {
        if (!urlString) {
            return false;
        }
        var curr = page || 1;
        var whiteGroupId = $('#whiteGroupId').val(),
            sn = $('#sn').val();
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                whiteGroupId: whiteGroupId,
                sn: sn,
                pageIndex: curr,
                pageSize: CONFIG.pageSize
            },
            success: function(result) {
                var result = JSON.parse(result),
                    data = result.data;
                if (result.code == 'N00000' && data != undefined) {
                    $('#listCount').text(data.length);
                    laypage({
                        cont: $('#page'), //容器。值支持id名、原生dom对象，jquery对象,
                        skip: true, //是否开启跳页
                        //skin: '#AF0000',
                        groups: 6, //连续显示分页数
                        pages: 1, //总页数
                        curr: curr || 1, //当前页
                        jump: function(obj, first) { //触发分页后的回调
                            if (!first) { //点击跳页触发函数自身，并传递当前页：obj.curr
                                loadDataList(urlString, obj.curr);
                            }
                        }
                    });
                    if (data != undefined) {
                        var tableData = data;
                        $('#dataList').dataTable({
                            "autoWidth": true, //自动宽度
                            "info": false,
                            "paging": false, //分页
                            "ordering": false, //排序
                            "searching": false, //本地搜索
                            "deferRender": true, //延时渲染
                            "destroy": true, //销毁先前实例
                            "data": tableData, //数据
                            "columns": [{
                                "data": "whiteGroupSnId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.whiteGroupSnId + '" name="selDel">';
                                }
                            }, {
                                "data": "sn"
                            }, {
                                "data": "noteName",
                                "render": function(data, type, row, meta) {
                                    return (row.noteName === undefined) ? '' : row.noteName;
                                }
                            }, {
                                "data": "createTime",
                                "render": function(data, type, row, meta) {
                                    return (row.createTime).toString().replace(/T/g, ' ').replace(/\+[\d]{4}/, '');
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="编辑" href="javascript:;" onclick="snsModeShow(\'编辑\',\'/admin/editWhiteGroupSnSubmit?whiteGroupSnId=' + row.whiteGroupSnId + '\',\'' + row.sn + '\',\'' + row.noteName + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteWhiteGroupSnList?whiteGroupSnIdList=' + row.whiteGroupSnId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
                                }
                            }],
                            "createdRow": function(row, data, index) {
                                $(row).addClass('text-c');
                            }
                        });
                    }
                } else {
                    layer.msg(result.message, {
                        icon: 1,
                        time: 1000
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('网络错误!', {
                    icon: 1,
                    time: 1000
                });
                console.error(XMLHttpRequest, textStatus, errorThrown);
            }
        });
    }
});

var dis = false;

function snsModeShow(title, url, str, val) {
    var noteName1 = $('#noteName'),
        sn1 = $('#sn1'),
        whiteGroupId1 = $('#whiteGroupId');
    sn1.val(str != undefined ? str : '');
    noteName1.val(val != undefined ? val : '');
    layer.open({
        type: 1,
        area: ['800px', '310px'],
        fix: false, //不固定
        maxmin: true,
        shade: 0.4,
        title: title,
        content: $('#addSns'),
        success: function(layero, index) {
            $('#reset').on('click', function() {
                layer.close(index);
                return false;
            });
            $("#form").validate({
                rules: {
                    sn1: {
                        required: true
                    },
                    whiteGroupId: {
                        required: true
                    }
                },
                onkeyup: false,
                focusCleanup: true,
                success: "valid",
                submitHandler: function(form) {
                    var noteName = noteName1.val(),
                        sn = sn1.val(),
                        whiteGroupId = whiteGroupId1.val();
                    if (dis) {
                        return false;
                    }
                    dis = true;
                    $.ajax({
                        type: 'post',
                        data: {
                            'noteName': noteName,
                            'sn': sn,
                            'whiteGroupId': whiteGroupId
                        },
                        url: url,
                        success: function(data) {
                            var data = JSON.parse(data);
                            if (data.code == 'N00000') {
                                layer.msg('保存成功!', {
                                    icon: 1,
                                    time: 1000
                                });
                                setTimeout(function() {
                                    location.replace(location.href)
                                    //$('#reset').trigger('click');
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
        }
    });
    return false;
}
$(function() {
    var $list = $("#fileList"),
        whiteGroupId = $('#whiteGroupId').val(),
        uploader;

    uploader = WebUploader.create({
        auto: true,
        swf: '/assets/lib/webuploader/0.1.5/Uploader.swf',
        // 文件接收服务端。
        server: '/admin/uploadWhiteGroupSnExcel?whiteGroupId=' + whiteGroupId,

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
        layer.msg('准备上传啦..');
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
        layer.msg(data.message, {
            time: 2 * 1000
        });
        if (data.code == 'N00000') {
            location.replace(location.href)
        }
    });

    // 文件上传失败，显示上传出错。
    uploader.on('uploadError', function(file) {
        $('#' + file.id).addClass('upload-state-error').find(".state").text("上传出错");
        layer.msg('上传出错', {
            icon: 2,
            time: 2 * 1000
        });
    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on('uploadComplete', function(file) {
        $('#' + file.id).find('.progress-box').fadeOut();
        layer.msg('正在操作..');
    });
});