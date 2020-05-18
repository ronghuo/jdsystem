$(function(){

    if ($('#content').length > 0) {
        var ue = UE.getEditor('content', {toolbars: []});
        ue.ready(function () {
            ue.setDisabled();
            $('#ueCloth').show();
        });
    }

});