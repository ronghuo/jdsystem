/**
 * Created by chenxh on 2019/4/2.
 */
$(function(){
    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '人员详情', 500, 600);
    });

    $('a.openlayerwin2').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '留言详情', 500, 600);
    });
});