/**
 * Created by chenxh on 2019/3/31.
 */
$(function(){
    fileview.clear();

    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
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
        if(!url){
            return false;
        }
        layeriframe(url, '修改密码', 500, 300);
    });

    $('a.openlayerwin2').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '人员指派', 700, 520);
    });
});