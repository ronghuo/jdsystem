$(function () {

    $('#btnImport').on('click', function () {
        $(this).attr("disabled", true);
        var $templateId = $(':input[name=TEMPLATE_ID]');
        if (!checkSelectEmpty($templateId)) {
            layeralert('请选择模板', 5, '提示');
            $(this).attr("disabled", false);
            return;
        }
        var $personList = $(':input[name=PERSON_LIST]');
        if (!checkInputEmpty($personList)) {
            layeralert('请选择要导入的人员清单', 5, '提示');
            $(this).attr("disabled", false);
            return;
        }
        $('#postform').submit();
    });

});