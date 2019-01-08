"use strict";
$(function() {
    $('#search').on('click', function() {
        var url = "/admin/getPath?sn=",
            sn = $('#sn').val();
        location.href = url + sn;
        return false;
    });
    delAllSelect(snIds, '/admin/deletePath?type=' + type + '&sn=' + sn);
    $('#dataList').on('click', 'a[name="zip"]', function() {
        var href = this.href;
        // layer.open({
        //     title: '系统',
        //     content: '正在压缩中，请等待..'
        // });
        $.getJSON(href, function(result) {
            //layer.closeAll();
            if (result.code == 'N00000') {
                layer.msg(result.message + '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' + result.data + '" target="_blank" title="下载">下载文件</a>', {
                    icon: 1,
                    time: 5000
                });
                setTimeout(function() {
                    window.open(result.data);
                }, 5000)
            } else {
                layer.msg(result.message, {
                    icon: 2,
                    time: 1000
                });
            }
        });
        return false;
    }).on('click', 'a[name="mapBulid"]', function() {
        var href = this.href,
            tabindex = $(this).attr('tabindex'),
            title = this.title;
        $.ajax({
            type: 'get',
            url: href,
            dataType: 'json',
            success: function(data) {
                if (data.infoType == 20004) {
                    var d = data.data,
                        w = d.width,
                        h = d.height,
                        resolution = d.resolution,
                        x_min = d.x_min,
                        y_min = d.y_min,
                        posArray = d.posArray,
                        posLen = posArray.length,
                        iLen = $('input[name="selDel"]').length,
                        next = $('#next'),
                        prev = $('#prev');
                    tabindex = parseInt(tabindex);

                    if (tabindex == 1) {
                        prev.hide();
                    } else {
                        prev.show();
                    }
                    if (tabindex == iLen) {
                        next.hide();
                    } else {
                        next.show();
                    }
                    prev.attr({
                        'data-index': tabindex
                    });
                    next.attr({
                        'data-index': tabindex
                    });
                    $('#start').html((new Date(d.start * 1000)).Format("yyyy-MM-dd hh:mm:ss"));
                    $('#end').html((new Date(d.end * 1000)).Format("yyyy-MM-dd hh:mm:ss"));
                    $('#sweep').html(d.sweep);
                    $('#cleanTime').html(parseFloat(d.cleanTime / 60).toFixed(2) + 'min');
                    $('#isDown').html(d.isDown);
                    $('#isError').html(d.isError);
                    drawMap(d.map, w, h, d.lz4_len);

                    var index = layer.open({
                        type: 1,
                        area: [(w + 350) + 'px', 'auto'],
                        //fix: false, //不固定
                        maxmin: true,
                        shade: 0,
                        title: title,
                        content: $('#mapBox'),
                        success: function(layero, index) {
                            $('#range').on('change', function() {
                                var val = 1000 - parseInt(this.value);
                                clearTimeout(timer);
                                drawMap(d.map, w, h, d.lz4_len);
                                //mapCanvasCtx.restore(); //还原状态
                                setTimeout(function() {
                                    drawLine(posArray, h, resolution, x_min, y_min, posLen, val);
                                }, 5);

                            }).prop({
                                'value': 980
                            });
                        },
                        cancel: function() {
                            posArray = [];
                        }
                    });
                    //layer.full(index);
                    drawLine(posArray, h, resolution, x_min, y_min, posLen, 20);
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
        return false;
    });
    $('#prev,#next').on('click', function(e) {
        var elm = $(e.target),
            iLen = $('input[name="selDel"]').length,
            tabindex = $(this).attr('data-index');
        tabindex = parseInt(tabindex);
        if (elm.is('#prev')) {
            tabindex = tabindex - 1;
            tabindex = tabindex >= 1 ? tabindex : 1;
        } else {
            tabindex = tabindex + 1;
            tabindex = tabindex <= iLen ? tabindex : iLen;
        }
        layer.closeAll();
        $('#dataList a[name="mapBulid"]').eq(tabindex - 1).trigger('click');
        return false;
    });
    $('#zipList').on('click', function() {
        var _url = this.href,
            _ids = $('input[name="selDel"]').length,
            _id = $('input[name="selDel"]:checked'),
            _gid = [];
        if (_id.length <= 0) {
            layer.msg('您还未选择要压缩的数据!', {
                icon: 2,
                time: 1000
            });
            return false;
        }
        if (_ids == _id.length) {
            _url += '&isAll=1';
        } else {
            _id.each(function() {
                var _this = $(this);
                _gid.push(_this.attr('value'));
            });
            _url += _gid.toString();
        }
        layer.confirm('压缩可能要等待几分钟，是否开始？', function(index) {
            $.ajax({
                type: 'get',
                url: _url,
                dataType: 'json',
                success: function(data) {
                    if (data.code == 'N00000') {
                        layer.msg(data.message + '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' + data.data + '" target="_blank" title="下载">下载文件</a>', {
                            icon: 1,
                            time: 5000
                        });
                        setTimeout(function() {
                            window.open(data.data);
                        }, 5000)
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
    });
});

//构建地图
var Buffer = require('buffer').Buffer,
    LZ4 = require('lz4'),
    uncompressed = new Buffer(1024 * 1024),
    uncompressedSize = 0;
var mapCanvas = $('#map')[0],
    mapCanvasCtx = mapCanvas.getContext("2d");

/**
 * [drawMap 解析数据]
 * @param  {[type]} ab       [数据]
 * @param  {[type]} w       [width]
 * @param  {[type]} h       [height]
 * @param  {[type]} lz4_len [校验数据长度]
 */
function drawMap(ab, w, h, lz4_len) {
    var _buf = new Buffer(ab, 'base64');
    uncompressedSize = LZ4.decodeBlock(_buf, uncompressed);
    if (lz4_len === _buf.byteLength) {
        mapCanvas.width = w;
        mapCanvas.height = h;
        //获取地图数据
        mapCanvasCtx.clearRect(w, h, w, h);
        var imgData = mapCanvasCtx.getImageData(0, 0, w, h);
        //填充图片
        if (uncompressedSize > 0) {
            for (var i = uncompressedSize; i >= 0; i--) {
                imgData.data[4 * i] = uncompressed[i];
                imgData.data[4 * i + 1] = uncompressed[i];
                imgData.data[4 * i + 2] = uncompressed[i];
                imgData.data[4 * i + 3] = 0xff;
            }
        }
        //填充路径
        mapCanvasCtx.putImageData(imgData, 0, 0);
        var newImgData = mapCanvasCtx.getImageData(0, 0, w, h);
        mapCanvasCtx.putImageData(imageDataVRevert(newImgData, imgData), 0, 0);
        mapCanvasCtx.save(); //保存当前状态
    }
}
//画线
var timer = null;

function drawLine(posArray, h, resolution, x_min, y_min, posLen, speedTime) {
    //mapCanvasCtx.restore();//还原状态
    mapCanvasCtx.strokeStyle = "#FF0000";
    speedTime = (speedTime == '' || speedTime == undefined) ? 20 : speedTime;
    mapCanvasCtx.beginPath();
    for (var i = posLen; i >=0 ; i--) {
        (function(j) {
            timer = setTimeout(function() {
                var arr = trangeXY(posArray[j][0], posArray[j][1], x_min, y_min, resolution, h);
                if (j == 0) {
                    mapCanvasCtx.moveTo(arr[0], arr[1]);
                }
                mapCanvasCtx.lineTo(arr[0], arr[1]);
                mapCanvasCtx.stroke();
            }, speedTime);
        })(i);
    }
    mapCanvasCtx.closePath();
}
//竖向像素反转
function imageDataVRevert(sourceData, newData) {
    for (var i = 0, h = sourceData.height; i < h; i++) {
        for (var j = 0, w = sourceData.width; j < w; j++) {
            newData.data[i * w * 4 + j * 4 + 0] = sourceData.data[(h - i) * w * 4 + j * 4 + 0];
            newData.data[i * w * 4 + j * 4 + 1] = sourceData.data[(h - i) * w * 4 + j * 4 + 1];
            newData.data[i * w * 4 + j * 4 + 2] = sourceData.data[(h - i) * w * 4 + j * 4 + 2];
            newData.data[i * w * 4 + j * 4 + 3] = sourceData.data[(h - i) * w * 4 + j * 4 + 3];
        }
    }
    return newData;
}
//横向像素反转
function imageDataHRevert(sourceData, newData) {
    for (var i = 0, h = sourceData.height; i < h; i++) {
        for (j = 0, w = sourceData.width; j < w; j++) {
            newData.data[i * w * 4 + j * 4 + 0] = sourceData.data[i * w * 4 + (w - j) * 4 + 0];
            newData.data[i * w * 4 + j * 4 + 1] = sourceData.data[i * w * 4 + (w - j) * 4 + 1];
            newData.data[i * w * 4 + j * 4 + 2] = sourceData.data[i * w * 4 + (w - j) * 4 + 2];
            newData.data[i * w * 4 + j * 4 + 3] = sourceData.data[i * w * 4 + (w - j) * 4 + 3];
        }
    }
    return newData;
}

/**
 * [trangeXY 翻转XY轴]
 * @param  {[type]} x          [原X]
 * @param  {[type]} y          [原Y]
 * @param  {[type]} x_min      [x_min]
 * @param  {[type]} y_min      [y_min]
 * @param  {[type]} resolution [resolution]
 * @param  {[type]} h          [height]
 */
function trangeXY(x, y, x_min, y_min, resolution, h) {
    var _x = (x / 1000.0 - x_min) / resolution,
        _y = (y / 1000.0 - y_min) / resolution;
    _y = h - _y;
    return [_x, _y];
}
//time format
Date.prototype.Format = function(fmt) { //author: meizz
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}