$(function(){


    //保存检查
    $('#savedata').on('click',function(){
        var startTime = $('input[name="startTime"]');
        if (!checkInputEmpty(startTime)) {
            pageMesg.show('请填写开始时间',0);
            startTime.focus();
            return false;
        }

        $('#postform').submit();
    });

});