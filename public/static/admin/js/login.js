/**
 * Created by xiaohui on 2015/7/17.
 */
$(function () {

    //刷新验证码
    $('#vfyimg').on('click',function(){
        var url = $(this).attr('src').split('?'),
            timenow =new Date().getTime();
        $(this).attr('src',url[0]+'?'+timenow);
        $('input[name="verifyinput"]').focus();
    });

    $('body').on('keyup',function(event) {
        //回车进行登录
        if (event.keyCode == 13) {
            $('#submitlogn').click();
        }
    });

    //
    var isclick = false;
    $('#submitlogn').on('click',function(){
        if(isclick){
            pageMesg.show('正在登录处理...',0);
            return false;
        }
        var btn = $(this),
            uname = $('input[name="uname"]'),
            pwsd = $('input[name="pwsd"]'),
            verify = $('input[name="verifyinput"]'),
            data = {},inputele = $('.input');
        if(!checkInputEmpty(uname)){
            pageMesg.show('请输入账号',0);
            uname.focus();
            return false;
        }
        if(!checkInputEmpty(pwsd)){
            pageMesg.show('请输入密码',0);
            pwsd.focus();
            return false;
        }
        if(!checkInputEmpty(verify)){
            pageMesg.show('请输入验证码',0);
            verify.focus();
            return false;
        }

        data = {'uname':uname.val(),'pwsd':pwsd.val(),'verify':verify.val(),'ajax':1};

        isclick = true;
        inputele.attr('disabled',true);
        btn.text('正在登录，请稍等...');

        $.post(location.href,data,function(d){
            if(d.err != '0'){
                pageMesg.show(d.mesg,0);
                isclick = false;
                inputele.attr('disabled',false);
                btn.text('登录');
                if(d.err=='2'){
                    $('#vfyimg').click();
                }
                return false;
            }else{
                pageMesg.show(d.mesg,1);
                setTimeout(function(){
                    location.href = d.url;
                },1800);
                return false;
            }
        },'json');
        return false;
    });

});