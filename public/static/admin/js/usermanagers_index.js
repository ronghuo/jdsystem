/**
 * Created by chenxh on 2019/4/1.
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
        layeriframe(url, '人员详情', 500, 600);
    });
    $('a.openlayerwin1').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '修改密码', 500, 300);
    });
});