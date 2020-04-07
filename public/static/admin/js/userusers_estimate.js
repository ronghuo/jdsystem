var ue;
$(function(){


    $('#subbtn').on('click',function(){
        var content = $('#content');


        if(!ue.hasContents()){
            formValid.showErr(content,'请填写评估内容');
            return false;
        }else{
            formValid.showSuccess(content);
        }

        return true;
    });


    //编辑器
    if($('#content').length>0){
        ue = UE.getEditor('content', {toolbars: [myueditorconfig],serverUrl:ed_url});
    }

    $('')

});