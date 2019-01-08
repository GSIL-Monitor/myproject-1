"use strict";
$(function() {
    $('#search').on('click', function() {
        loadDataList("/admin/getAdvertisementPageList", 1);
        return false;
    }).trigger('click');
    delAllSelect('advertisementIdList', '/admin/deleteAdvertisementList');
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
        var advertisementPlaceId = $('#advertisementPlaceId').val(),
            keyword = $('#keyword').val();
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                advertisementPlaceId: advertisementPlaceId,
                keyword: keyword,
                pageIndex: curr,
                pageSize: CONFIG.pageSize
            },
            success: function(result) {
                //console.log(result);
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
                        //console.log(tableData);
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
                                "data": "advertisementId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.advertisementId + '" name="selDel">';
                                }
                            }, {
                                "data": "sortId",
                                "render": function(data, type, row, meta) {
                                    return (row.sortId != undefined) ? row.sortId : '';
                                }
                            }, {
                                "data": "title",
                                "render": function(data, type, row, meta) {
                                    return (row.title != undefined) ? row.title : '';
                                }
                            }, {
                                "data": "placeName",
                                "render": function(data, type, row, meta) {
                                    return (row.placeName != undefined) ? row.placeName : '';
                                }
                            },{
                                "data": "fileUrl",
                                "render": function(data, type, row, meta) {
                                    return '<a href="' + row.fileUrl + '" title="查看" target="_blank" style="color: #3c8dbc;">查看</a>';
                                }
                            },{
                                "data": "url",
                                "render": function(data, type, row, meta) {
                                    return '<a href="' + row.url + '" title="查看" target="_blank" style="color: #3c8dbc;">'+ row.url +'</a>';
                                }
                            },{
                                "data": "language",
                                "render": function(data, type, row, meta) {
                                    return (row.language != undefined) ? row.language : '';
                                }
                            },{
                                "render": function(data, type, row, meta) {
                                    //渲染 把数据源中的标题和url组成超链接
                                    return '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'编辑\',\'/admin/editAdvertisement?advertisementId=' + row.advertisementId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteAdvertisementList?advertisementIdList=' + row.advertisementId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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