"use strict";
$(function() {
    var _bool = false;

    $("#form").validate({
        rules: {
            title: {
                required: true
            },
            advertisementPlaceId: {
                required: true
            },
            fileUrl: {
                required: true
            }
        },
        onkeyup: false,
        focusCleanup: true,
        success: "valid",
        submitHandler: function(form) {
            // $(form).ajaxSubmit();
        var title = $('#title').val(),
          advertisementPlaceId = $('#advertisementPlaceId').val(),
          fileUrl = $('#fileUrl').val(),
          language = $('#language').val(),
          //advertisementUrl = $('#advertisementUrl').val(),
          url = $('#url').val(),
          sortId = $('#sortId').val(),
          description = $('#description').val();
            if (_bool) {
                return false;
            }
            _bool = true;
            $.ajax({
                type: 'post',
                data: {
                    title:title,
                    advertisementPlaceId:advertisementPlaceId,
                    fileUrl:fileUrl,
                    language:language,
                    //advertisementUrl:advertisementUrl,
                    url:url,
                    sortId:sortId,
                    description:description
                },
                url: "/admin/addAdvertisementSubmit",
                success: function(data) {
                    var data = JSON.parse(data);
                    if (data.code == 'N00000') {
                        layer.msg('保存成功!', {
                            icon: 1,
                            time: 1000
                        });
                        window.parent.location.reload();
                        // $('#form', window.parent.document).find('.btn-refresh').click();
                        // setTimeout(function() {
                        //     $('#reset').trigger('click');
                        // }, 2000);
                    } else {
                        layer.msg(data.message, {
                            icon: 2,
                            time: 2000
                        });
                    }
                    _bool = false;
                },
                error: function(XmlHttpRequest, textStatus, errorThrown) {
                    layer.msg('网络错误!', {
                        icon: 2,
                        time: 2000
                    });
                    _bool = false;
                }
            });
        }
    });

    // $('#reset').on('click', function() {
    //     layer_close();
    //     return false;
    // });

    var _bool = false;
     (function() {
       $('#fileUrl').on('click', function(e) {
         var _e = e.target.files;
         if (!(_bool && _e)) {
           $('#advertisementFile').click();
           _bool = true;
         }
       });
       $('#advertisementFile').on('change', function() {

         var _fileName = $('#advertisementFile').val();
         var _flg = matchFileEx(_fileName, 'gif|jpg|jpeg|png');
         if (!_flg) {
           _bool = false;
           layer.msg('附件上传失败，原因可能：文件格式不正确！');
         } else {
           _bool = true;
           $('#myForm').submit();
         }
       });
       var _bar = $('#bar');
       $('#myForm').ajaxForm({

         beforeSend: function() {
           _bool = true;
         },
         uploadProgress: function(event, position, total, percentComplete) {
           var percentVal = '上传完成:' + percentComplete + '%';
           // _bar.width(percentVal)
           _bar.html(percentVal);
         },
         success: function() {
           var percentVal = '上传完成:' + '100%';
           // _bar.width(percentVal)
           _bar.html(percentVal);
           _bool = false;
         },
         complete: function(xhr) {
           var result = $.parseJSON(xhr.responseText);
           if (result.code == 'N00000') {
             $('#fileUrl').val(result.data);
             layer.msg('附件上传成功!');
           } else {             
             layer.msg('附件上传失败,原因可能:' + result.message);
           }
           _bar.html('');           
           _bool = false;
         }
       });
     })();
   
/*
@param File 文件名
@param Ex 后缀名 
@matchFileEx('api.jpg','gif|jpg|jpeg|png')
 */
function matchFileEx(File, Ex) {
  var _File = File;
  var _Ex = Ex;
  if (!_Ex) {
    return true;
  }
  if (!_File) {
    return false;
  }
  _File = _File.toLowerCase();
  var _flg = _File.match(eval('/\.+(' + _Ex + ')$/i'));
  //console.log('flg:' + _flg);
  if (_flg != null && _flg != undefined) {
    return true;
  } else {
    return false;
  }
}
});