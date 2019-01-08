"use strict";
$(function() {
    $('#search').on('click', function() {
        loadDataList("/admin/getMachinePageList", 1);
        return false;
    }).trigger('click');
    delAllSelect('machineIdList', '/admin/deleteMachineList');
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
            startDate = $('#startDate').val(),
            endDate = $('#endDate').val(),
            searchType = $('#searchType').val(),
            version = $('#version').val(),
            companyId = $('#companyId').val();
        $.ajax({
            'type': 'POST',
            'url': urlString,
            'data': {
                startDate: startDate,
                endDate: endDate,
                sn: sn,
                companyId: companyId,
                searchType:searchType,
                version:version,
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
                                    return '<input type="checkbox" value="' + row.machineId + '" name="selDel">';
                                }
                            }, {
                                "data": "sn"
                            }, {
                                "data": "machineName",
                                "render": function(data, type, row, meta) {
                                    return (row.machineName != undefined) ? row.machineName : '';
                                }
                            }, {
                                "data": "version",
                                "render": function(data, type, row, meta) {
                                    return (row.version != undefined) ? row.version : '';
                                }
                            }, {
                                "data": "hardware",
                                "render": function(data, type, row, meta) {
                                    return (row.hardware != undefined) ? row.hardware : '';
                                }
                            }, {
                                "data": "companyName"
                            }, {
                                "render": function(data, type, row, meta) {
                                    return (row.sn === undefined) ? '' : '<a title="查看" href="javascript:;" onclick="userDecShow (\'用户列表查看\',\'/admin/getMachineUserList?pageSize=100&sn=' + row.sn + '\')">查看</a>';
                                }
                            }, {
                                "data": "sn",
                                "render": function(data, type, row, meta) {
                                    return (row.sn === undefined) ? '' : '<a title="查看" href="javascript:;" onclick="DecInfoShow (\'机器状态查看\',\'/admin/getUserOrMachineIsOnline?type=1&sn=' + row.sn + '\')">查看</a>';
                                }
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
                                    return '<a title="编辑" href="javascript:;" onclick="pageFullShow (\'机器编辑\',\'/admin/editMachine?machineId=' + row.machineId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> <a title="删除" href="javascript:;" onclick="delSelect(this,\'/admin/deleteMachineList?machineIdList=' + row.machineId + '\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
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

function userDecShow(title, url, elms) {
    var sn = url.split('&sn=')[1];
    $.ajax({
        type: 'post',
        url: url,
        success: function(result) {
            var result = JSON.parse(result);
            if (result.code == 'N00000') {
                var html = '<div class="cl pd-5 bg-1 bk-gray"> <span class="l"> <a name="addUserMachine" href="javascript:;" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加用户</a></span> </div>',
                    data = result.data,
                    addTb = '';
                addTb += '<article class="page-container" style="display:none;" id="addTb">';
                addTb += '    <form class="form form-horizontal" id="form" action="/admin/addUserMachine?sn=' + sn + '" method="post" data-href="' + url + '">';
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
                addTb += '            <label class="form-label col-xs-4 col-sm-2">用户名：</label>';
                addTb += '            <div class="formControls col-xs-8 col-sm-9"> ';
                addTb += '                <input type="text" class="input-text" value="" placeholder="用户名/邮箱/手机（如+86 13800888000）" id="userName" name="userName">';
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
                // html += '            <th>设备别名</th>';
                html += '            <th>用户名</th>';
                html += '            <th>类型</th>';
                html += '            <th>共享用户别名</th>';
                html += '            <th>操作</th>';
                html += '        </tr>';
                html += '    </thead>';
                html += '    <tbody>';
                if (data == undefined) {
                    html += '        <tr class="text-c"><td scope="col" colspan="4">没有数据</td></tr>';
                } else {
                    var len = data.length;
                    for (var i = 0; i < len; i++) {
                        html += '        <tr class="text-c">';
                        // html += '            <td>' + ((data[i].machineName != undefined) ? data[i].machineName : '') + '</td>';
                        html += '            <td>' + data[i].userName + '</td>';
                        html += '            <td>' + ((data[i].userType == 1) ? '主人' : '共享') + '</td>';
                        html += '            <td>' + ((data[i].noteName != undefined) ? data[i].noteName : '') + '</td>';
                        html += '            <td><a name="deleteUserMachine" title="解除绑定" href="/admin/deleteUserMachine?&sn=' + data[i].sn + '&userId=' + data[i].userId + '&userType=' + data[i].userType + '&userMachineId=' + data[i].userMachineId + '" class="ml-5" style="text-decoration:none">解除绑定</a>';
                        if (data[i].userType == 2) {
                            html += '<a name="editUserUp" title="设为主人" href="/admin/editUserUp?&sn=' + data[i].sn + '&userId=' + data[i].userId + '&userType=' + data[i].userType + '" class="ml-5" style="text-decoration:none">设为主人</a>';
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

function DecInfoShow(title, url, elms) {
    $.ajax({
        type: 'get',
        url: url,
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
        _data = {},
        url = _this.attr('action'),
        href = _this.attr('data-href'),
        layContent = $('.layui-layer-content'),
        userName = $('#userName').val(),
        noteName = $('#noteName').val(),
        userType = $('#userType').val();
    if (dis) {
        return false;
    }
    if (!userName) {
        layer.msg('用户名必填', {
            icon: 2,
            time: 1000
        });
        return false;
    }
    if (userName && (!isPhone(userName) && !isMail(userName))) {
        _data.userName = userName;
    }
    if (userName && isPhone(userName)) {
        _data.phone = userName;
    }
    if (userName && isMail(userName)) {
        _data.mail = userName;
    }
    _data.noteName = noteName;
    _data.userType = userType;
    dis = true;
    $.ajax({
        type: 'post',
        data: _data,
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