"use strict";
$(function() {
    $('#search').on('click', function() {
        loadDataList("/admin/getUserPageList", 1);
        return false;
    }).trigger('click');
    delAllSelect('userIdList', '/admin/deleteUserList');
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
            userAppVersion = $('#userAppVersion').val(),
            companyId = $('#companyId').val(),
            sn = $('#sn').val(),
            searchType = 0;

        if (!keyWord) {
            searchType = 0;
        }

        if (keyWord && (!isPhone(keyWord) && !isMail(keyWord))) {
            searchType = 3;
        }
        if (keyWord && isPhone(keyWord)) {
            searchType = 1;
        }
        if (keyWord && isMail(keyWord)) {
            searchType = 2;
        }


        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                'startDate': startDate,
                'endDate': endDate,
                'keyWord': keyWord,
                'companyId': companyId,
                'searchType': searchType,
                'userAppVersion': userAppVersion,
                'sn': sn,
                'pageIndex': curr,
                'pageSize': CONFIG.pageSize
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
                        //debugger;
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
                                "data": "userId",
                                "render": function(data, type, row, meta) {
                                    return '<input type="checkbox" value="' + row.userId + '" name="selDel">';
                                }
                            }, {
                                "data": "companyName",
                                "render": function(data, type, row, meta) {
                                    return (row.companyName === undefined) ? '' : row.companyName;
                                }
                            }, {
                                "data": "avatar",
                                "render": function(data, type, row, meta) {
                                    return (row.avatar === undefined || !row.avatar) ? '' : '<img src="' + row.avatar + '" alt="' + row.userName + '" style="height:32px;">';
                                }
                            }, {
                                "data": "userName",
                                "render": function(data, type, row, meta) {
                                    return (row.userName === undefined || !row.userName) ? '' : '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'会员编辑\',\'/admin/editUser?userId=' + row.userId + '\')" class="ml-5" style="text-decoration:none">' + row.userName + '</a>';
                                }
                            }, {
                                "data": "email",
                                "render": function(data, type, row, meta) {
                                    return (row.email === undefined) ? '' : row.email;
                                }
                            }, {
                                "data": "phone",
                                "render": function(data, type, row, meta) {
                                    return (row.phone === undefined) ? '' : row.phone;
                                }
                            }, {
                                "data": "nowSn",
                                "render": function(data, type, row, meta) {
                                    return (row.nowSn === undefined) ? '' : row.nowSn;
                                }
                            },{
                                "data": "userAppVersion",
                                "render": function(data, type, row, meta) {
                                    return (row.userAppVersion === undefined) ? '' : row.userAppVersion;
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return (row.userId === undefined) ? '' : '<a title="查看" href="javascript:;" onclick="userDecShow (\'设备列表查看\',\'/admin/getUserMachinePageList?pageSize=100&userId=' + row.userId + '\')">查看</a>';
                                }
                            }, { //添加app是否在线 @anthor myf
                                "data": "userName",
                                "render": function(data, type, row, meta) {
                                    return (row.userName === undefined) ? '' : '<a title="查看" href="javascript:;" onclick="userNameShow (\'APP状态查看\',\'/admin/getUserOrMachineIsOnline?type=2&userName=' + encodeURIComponent(row.userName) + '\')">查看</a>';
                                }
                            }, {
                                "data": "createTime",
                                "render": function(data, type, row, meta) {
                                    return (row.createTime === undefined) ? '' : (row.createTime).toString().replace(/T/g, ' ').replace(/\+[\d]{4}/, '');
                                }
                            }, {
                                "render": function(data, type, row, meta) {
                                    return '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'会员编辑\',\'/admin/editUser?userId=' + row.userId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteUserList?userIdList=' + row.userId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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

function userDecShow(title, url, elms) { //设备列表查看信息
    // debugger;
    var userId = url.split('&userId=')[1];
    $.ajax({
        type: 'get',
        url: url,
        success: function(result) {
            var result = JSON.parse(result);
            if (result.code == 'N00000') {
                var html = '<div class="cl pd-5 bg-1 bk-gray"> <span class="l"> <a name="addUserMachine" href="javascript:;" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加机器</a></span> </div>',
                    data = result.data,
                    addTb = '';
                addTb += '<article class="page-container" style="display:none;" id="addTb">';
                addTb += '    <form class="form form-horizontal" id="form" action="/admin/addUserMachine?userId=' + userId + '" method="post" data-href="' + url + '">';
                addTb += '        <div class="row cl">';
                addTb += '            <label class="form-label col-xs-4 col-sm-2">类型：</label>';
                addTb += '            <div class="formControls col-xs-8 col-sm-9">';
                addTb += '                <select name="userType" id="userType">';
                addTb += '                    <option value="1">主人</option>';
                addTb += '                    <option value="2">共享</option>';
                addTb += '                </select>';
                addTb += '            </div>';
                addTb += '        </div>';
                addTb += '        <div class="row cl">';
                addTb += '            <label class="form-label col-xs-4 col-sm-2">机器SN：</label>';
                addTb += '            <div class="formControls col-xs-8 col-sm-9"> ';
                addTb += '                <input type="text" class="input-text" value="" placeholder="机器SN" id="csn" name="csn">';
                addTb += '            </div>';
                addTb += '        </div>';
                addTb += '        <div class="row cl">';
                addTb += '            <label class="form-label col-xs-4 col-sm-2">用户别名：</label>';
                addTb += '            <div class="formControls col-xs-8 col-sm-9">';
                addTb += '                <input type="text" class="input-text" value="" placeholder="用户别名" id="noteName" name="noteName">';
                addTb += '            </div>';
                addTb += '        </div>';
                addTb += '        <div class="row cl">';
                addTb += '            <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-2">';
                addTb += '                <button class="btn btn-primary radius" id="submit" type="submit"><i class="Hui-iconfont">&#xe632;</i> 保存</button>';
                addTb += '                <button id="reset" class="btn btn-default radius" type="button">&nbsp;&nbsp;取消&nbsp;&nbsp;</button>';
                addTb += '            </div>';
                addTb += '        </div>';
                addTb += '    </form>';
                addTb += '</article>';
                html += '<table class="table table-border table-bordered table-bg" data-href="' + url + '">';
                html += '    <thead>';
                html += '        <tr class="text-c">';
                html += '            <th>设备别名</th>';
                html += '            <th>设备SN</th>';
                html += '            <th>类型</th>';
                html += '            <th>共享用户别名</th>';
                html += '            <th>操作</th>';
                html += '        </tr>';
                html += '    </thead>';
                html += '    <tbody>';
                if (data.totalCount <= 0) {
                    html += '        <tr class="text-c"><td scope="col" colspan="5">没有数据</td></tr>';
                } else {
                    var tb = data.data,
                        len = tb.length;
                    for (var i = 0; i < len; i++) {
                        html += '        <tr class="text-c">';
                        html += '            <td>' + ((tb[i].machineName != undefined) ? tb[i].machineName : '') + '</td>';
                        html += '            <td>' + tb[i].sn + '</td>';
                        html += '            <td>' + ((tb[i].userType == 1) ? '主人' : '共享') + '</td>';
                        html += '            <td>' + ((tb[i].noteName != undefined) ? tb[i].noteName : '') + '</td>';
                        html += '            <td><a name="deleteUserMachine" title="解除绑定" href="/admin/deleteUserMachine?&sn=' + tb[i].sn + '&userId=' + tb[i].userId + '&userType=' + tb[i].userType + '&userMachineId=' + tb[i].userMachineId + '" class="ml-5" style="text-decoration:none">解除绑定</a>';
                        if (tb[i].userType == 2) {
                            html += '<a name="editUserUp" title="设为主人" href="/admin/editUserUp?&sn=' + tb[i].sn + '&userId=' + tb[i].userId + '&userType=' + tb[i].userType + '" class="ml-5" style="text-decoration:none">设为主人</a>';
                        }
                        html += '            </td>';
                        html += '        </tr>';
                    }
                }
                html += '    </tbody>';
                html += '</table>';
                html += addTb;
                if (elms == undefined) {
                    layer.open({
                        type: 1,
                        area: ['800px', '510px'],
                        fix: false, //不固定
                        maxmin: true,
                        shade: 0.4,
                        title: title,
                        content: html
                    });
                } else {
                    elms.html(html);
                }
            } else {
                layer.msg(data.message, {
                    icon: 2,
                    time: 1000
                });
            }
        },
        error: function(XmlHttpRequest, textStatus, errorThrown) {
            layer.msg('网络错误!', {
                icon: 2,
                time: 1000
            });
        }
    });
    return false;
}

function userNameShow(title, url, elms) { // APP状态查看
    //# var username = url.split('&userName=')[1];
    //# debugger;
    $.ajax({
        type: 'get',
        url: url,
        //# data:{username:username},
        success: function(result) {
            var result = JSON.parse(result);
            // console.log(result);
            if (result.code == 'N00000') {
                var data = result.data;
                data === 0 ?
                    layer.msg('不在线', { // 找不到该用户
                        icon: 2,
                        time: 3000
                    }) : layer.msg('在线', { // 该用户在线
                        icon: 1,
                        time: 3000
                    })
            }
        }
    });
}
var dis = false;
$(document).on('click', 'a[name="deleteUserMachine"],a[name="editUserUp"]', function() {
    var href = this.href,
        _this = $(this),
        tb = _this.parents('table'),
        layContent = tb.parent(),
        url = tb.attr('data-href');
    $.getJSON(href, function(result) {
        var icon = 2;
        if (result.code == 'N00000') {
            icon = 1;
            userDecShow('', url, layContent);
        }
        layer.msg(result.message, {
            icon: icon,
            time: 1000
        });

    });
    return false;
}).on('click', 'a[name="addUserMachine"]', function() {
    $('#addTb').show();
    return false;
}).on('click', '#reset', function() {
    $('#addTb').hide();
    return false;
}).on('submit', '#form', function() {
    var _this = $(this),
        url = _this.attr('action'),
        href = _this.attr('data-href'),
        layContent = $('.layui-layer-content'),
        sn = $('#csn').val(),
        noteName = $('#noteName').val(),
        userType = $('#userType').val();
    if (dis) {
        return false;
    }
    if (!sn) {
        layer.msg('机器SN必填', {
            icon: 2,
            time: 1000
        });
        return false;
    }
    dis = true;
    $.ajax({
        type: 'post',
        data: {
            'sn': sn,
            'noteName': noteName,
            'userType': userType
        },
        url: url,
        success: function(data) {
            var data = JSON.parse(data);
            if (data.code == 'N00000') {
                layer.msg('添加成功!', {
                    icon: 1,
                    time: 1000
                });
                $('#addTb').hide();
                userDecShow('', href, layContent);
                setTimeout(function() {
                    $('#search').trigger('click');
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
    return false;
});