/**
 * Created by xiaohui on 2015/7/16.
 */
$(function(){

    /**
     * 类型切换

    $('select[name="level"]').on('change',function(){
        var val = $(this).val();
        if(val=='2'){
            $('#ngroup').removeClass('hide');
        }else{
            $('#ngroup').addClass('hide');
        }

    });*/

    /**
     * 数据检查
     */
    $('#submitbtn').on('click',function(){
        var title = $('input[name="TITLE"]'),
            name = $('input[name="NAME"]'),
            pid = $('select[name="PID"]'),
            level = $('select[name="LEVEL"]');

        if(!checkInputEmpty(title)){
            formValid.showErr(title,'请填写中文名称');
            return false;
        }else{
            formValid.showSuccess(title);
        }
        if(!checkInputEmpty(name)){
            formValid.showErr(name,'请填写英文名称');
            return false;
        }else{
            formValid.showSuccess(name);
        }

        if(!checkSelectEmpty(level)){
            formValid.showErr(level,'请选择 类型');
            return false;
        }else{
            formValid.showSuccess(level);
        }
        $('#postform').submit();
    });

});