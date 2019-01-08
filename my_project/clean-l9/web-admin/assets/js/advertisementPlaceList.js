"use strict";
$(function() {
    loadDataList("/admin/getAdvertisementPlacePageList", 1);
    delAllSelect('advertisementPlaceId', '/admin/advertisementPlaceIdList');
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
       
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
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
                        pages: data.totalPages, //总页数
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
                            "columns": [
                            {
                                "data": "advertisementPlaceId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.advertisementPlaceId + '" name="selDel">';
                                }
                            },{
                                "data": "advertisementPlaceId",
                                "render": function(data, type, row, meta) {
                                    return (row.advertisementPlaceId != undefined) ? row.advertisementPlaceId : '';
                                }
                            },  {
                                "data": "placeName",
                                "render": function(data, type, row, meta) {
                                    return (row.placeName != undefined) ? row.placeName : '';
                                }
                            }, {
                                "data": "flag",
                                "render": function(data, type, row, meta) {
                                    return (row.flag != undefined) ? row.flag : '';
                                }
                            }, {
                                "data": "placeHeight",
                                "render": function(data, type, row, meta) {
                                    return (row.placeHeight != undefined) ? row.placeHeight + "px" : '';
                                }
                            },{
                                "data": "placeWidth",
                                "render": function(data, type, row, meta) {
                                    return (row.placeWidth != undefined) ? row.placeWidth + "px": '';
                                }
                            },{
                                "data": "description",
                                "render": function(data, type, row, meta) {
                                    return (row.description != undefined) ? row.description : '';
                                }
                            },{
                                "render": function(data, type, row, meta) {
                                    //渲染 把数据源中的标题和url组成超链接
                                    return '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'编辑\',\'/admin/editAdvertisementPlace?advertisementPlaceId=' + row.advertisementPlaceId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteAdvertisementPlaceList?advertisementPlaceIdList=' + row.advertisementPlaceId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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
                        time: 8000
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