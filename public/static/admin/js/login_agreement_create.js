$(function(){


    $('#subbtn').on('click',function(){
        var title = $('#title');
        if (!checkInputEmpty(title)) {
            formValid.showErr(title,'请填写协议标题');
            return false;
        } else {
            formValid.showSuccess(title);
        }
        var content = $('#content');
        if (!ue.hasContents()) {
            formValid.showErr(content,'请填写协议内容');
            return false;
        } else {
            formValid.showSuccess(content);
        }

        return true;
    });


    //编辑器
    if ($('#content').length > 0) {
        UE.getEditor('content', {toolbars: [myueditorconfig],serverUrl:ed_url});
    }

});