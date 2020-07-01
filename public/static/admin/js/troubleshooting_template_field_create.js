$(function(){

    $('#subbtn').on('click',function(){
        var $templateId = $(':input[name=TEMPLATE_ID]');
        if (!checkSelectEmpty($templateId)) {
            formValid.showErr($templateId, '请选择模板');
            return false;
        } else {
            formValid.showSuccess($templateId);
        }

        var $code = $(':input[name=CODE]');
        if (!checkInputEmpty($code)) {
            formValid.showErr($code, '请输入字段代码');
            return false;
        } else {
            formValid.showSuccess($code);
        }

        var $name = $(':input[name=NAME]');
        if (!checkInputEmpty($name)) {
            formValid.showErr($name, '请输入字段名称');
            return false;
        } else {
            formValid.showSuccess($name);
        }

        var $widget = $(':input[name=WIDGET]');
        if (!checkSelectEmpty($widget)) {
            formValid.showErr($widget, '请选择控件类型');
            return false;
        } else {
            formValid.showSuccess($widget);
        }

        var $sort = $(':input[name=SORT]');
        if (!checkInputEmpty($sort)) {
            formValid.showErr($sort, '请输入字段排序号');
            return false;
        } else {
            formValid.showSuccess($sort);
        }

        var $nullable = $(':input[name=NULLABLE]');
        if (!checkSelectEmpty($nullable)) {
            formValid.showErr($nullable, '请选择是否可为空');
            return false;
        } else {
            formValid.showSuccess($nullable);
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