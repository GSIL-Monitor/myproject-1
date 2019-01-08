$(function() {
    if (window.top != window.self) {
        window.top.location = "/admin/login";
    }
    $("#min_title_list li").contextMenu('Huiadminmenu', {
        bindings: {
            'closethis': function(t) {
                console.log(t);
                if (t.find("i")) {
                    t.find("i").trigger("click");
                }
            },
            'closeall': function(t) {
                //console.log(t.id);
            },
        }
    });
});

var dis = false;

function pwdEditShow(title, url) {
    var password = $('#password'),
        password1 = $('#password1'),
        password2 = $('#password2');

    password.val('');
    password1.val('');
    password2.val('');
    layer.open({
        type: 1,
        area: ['800px', '310px'],
        fix: false, //不固定
        maxmin: true,
        shade: 0.4,
        title: title,
        content: $('#editPwd'),
        success: function(layero, index) {
            $('#reset').on('click', function() {
                layer.close(index);
                return false;
            });
            $("#form").validate({
                rules: {
                    password: {
                        required: true,
                        minlength: 6,
                        maxlength: 16
                    },
                    password1: {
                        required: true,
                        minlength: 6,
                        maxlength: 16
                    },
                    password2: {
                        required: true,
                        equalTo: '#password1',
                        minlength: 6,
                        maxlength: 16
                    }
                },
                onkeyup: false,
                focusCleanup: true,
                success: "valid",
                submitHandler: function(form) {
                    var oldPassword = password.val(),
                        newPassword = password2.val();
                    if (dis) {
                        return false;
                    }
                    dis = true;
                    $.ajax({
                        type: 'post',
                        data: {
                            'oldPassword': window.md5(oldPassword),
                            'newPassword': window.md5(newPassword)
                        },
                        url: url,
                        success: function(data) {
                            var data = JSON.parse(data);
                            if (data.code == 'N00000') {
                                layer.msg('修改成功，请重新登录系统!', {
                                    icon: 1,
                                    time: 1000
                                });
                                setTimeout(function() {
                                    location.replace('/admin/logout');
                                }, 1500);
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