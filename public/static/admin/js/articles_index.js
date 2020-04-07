/**
 * Created by chenxh on 2019/4/6.
 */
$(function(){
    fileview.clear();
    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '资讯详情', 500, 600);
    });

    $('a.openlayerwin2').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '资讯轮播', 500, 600);
    });


    $('.pushbtn').on('click',function(){

        var self = $(this),
            id = self.attr('data-id'),
            to = self.prev('select').val();

        // console.log([
        //     id,to
        // ]);

        if(!id || !to){
            return false;
        }
        layer.load();

        $.post(push_url,{"id":id,"to":to},function(d){

            console.log(d);
            layer.closeAll();
            if(d.err=='0'){
                layermsg(d.msg,1);
            }else{
                layeralert(d.msg,4);
            }

            return false;
        },'json');



    });
});