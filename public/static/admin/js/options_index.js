$(function(){


    $('.addoption').on('click',function(){

        var self = $(this);

        self.hide();
        self.next('p').show();

    });

    $('.postoption').on('click',function(){

        var self = $(this),
            pid = self.attr('data-pid'),
            name = self.prev('input').val();
        // console.log([
        //     pid,
        //     name
        // ]);
        if(!name || !pid){
            return false;
        }

        $.post(createurl,{'pid':pid,'name':name},function(d){


            if(d.err=='0'){

                location.reload();
            }else{
                layermsg(d.msg,4);
            }


        },'json');

    });


    $('.nameinput').on('blur',function(){
        var self = $(this),
            id = self.attr('data-id'),
            name = self.val();

        console.log([
            id,
            name
        ]);
        if(!id || !name){
            return false;
        }
        $.post(editurl,{'id':id,'name':name},function(d){


            if(d.err=='0'){

                //location.reload();
            }else{
                layermsg(d.msg,4);
            }


        },'json');

    });


    $('.deloption').on('click',function(){

        var self = $(this),
            id = self.attr('data-id');
        if(!id){
            return false;
        }

        layerconfirm('删除后不可恢复，确定要删除吗？', 2, '操作确认', function () {
            $.post(delurl,{'id':id},function(d){

                if(d.err=='0'){
                    self.closest('li').remove();
                    closelayer();
                }else{
                    layeralert(d.msg,4,'操作提示');
                }

            },'json');
        }, function () {
            closelayer();
        });

    });

});