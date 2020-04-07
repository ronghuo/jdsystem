/**
 * Created by chenxh on 2019/4/6.
 */
$(function(){

    //保存检查
    $('#savedata').on('click',function(){
        var name = $('input[name="NAME"]'),
            clienttag = $('select[name="CLIENT_TAG"]');
        if(!checkInputEmpty(name)){
            pageMesg.show('请填写分类名称',0);
            name.focus();
            return false;
        }
        if(!checkSelectEmpty(clienttag)){
            pageMesg.show('请选择客户端',0);
            clienttag.focus();
            return false;
        }
        $('#postform').submit();
    });

});