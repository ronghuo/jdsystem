/**
 * Created by chenxh on 2019/4/2.
 */
$(function(){

    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });

    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '申请人详情', 500, 600);
    });

    $('a.openlayerwin2').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '审批人详情', 500, 600);
    });

    $('a.openlayerwin3').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '申请内容详情', 500, 600);
    });
});