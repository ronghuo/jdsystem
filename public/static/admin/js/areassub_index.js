/**
 * Created by chenxh on 2019/3/29.
 */
$(function(){
    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '新增街道社区信息', 500, 600);
    });

    $('a.delete_area').on('click',function(){
        var self = $(this),
            id = self.attr('data-id');
        if(!id){
            return false;
        }

        layerconfirm('确定要删除吗？', 2, '确认', function () {
            $.post(del_url,{"id":id},function(d){

                if(d.err=='0'){
                    self.closest('td').html('');
                }

            },'json');
            layer.closeAll();
        }, function () {
            layer.closeAll();
        });



    });

});