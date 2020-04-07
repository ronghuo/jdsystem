/**
 * Created by chenxh on 2019/3/29.
 */
$(function(){
    fileview.clear();

    $('#dmmcsbox').levelSelect({
        url:'/static/plugin/cate/dmmcs-43.json?vv='+JD_VERSION
    });

    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '用户详情', 500, 600);
    });

    $('a.openlayerwin1').on('click',function(){
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url, '修改密码', 500, 300);
    });
});