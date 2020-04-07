/**
 * Created by chenxh on 2019/4/6.
 */
$(function(){
    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '添加新分类', 500, 400);
    });
    $('a.openlayerwin1').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '编辑分类', 500, 400);
    });

});