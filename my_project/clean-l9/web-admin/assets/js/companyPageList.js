"use strict";
$(function() {
    loadDataList("/admin/getCompanyPageList", 1);
    delAllSelect('companyIdList', '/admin/deleteCompanyList');
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
                                "data": "companyId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.companyId + '" name="selDel">';
                                }
                            }, {
                                "data": "companyId"
                            }, {
                                "data": "companyName"
                            }, {
                                "data": "createTime",
                                "render": function(data, type, row, meta) {
                                    return (row.createTime).toString().replace(/T/g, ' ').replace(/\+[\d]{4}/, '');
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="编辑" href="javascript:;" onclick="companyModeShow (\'公司编辑\',\'/admin/editCompanySubmit?companyId=' + row.companyId + '\',\'' + row.companyName + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteCompanyList?companyIdList=' + row.companyId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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
function companyModeShow(title, url, str) {
    var companyName = $('#companyName');
    companyName.val(str);
    layer.open({
        type: 1,
        area: ['800px', '310px'],
        fix: false, //不固定
        maxmin: true,
        shade: 0.4,
        title: title,
        content: $('#addCompany'),
        success: function(layero,index) {            
            $('#reset').on('click', function() {
                layer.close(index);
                return false;
            });
            $("#form").validate({
                rules: {
                    companyName: {
                        required: true
                    }
                },
                onkeyup: false,
                focusCleanup: true,
                success: "valid",
                submitHandler: function(form) {
                    var val = companyName.val();
                    if (dis) {
                        return false;
                    }
                    dis = true;
                    $.ajax({
                        type: 'post',
                        data: {
                            'companyName': val,
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