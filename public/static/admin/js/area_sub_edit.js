$(function(){


    //保存检查
    $('#savedata').on('click',function(){
        var name = $('input[name="NAME"]');
        if(!checkInputEmpty(name)){
            pageMesg.show('请填写地区名称',0);
            name.focus();
            return false;
        }

        $('#postform').submit();
    });

});