"use strict";
$(function() {
    $('#search').on('click', function() {
         loadDataList("/admin/getFirmwarePageList", 1);
         return false;
    }).trigger('click');
    loadDataList("/admin/getFirmwarePageList", 1);
    delAllSelect('firmwareIdList', '/admin/deleteFirmwareList');
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
        var searchType = $('#searchType').val(),
            versionCode = $('#version').val();
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                searchType:searchType,
                versionCode:versionCode,
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
                                "data": "firmwareId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.firmwareId + '" name="selDel">';
                                }
                            }, {
                                "data": "companyName"
                            }, {
                                "data": "firmwareName"
                            }, {
                                "data": "versionCode"
                            }, {
                                "data": "displayVersionCode",
                                "render": function(data, type, row, meta) {
                                    return data != undefined ? data : '';
                                }
                            }, {
                                "data": "url",
                                "render": function(data, type, row, meta) {
                                    return '<a title="下载" href="' + row.url + '">' + row.url + '</a>';
                                }
                            }, {
                                "data": "checkCode"
                            }, {
                                "data": "lastUpdate",/*lastUpdate  */
                                "render": function(data, type, row, meta) {
                                    return (row.lastUpdate).toString().replace(/T/g, ' ').replace(/\+[\d]{4}/, '');
                                }
                            }, {
                                "data": "createTime",
                                "render": function(data, type, row, meta) {
                                    return (row.createTime).toString().replace(/T/g, ' ').replace(/\+[\d]{4}/, '');
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'固件编辑\',\'/admin/editFirmware?firmwareId=' + row.firmwareId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteFirmwareList?firmwareIdList=' + row.firmwareId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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