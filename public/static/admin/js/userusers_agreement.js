var ue;
$(function(){


    $('#subbtn').on('click',function(){
        var content = $('#content');


        if(!ue.hasContents()){
            formValid.showErr(content,'请填写协议内容');
            return false;
        }else{
            formValid.showSuccess(content);
        }

        return true;
    });


    //编辑器
    if($('#content').length>0){
        ue = UE.getEditor('content', {toolbars: [agreementUeditorConfig],serverUrl:ed_url});
        ue.ready(function () {
           ue.setDisabled(['insertimage', 'cleardoc']);
        });
    }

});