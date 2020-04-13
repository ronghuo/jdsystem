/**
 * Created by chenxh on 2019/4/1.
 */
$(function () {

    fileview.init();

    $('#areas1').levelSelect({
        url:'/static/plugin/cate/areas-all.json?vv='+JD_VERSION
    });

    $('#areas2').levelSelect({
        url:'/static/plugin/cate/areas-all.json?vv='+JD_VERSION
    });
    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });

    $('#areas4').levelSelect({
        url:'/static/plugin/cate/dmmcs-43.json?vv='+JD_VERSION
    });

    $('#subbtn1').on('click',function(){
        $('#check_result').val(1);


        return validFrom();
    });

    $('#subbtn2').on('click',function(){
        $('#check_result').val(2);

        return validFrom();
    });


    $('#subbtn').on('click',function(){
        var name = $('#name'),
            idnumbertype = $('#idnumbertype'),
            idnumber = $('#idnumber'),
            gender = $('#gender'),
            mobile = $('#mobile'),
            pwsd = $('#pwsd'),
            address = $('#address');

        if(!checkInputEmpty(name)){
            formValid.showErr(name,'缺少姓名');
            return false;
        }else{
            formValid.showSuccess(name);
        }

        if(!checkSelectEmpty(idnumbertype)){
            formValid.showErr(idnumbertype,'缺少证件类型');
            return false;
        }else{
            formValid.showSuccess(idnumbertype);
        }

        if(!checkInputEmpty(idnumber)){
            formValid.showErr(idnumber,'缺少证件号码');
            return false;
        }else{
            formValid.showSuccess(idnumber);
        }

        if(!checkSelectEmpty(gender)){
            formValid.showErr(gender,'缺少性别');
            return false;
        }else{
            formValid.showSuccess(gender);
        }

        if(!checkInputEmpty(mobile)){
            formValid.showErr(mobile,'缺少手机号码');
            return false;
        }else{
            formValid.showSuccess(mobile);
        }

        if(pwsd.length>0){
            if(!checkInputEmpty(pwsd)){
                formValid.showErr(pwsd,'缺少登录密码');
                return false;
            }else{
                formValid.showSuccess(pwsd);
            }
        }

        var areas1 = $('#areas1'),
            lv3 = areas1.children('select.lv3').val();
        if (!lv3){
            formValid.showErr(areas1,'缺少籍贯');
            return false;
        }else{
            formValid.showSuccess(areas1);
        }

        var areas2 = $('#areas2'),
            lv3 = areas2.children('select.lv3').val();
        if(!lv3){
            formValid.showErr(areas2,'缺少现住址');
            return false;
        }else{
            formValid.showSuccess(areas2);
        }

        var areas3 = $('#areas3'),
            lv3 = areas3.children('select.lv3').val();
        if(!lv3){
            formValid.showErr(areas3,'缺少所在社区');
            return false;
        }else{
            formValid.showSuccess(areas3);
        }


        if(!checkInputEmpty(address)){
            formValid.showErr(address,'缺少现住详细地址');
            return false;
        }else{
            formValid.showSuccess(address);
        }

        var areas4 = $('#areas4'),
            dmmcs = $('select[name^=dmmc]');
        for (var i = 0; i < dmmcs.length; i++) {
            if ($(dmmcs[i]).val() == '') {
                formValid.showErr(areas4,'缺少所属禁毒办');
                return false;
            }
        }
        formValid.showSuccess(areas4);

        return true;
    });

});

