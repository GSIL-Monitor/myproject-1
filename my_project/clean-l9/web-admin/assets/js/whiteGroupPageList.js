"use strict";
$(function() {
    loadDataList("/admin/getWhiteGroupPageList", 1);
    delAllSelect('whiteGroupIdList', '/admin/deleteWhiteGroupList');
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
                                "data": "whiteGroupId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.whiteGroupId + '" name="selDel">';
                                }
                            }, {
                                "data": "groupName"
                            }, {
                                "data": "sortId"
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="管理" href="/admin/whiteGroupSnPageList?whiteGroupId=' + row.whiteGroupId + '&whiteGroupName=' + row.groupName + '" class="ml-5">管理</a>';
                                }
                            }, {
                                "data": "createTime",
                                "render": function(data, type, row, meta) {
                                    return (row.createTime).toString().replace(/T/g, ' ').replace(/\+[\d]{4}/, '');
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="编辑" href="javascript:;" onclick="snsModeShow(\'编辑\',\'/admin/editWhiteGroupSubmit?whiteGroupId=' + row.whiteGroupId + '\',\'' + row.groupName + '\',\'' + row.sortId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteWhiteGroupList?whiteGroupIdList=' + row.whiteGroupId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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
    var groupName1 = $('#groupName'),
        sortId1 = $('#sortId');
    groupName1.val(str);
    sortId1.val(val);
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
                    groupName: {
                        required: true
                    },
                    sortId: {
                        required: true
                    }
                },
                onkeyup: false,
                focusCleanup: true,
                success: "valid",
                submitHandler: function(form) {
                    var groupName = groupName1.val(),
                        sortId = sortId1.val();
                    if (dis) {
                        return false;
                    }
                    dis = true;
                    $.ajax({
                        type: 'post',
                        data: {
                            'groupName': groupName,
                            'sortId': sortId,
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