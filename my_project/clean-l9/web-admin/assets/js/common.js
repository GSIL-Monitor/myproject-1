/**
 * [isPhone description]
 * @param  {[type]}  str [description]
 * @return {Boolean}     [description]
 */
function isPhone(str) {
    var tel = /(\+)([\d]*)(\s)([\d]*)/g;
    return (tel.test(str) && str.match(tel).length == 1) ? true : false;
}
/**
 * [isMail description]
 * @param  {[type]}  str [description]
 * @return {Boolean}     [description]
 */
function isMail(str) {
    var mail = /[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/gi;
    return (mail.test(str) && str.match(mail).length == 1) ? true : false;
}
/*pageFullShow*/
/**
 * [pageFullShow description]
 * @param  {[type]} title [标题]
 * @param  {[type]} url   [请求的url]
 */
function pageFullShow(title, url) {
    var index = layer.open({
        type: 2,
        title: title,
        content: url
    });
    layer.full(index);
}

/*pageShow*/
/**
 * [pageShow description]
 * @param  {[type]} title [标题]
 * @param  {[type]} url   [请求的url]
 * @param  {[type]} id    [需要操作的数据id]
 * @param  {[type]} w     [弹出层宽度（缺省调默认值）]
 * @return {[type]} h     [弹出层高度（缺省调默认值）]
 */
function pageShow(title, url, w, h) {
    layer_show(title, url, w, h);
}

/**
 * [delList description]
 * @param  {[type]} obj [description]
 * @param  {[type]} url [description]
 */
function delSelect(obj, url) {
    layer.confirm('确认要删除吗？', function(index) {
        $.ajax({
            type: 'get',
            url: url,
            dataType: 'json',
            success: function(data) {
                if (data.code == 'N00000') {
                    $(obj).parents("tr").remove();
                    layer.msg('已删除!', {
                        icon: 1,
                        time: 1000
                    });
                } else {
                    layer.msg(data.message, {
                        icon: 2,
                        time: 1000
                    });
                }

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('网络错误!', {
                    icon: 2,
                    time: 1000
                });
            },
        });
    });
    return false;
}

/**
 * [delAllSelect description]
 * @param  {[type]} ids [description]
 * @param  {[type]} url [description]
 */
function delAllSelect(param, url) {
    $('#delAllList').on('click', function() {
        var _this = $(this),
            _id = $('input[name="selDel"]:checked'),
            _gid = [],
            _pid = [];
        if (_id.length <= 0) {
            layer.msg('您还未选择要删除的数据!', {
                icon: 2,
                time: 1000
            });
            return false;
        }
        _id.each(function() {
            var _this = $(this);
            _gid.push(_this.attr('value'));
            _pid.push(_this.closest('tr').index());
        });

        url += (url.indexOf('?') > -1 ? '&' : '?') + param + '=' + _gid.toString();
        layer.confirm('确认要删除吗？', function(index) {
            $.ajax({
                type: 'get',
                url: url,
                dataType: 'json',
                success: function(data) {
                    if (data.code == 'N00000') {
                        for (var i = _pid.length - 1; i > -1; i--) {
                            $('#dataList tbody tr').eq(_pid[i]).remove();
                        }
                        layer.msg('删除成功!', {
                            icon: 1,
                            time: 1000
                        });
                    } else {
                        layer.msg(data.message, {
                            icon: 2,
                            time: 1000
                        });
                    }

                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    layer.msg('网络错误!', {
                        icon: 2,
                        time: 1000
                    });
                    console.log(XMLHttpRequest, textStatus, errorThrown);
                },
            });
        });
        return false;

    });
}
//全选
$('.table').on('click', 'input[name=checkAll]', function() { //全选
    var checked = $(this).prop('checked');
    $('input[name=selDel]').prop({
        'checked': checked
    });
}).on('click', 'input[name=selDel]', function() { //全选
    var len = $('.table').find('input[name=selDel]').length,
        isCheck = $('.table').find('input[name=selDel]:checked').length,
        f = (len == isCheck) ? true : false;
    $('input[name=checkAll]').prop({
        'checked': f
    })
});
//密码可见
$('input[type=password]').on('keyup', function() {
    var v = this.value,
        pShow = $('#p-show'),
        _this = $(this);
    pShow.size() > 0 ? pShow.text(v) : _this.after('<label id="j-label" for="password" style="position: absolute; top:-64px;left:15px;z-index: 1000000; background: #5eb95e;color: #000;font-size: 40px;padding:0 15px;"><strong id="p-show">' + v + '</strong></label>');
}).on('blur', function() {
    $('#j-label').remove();
}).on('focus', function() {
    var v = this.value,
        _this = $(this);
    _this.after('<label id="j-label" for="password" style="position: absolute; top:-64px;left:15px;z-index: 1000000; background: #5eb95e;color: #000;font-size: 40px;padding:0 15px;"><strong id="p-show">' + v + '</strong></label>');
});
/*
CONFIG
*/
var CONFIG = {
    'pageSize': 20
}