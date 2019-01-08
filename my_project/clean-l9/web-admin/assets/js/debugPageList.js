"use strict";
$(function() {
    $('#search').on('click', function() {
        loadDataList("/admin/getMachinePageList", 1);
        return false;
    }).trigger('click');

    delAllSelect('snFile', '/admin/deletePath?type=2');
    $('#dataList').on('click', 'a[name="zip"]', function() {
        var href = this.href;
        $.getJSON(href, function(result) {
            //layer.closeAll();
            if (result.code == 'N00000') {
                layer.msg(result.message + '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' + result.data + '" target="_blank" title="下载">下载文件</a>', {
                    icon: 1,
                    time: 5000
                });
                setTimeout(function() {
                    window.open(result.data);
                }, 5000)
            } else {
                layer.msg(result.message, {
                    icon: 2,
                    time: 1000
                });
            }
        });
        return false;
    });

    $('#zipList').on('click', function() {
        var _url = this.href,
            _ids = $('input[name="selDel"]').length,
            _id = $('input[name="selDel"]:checked'),
            _gid = [];
        if (_id.length <= 0) {
            layer.msg('您还未选择要压缩的数据!', {
                icon: 2,
                time: 1000
            });
            return false;
        }
        if (_ids == _id.length) {
            _url += '&isAll=1';
        } else {
            _id.each(function() {
                var _this = $(this);
                _gid.push(_this.attr('value'));
            });
            _url += _gid.toString();
        }
        layer.confirm('压缩可能要等待几分钟，是否开始？', function(index) {
            $.ajax({
                type: 'get',
                url: _url,
                dataType: 'json',
                success: function(data) {
                    if (data.code == 'N00000') {
                        layer.msg(data.message + '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' + data.data + '" target="_blank" title="下载">下载文件</a>', {
                            icon: 1,
                            time: 5000
                        });
                        setTimeout(function() {
                            window.open(data.data);
                        }, 5000)
                    } else {
                        layer.msg(data.message, {
                            icon: 2,
                            time: 1000
                        });
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg('网络错误!', {
                        icon: 2,
                        time: 1000
                    });
                },
            });
        });
        return false;
    });

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
        var sn = $('#sn').val();
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                sn: sn,
                pageIndex: curr,
                pageSize: CONFIG.pageSize
            },
            success: function(result) {
                var result = JSON.parse(result),
                    data = result.data;
                if (result.code == 'N00000' && data != undefined) {
                    $('#listCount').text(data.totalCount);
                    laypage({
                        cont: $('#page'), //容器。值支持id名、原生dom对象，jquery对象,
                        skip: true, //是否开启跳页
                        //skin: '#AF0000',
                        groups: 6, //连续显示分页数
                        pages: data.totalPages, //总页数
                        curr: curr || 1, //当前页
                        jump: function(obj, first) { //触发分页后的回调
                            if (!first) { //点击跳页触发函数自身，并传递当前页：obj.curr
                                loadDataList(urlString, obj.curr);
                            }
                        }
                    });
                    if (data.data != undefined) {
                        var tableData = data.data;
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
                                "data": "machineId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.sn + '" name="selDel">';
                                }
                            }, {
                                "data": "sn"
                            }, {
                                "render": function(data, type, row, meta) {
                                    return (row.sn === undefined) ? '' : '<a title="查看" href="javascript:;" onclick="pageFullShow (\'日志查看\',\'/admin/logPageList?sn=' + row.sn + '\')">查看</a>';
                                }
                            },{
                                "render": function(data, type, row, meta) {
                                    return (row.sn === undefined) ? '' : '<a title="查看" href="javascript:;" onclick="pageFullShow (\'日志查看\',\'/admin/machineLogPageList?sn=' + row.sn + '\')">查看</a>';
                                }
                            }
                            , {
                                "render": function(data, type, row, meta) {
                                    return (row.sn === undefined) ? '' : '<a title="查看" href="javascript:;" onclick="pageFullShow (\'清扫记录查看\',\'/admin/getPath?sn=' + row.sn + '\')">查看</a>';
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a href=\"/admin/getPathZip?sn=' + row.sn + '\" name=\"zip\" title=\"压缩\" class=\"ml-5\" style=\"text-decoration:none\"><i class=\"Hui-iconfont\">&#xe641;</i></a> <a title=\"删除\" href=\"javascript:;\" onclick=\"delSelect(this,\'/admin/deletePath?type=2&snFile=' + row.sn + '\')\" class=\"ml-5\" style=\"text-decoration:none\"><i class=\"Hui-iconfont\">&#xe6e2;</i></a>';
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