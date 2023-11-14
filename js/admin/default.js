$.extend($.fn.calendar.methods, {
  moveTo: function(jq, date){
    return jq.each(function(){
      if (!date){
        var now = new Date();
        $(this).calendar({
          year: now.getFullYear(),
          month: now.getMonth()+1,
          current: date
        });
        return;
      }
      var opts = $(this).calendar('options');
      if (opts.validator.call(this, date)){
        var oldValue = opts.current;
        $(this).calendar({
          year: date.getFullYear(),
          month: date.getMonth()+1,
          current: date
        });
        if (!oldValue || oldValue.getTime() != date.getTime()){
          opts.onChange.call(this, opts.current, oldValue);
        }
      }
    });
  }
});
$.extend($.fn.datebox.defaults, {
  formatter: function(date){
    if (!date){return ' ';}
    var y = date.getFullYear();
    var m = date.getMonth()+1;
    var d = date.getDate();
    return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
  },
  parser: function(s){
    if (!s) return null;
    var ss = s.split('-');
    var d = parseInt(ss[0],10);
    var m = parseInt(ss[1],10);
    var y = parseInt(ss[2],10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
      return new Date(y,m-1,d);
    } else {
      return null;
    }
  }
});


//弹出信息窗口 title:标题 msgString:提示信息 msgType:信息类型 [error,info,question,warning]
function msgShow(title, msgString, msgType) {
    $.messager.alert(title, msgString, msgType);
}


$(function() {
    tabClose();
    tabCloseEven();


    $('#editPass').click(function() {
        $('#w').window('open');
    });

    $('#btnEp').click(function() {
        serverLogin();
    })

    $('#btnCancel').click(function(){$('#w').window('close');;})

    $('#loginOut').click(function() {
        $.messager.confirm('Informasi', 'Yakin ingin keluar dari sistem?', function(r) {
            if (r) {
                location.href = mlite.url + '/' + mlite.admin + '/logout?t=' + mlite.token;
            }
        });
    })
});

function tabClose()
{
    $(".tabs-inner").dblclick(function(){
        console.log(123)
        var subtitle = $(this).children(".tabs-closable").text();
        $('#wu-tabs').tabs('close',subtitle);
    })

    /*为选项卡绑定右键*/
    $(".tabs-inner").bind('contextmenu',function(e){
        $('#tc').menu('show', {
            left: e.pageX,
            top: e.pageY
        });

        var subtitle =$(this).children(".tabs-closable").text();

        $('#tc').data("currtab",subtitle);
        $('#wu-tabs').tabs('select',subtitle);
        return false;
    });
}

function createFrame(url)
{
    var s = '<iframe scrolling="auto" frameborder="0"  src="'+url+'" style="width:100%;height:100%;"></iframe>';
    return s;
}

//绑定右键菜单事件
function tabCloseEven()
{
    //关闭当前
    $('#tc-tabclose').click(function(){
        var currtab_title = $('#tc').data("currtab");
        $('#wu-tabs').tabs('close',currtab_title);
    })

    //全部关闭
    $('#tc-tabcloseall').click(function(){
        $('.tabs-inner span').each(function(i,n){
            var t = $(n).text();
            if(t != '首页'){
                $('#wu-tabs').tabs('close',t);
            }
        });
    });

    //关闭除当前之外的TAB
    $('#tc-tabcloseother').click(function(){
        $('#tc-tabcloseright').click();
        $('#tc-tabcloseleft').click();
    });

    //关闭当前右侧的TAB
    $('#tc-tabcloseright').click(function(){
        var nextall = $('.tabs-selected').nextAll();
        if(nextall.length != 0){
            nextall.each(function(i,n){
                var t=$('a:eq(0) span',$(n)).text();
                if(t != '首页'){
                    $('#wu-tabs').tabs('close',t);
                }
            });
        }
        $('#tc').menu('hide');
    });

    //关闭当前左侧的TAB
    $('#tc-tabcloseleft').click(function(){
        var prevall = $('.tabs-selected').prevAll();
        if(prevall.length != 0){
            prevall.each(function(i,n){
                var t=$('a:eq(0) span',$(n)).text();
                if(t != '首页'){
                    $('#wu-tabs').tabs('close',t);
                }
            });
        }
        $('#tc').menu('hide');
    });

    //退出
    $("#tc-exit").click(function(){
        $('#tc').menu('hide');
    })
}


//修改密码
function serverLogin() {
    var $newPass = $('#txtNewPass');
    var $rePass = $('#txtRePass');

    if ($newPass.val() == '') {
        msgShow('系统提示', '请输入密码！', 'warning');
        return false;
    }
    if ($rePass.val() == '') {
        msgShow('系统提示', '请在一次输入密码！', 'warning');
        return false;
    }

    if ($newPass.val() != $rePass.val()) {
        msgShow('系统提示', '两次密码不一至！请重新输入', 'warning');
        return false;
    }

    $.post('/userInfo/editPassword?newpass=' + $newPass.val(), function(msg) {
        msgShow('系统提示', "恭喜，密码修改成功！<br>您的新密码为：" + msg, 'info');
        $newPass.val('');
        $rePass.val('');
        close();
    })

}
