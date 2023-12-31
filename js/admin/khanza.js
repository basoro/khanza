$(function(){
    $('#menuUtama').click(function() {
            $('#dlg_menu_utama').window('open');
    });

    $("#cari_modul").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#list_modul li").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $('.wu-header-top a').bind("click",function(data){
        var title = $(this).attr('title');
        var url = $(this).attr('data-link');
        var iconCls = $(this).attr('data-icon');
        var iframe = $(this).attr('iframe')==1?true:false;
        addTab(title,url,iconCls,iframe);
    });
    $('.mm .mm_item').bind("click",function(){
        var title = $(this).text();
        var url = $(this).attr('data-link');
        var iconCls = $(this).attr('data-icon');
        var iframe = $(this).attr('iframe')==1?true:false;
        addTab(title,url,iconCls,iframe);
    });
    $('.sub_mm .sub_mm_item').bind("click",function(){
        var title = $(this).text();
        var url = $(this).attr('data-link');
        var iconCls = $(this).attr('data-icon');
        var iframe = $(this).attr('iframe')==1?true:false;
        addTab(title,url,iconCls,iframe);
    });
    $('.modul_menu a').bind("click",function(){
        var title = $(this).attr('data-title');
        var url = $(this).attr('data-link');
        var iconCls = $(this).attr('data-icon');
        var iframe = $(this).attr('iframe')==1?true:false;
        addTab(title,url,iconCls,iframe);
        $('#dlg_menu_utama').window('close')
    });
})

$('#wu-tabs').tabs({
    tools:[{
        iconCls:'icon-reload',
        border:false,
        handler:function(){
            $('#dg_Pasien').datagrid('reload');
            $('#dg_RegPeriksa').datagrid('reload');
            $('#dg_RawatJalan').datagrid('reload');
        }
    }]
});

function addTab(title, href, iconCls, iframe){
    var tabPanel = $('#wu-tabs');
    if(!tabPanel.tabs('exists',title)){
        var content = '<iframe scrolling="auto" frameborder="0"  src="'+ href +'" style="width:100%;height:100%;"></iframe>';
        if(iframe){
            tabPanel.tabs('add',{
                title:title,
                content:content,
                iconCls:iconCls,
                fit:true,
                cls:'pd3',
                closable:true
            });
        }
        else{
            tabPanel.tabs('add',{
                title:title,
                href:href,
                iconCls:iconCls,
                fit:true,
                cls:'pd3',
                closable:true
            });
        }
    }
    else
    {
        tabPanel.tabs('select',title);
    }
                tabClose();
}

function removeTab(){
    var tabPanel = $('#wu-tabs');
    var tab = tabPanel.tabs('getSelected');
    if (tab){
        var index = tabPanel.tabs('getTabIndex', tab);
        tabPanel.tabs('close', index);
    }
}

function closeDialog(){
    var dialog = $('#dlgPasien');
    var dlg = dialog.dialog('getSelected');
    if (dlg){
        var index = dialog.dialog('getDialogIndex', dlg);
        dialog.dialog('close', index);
    }
}

function datetimeformatter(date){
    if (!(date instanceof Date)) date = new Date(date);
    var h = date.getHours();
    var M = date.getMinutes();
    var s = date.getSeconds();
    function _ff(v) {
        return (v < 10 ? '0' : '') + v;
    };
    return _ff(date.getFullYear())+'-'+_ff(date.getMonth()+1)+'-'+_ff(date.getDate())+' '+ _ff(h)+':'+_ff(M)+':'+_ff(s);		
}

function datetimeparser(s){
    if ($.trim(s) == '') {
        return new Date();
    }
    var dt = s.split(' ');
    var p1 = dt[0].split('-');
    var p2 = dt[1].split(':');
    return new Date(p1[0],p1[1]-1,p1[2],p2[0],p2[1],p2[2]);
}

function dateformatter(date){
    var y = date.getFullYear();
    var m = date.getMonth()+1;
    var d = date.getDate();
    return y + '-' + (m<10?('0'+m):m) + '-' + (d<10?('0'+d):d);		
}

function dateparser(s){
    if (!s) return new Date();
    var ss = (s.split('-'));
    var y = parseInt(ss[0],10);
    var m = parseInt(ss[1],10);
    var d = parseInt(ss[2],10);
    if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
        return new Date(y,m-1,d);
    } else {
        return new Date();
    }
}  