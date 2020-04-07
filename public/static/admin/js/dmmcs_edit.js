$(function(){


    //保存检查
    $('#savedata').on('click',function(){
        var deptname = $('input[name="DEPTNAME"]');
        if(!checkInputEmpty(deptname)){
            pageMesg.show('请填写机构名称',0);
            deptname.focus();
            return false;
        }

        $('#postform').submit();
    });

});