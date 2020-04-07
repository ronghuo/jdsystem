/**
 * Created by xiaohui on 2015/7/16.
 */
$(function(){
    //添加/编辑部门
    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '权限分组', 400, 450);
    });

    //保存检查
    $('#savedata').on('click',function(){
        var name = $('input[name="NAME"]'),
            link = $('input[name="LINK"]'),
            icon = $('input[name="ICON"]');
        if(!checkInputEmpty(name)){
            pageMesg.show('请填写分组名称',0);
            name.focus();
            return false;
        }
        if(!checkInputEmpty(link)){
            pageMesg.show('请填写分组首页链接',0);
            link.focus();
            return false;
        }
        if(!checkInputEmpty(icon)){
            pageMesg.show('请填写分组图标',0);
            icon.focus();
            return false;
        }
        $('#postform').submit();
    });

    //
    /*if($('.sortnum').length>0){
        $('.sortnum').on('blur',function(){
            var me = $(this),
                _id = me.attr('data-id'),
                _val = me.val();
            if(!_id || !_val || _val<=0){
                return false;
            }
            $.post('/syspower/groupopers',{'id':_id,'sval':_val,'ajax':1,'act':'setsort'},function(d){},'json');
        });
    }*/
});