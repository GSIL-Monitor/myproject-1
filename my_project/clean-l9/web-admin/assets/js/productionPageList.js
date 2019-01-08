"use strict";
$(function() {

    loadDataList("/admin/getProductionPageList", 1);
    $('#search').on('click', function() {
        loadDataList("/admin/getProductionPageList", 1);
        return false;
    });//.trigger('click');

    delAllSelect('basicIdList', '/admin/deleteProductionList');
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
        var _data = {},
            companyId = $('#companyId').val(),
            keyword = $('#keyword').val();
        _data.pageIndex = curr;
        _data.pageSize = CONFIG.pageSize;
        if (companyId && companyId != undefined) {
            _data.companyId = companyId;
        }
        if (keyword && keyword != undefined) {
            _data.keyword = keyword;
        }
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': _data,
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
                                "data": "basicInfoId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.basicInfoId + '" name="selDel">';
                                }
                            }, {
                                "data": "companyName",
                                "render": function(data, type, row, meta) {
                                    return '<a title="' + row.companyName + '" href="javascript:;" onclick="pageFullShow (\'产品指南编辑\',\'/admin/editProduction?basicInfoId=' + row.basicInfoId + '\')">' + row.companyName + '</a>';
                                }
                            }, {
                                "data": "lang",
                                "render": function(data, type, row, meta) {
                                    //debugger;
                                     return (row.lang == undefined) ? '韩语' : row.lang;
                                     //console.log(row);
                                }
                            }, {
                                "data": "type",
                                "render": function(data, type, row, meta) {
                                    return (row.type == '1') ? '主页面' : '描述选项';
                                }
                            }, {
                                "data": "description",
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'产品指南编辑\',\'/admin/editProduction?basicInfoId=' + row.basicInfoId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteProduction?basicInfoId=' + row.basicInfoId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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