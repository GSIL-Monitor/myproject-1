"use strict";
$(function() {
    $('#seach').on('click', function() {
        loadDataList("/admin/getSystemMessagePageList", 1);
        return false;
    }).trigger('click');
    delAllSelect('systemMessageIdList', '/admin/deleteSystemMessageList');
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
        var startDate = $('#startDate').val(),
            endDate = $('#endDate').val(),
            keyWord = $('#keyWord').val(),
            type = $('#type').val(),
            companyId = $('#companyId').val();
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                startDate: startDate,
                endDate: endDate,
                userName: keyWord,
                companyId: companyId,
                type: type,
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
                                "data": "systemMessageId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.systemMessageId + '" name="selDel">';
                                }
                            }, {
                                "data": "companyName"
                            }, {
                                "data": "messageType",
                                "render": function(data, type, row, meta) {
                                    var html = '',
                                        messageType = row.messageType;
                                    if (messageType != undefined) {
                                        html = messageType == 1 ? '纯文字' : '图文链接'
                                    }
                                    return html;
                                }
                            }, {
                                "data": "title"
                            }, {
                                "data": "messageContent",
                                "render": function(data, type, row, meta) {
                                    var html = '',
                                        messageType = row.messageType,
                                        messageContent = row.messageContent;
                                    if (messageContent != undefined) {
                                        html = messageType == 1 ? messageContent : '<a title="查看" href="javascript:;" onclick="pageShow (\'消息编辑\',\'' + messageContent + '\',\'\',\'510\')" class="ml-5" style="text-decoration:none">' + messageContent + '</a>';
                                    }
                                    return html;
                                }
                            }, {
                                "data": "userName",
                                "render": function(data, type, row, meta) {
                                    return (row.userName === undefined) ? '' : row.userName;
                                }

                            }, {
                                "data": "createTime",
                                "render": function(data, type, row, meta) {
                                    return (row.createTime === undefined) ? '' : (row.createTime).toString().replace(/T/g, ' ').replace(/\+[\d]{4}/, '');
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'消息编辑\',\'/admin/editSystemMessage?systemMessageId=' + row.systemMessageId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteSystemMessageList?systemMessageIdList=' + row.systemMessageId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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