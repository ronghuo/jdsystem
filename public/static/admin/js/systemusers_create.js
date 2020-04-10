/**
 * Created by chenxh on 2019/3/28.
 */
$(function(){

    fileview.init();

    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });

    $('#dmmcsbox').levelSelect({
        url:'/static/plugin/cate/dmmcs-43.json?vv='+JD_VERSION
    });


    $('#subbtn').on("click",function() {
        var $log = $(':text[name=LOG]');
        if (!checkInputEmpty($log)) {
            formValid.showErr($log, '账号不能为空');
            return false;
        }
        formValid.showSuccess($log);

        var $pwd = $(':password[name=PWD]');
        if (!checkInputEmpty($pwd)) {
            formValid.showErr($pwd, '密码不能为空');
            return false;
        }
        formValid.showSuccess($pwd);

        var $name = $(':text[name=NAME]');
        if (!checkInputEmpty($name)) {
            formValid.showErr($name, '姓名不能为空');
            return false;
        }
        formValid.showSuccess($name);

        var $mobile = $(':text[name=MOBILE]');
        if (!checkInputEmpty($mobile)) {
            formValid.showErr($mobile, '手机号码不能为空');
            return false;
        }
        formValid.showSuccess($mobile);

        var $role = $('select[name=role]');
        if (!checkSelectEmpty($role)) {
            formValid.showErr($role, '后台权限角色不能为空');
            return false;
        }
        formValid.showSuccess($role);

        var $powerAreas = $('select[name^=power]');
        var role = $role.find('option:selected').val().split('-')[1];
        if (role == '县级权限' && !$($powerAreas[0]).val()) {
            formValid.showErr($($powerAreas[0]), '当角色为“县级权限”时，权限单位必须选择对应“县（市、区）”');
            return false;
        }
        else if (role == '乡级权限' && !$($powerAreas[1]).val()) {
            formValid.showErr($($powerAreas[1]), '当角色为“乡级权限”时，权限单位必须选择对应“乡镇（街道）”');
            return false;
        }
        else if (role == '村级权限' && !$($powerAreas[2]).val()) {
            formValid.showErr($($powerAreas[2]), '当角色为“村级权限”时，权限单位必须选择对应“村（社区）”');
            return false;
        }
        formValid.showSuccess($($powerAreas[0]));

        var $dmmcs = $('select[name^=dmmc]:not(:first)');
        if (!$($dmmcs[0]).val()) {
            formValid.showErr($($dmmcs[0]), '单位不能为空');
            return false;
        }
        formValid.showSuccess($($dmmcs[0]));
        return true;
    });


});