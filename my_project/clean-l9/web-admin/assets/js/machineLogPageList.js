"use strict";
$(function() {
    $('#search').on('click', function() {
        loadDataList("/admin/getMachineLogPageList", 1);
        return false;
    }).trigger('click');
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
        var sn = $('#sn').val(),
            type = $('#type').val(),
            startDate = $('#startDate').val(),
            endDate = $('#endDate').val();
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                sn: sn,
                type: type,
                startDate: startDate,
                endDate: endDate,
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
                    var tableData = data.data;
                    if (tableData != undefined) {

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
                                "data": "machineLogId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.machineLogId + '" name="selDel">';
                                }
                            }, {
                                "data": "sn",
                                "render": function(data, type, row, meta) {
                                    return (row.sn != undefined) ? row.sn : '';
                                }
                            }, {
                                "data": "url",
                                "render": function(data, type, row, meta) {
                                    return (row.url === undefined) ? '' : '<a title="下载" href="' + row.url + '">下载</a>';
                                }
                            }, {
                                "data": "type",
                                "render": function(data, type, row, meta) {
                                    return (row.type === 1) ? '正常日志' : '崩溃日志';
                                }
                            }, {
                                "data": "uploadTime",
                                "render": function(data, type, row, meta) {
                                    return (row.uploadTime != undefined) ? row.uploadTime : '';
                                }
                            }],
                            "createdRow": function(row, data, index) {
                                $(row).addClass('text-c');
                            }
                        });
                    }else{
                        $('#dataList tbody').html('<tr class="text-c"><td colspan="5">没有数据</td></tr>')
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