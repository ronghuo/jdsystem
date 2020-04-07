/**
 * Created by chenxh on 2019/3/29.
 */
//保存检查
$('#savedata').on('click',function(){

    var arealist = $('textarea[name="arealist"]');

    if(!checkInputEmpty(arealist)){
        pageMesg.show('请填地区信息',0);
        name.focus();
        return false;
    }
    $('#postform').submit();
});