/**
 * Created by chenxh on 2019/4/2.
 */
$(function(){

    $('#savedata').on('click',function () {
        var content = $('textarea[name="content"]');

        if(!checkInputEmpty(content)){
            pageMesg.show('请填写回复内容',0);
            content.focus();
            return false;
        }

        $('#postform').submit();
    });
});