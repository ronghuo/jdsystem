$(function(){

    $('#subbtn').on('click',function(){
        var $name = $('input[name=NAME]');

        if ($name.val().length == 0) {
            formValid.showErr($name, '请输入模板名称');
            return false;
        } else {
            formValid.showSuccess($name);
        }
        return true;
    });

    $('a[id^=btnDelete_]').each(function (i, item) {
        var $self = $(item);
        $self.bind('click', function () {
            layerconfirm('确定要删除吗？', 2, '确认', function () {
                $self.parent().remove();
                layer.closeAll();
            }, function () {
                layer.closeAll();
            });
        });
    });

});