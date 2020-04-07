$(function(){


    //保存检查
    $('#savedata').on('click',function(){
        var pwsd = $('input[name="PWSD"]');
        if(!checkInputEmpty(pwsd)){
            pageMesg.show('请填写新密码',0);
            pwsd.focus();
            return false;
        }

        $('#postform').submit();
    });

});