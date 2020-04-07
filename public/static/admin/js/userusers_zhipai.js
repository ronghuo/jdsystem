
$(function () {
    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });

    $('select[powerLevel]').change(function () {
        var powerLevel = $(this).attr('powerLevel');
        if (powerLevel > POWER_LEVEL) {
            return;
        }
        var curVal = $(this).val();
        var oriVal = $(this).attr('data-value');
        if (POWER_LEVEL == powerLevel) {
            if (curVal == '') {
                return;
            }
            if (curVal == oriVal) {
                return;
            }
            layeralert('不能指派给与自己管辖区域同级别的区域', 5, '警告');
        }
        $(this).val(oriVal);
    });

    $('#zhipaiok').on('click', function () {
        var lv1 = $('#lv1'),
            lv2 = $('#lv2'),
            lv3 = $('#lv3');

        $('#zpact').val('1');

        var isArea1Empty = lv1.val() == '';
        var isArea2Empty = lv2.val() == '';
        var isArea3Empty = lv3.val() == '';
        var confirmMsg = '确定要将该人员指派到【'
            + (isArea1Empty ? '市' : lv1.find('option:selected').text())
            + (isArea2Empty ? '' : lv2.find('option:selected').text())
            + (isArea3Empty ? '' : lv3.find('option:selected').text())
            + '】吗？';

        if (POWER_LEVEL == 2 && isArea1Empty) {
            openReasonLayer();
        }
        else if (POWER_LEVEL == 3 && isArea2Empty) {
            openReasonLayer();
        }
        else if (POWER_LEVEL == 4 && isArea3Empty) {
            openReasonLayer();
        }
        else {
            confirmAction(confirmMsg, function () {
                $('#postform').submit();
            });
        }
    });

    $('#zhipaiok1').on('click', function () {
        var lv1 = $('#lv1'),
            lv2 = $('#lv2'),
            lv3 = $('#lv3');
        $('#zpact').val('2');
        if(!checkSelectEmpty(lv1)){
            pageMesg.show('请确认到完整的地区',0);
            lv1.focus();
            return false;
        }
        if(!checkSelectEmpty(lv2)){
            pageMesg.show('请确认到完整的地区',0);
            lv2.focus();
            return false;
        }
        if(!checkSelectEmpty(lv3)){
            pageMesg.show('请确认到完整的地区',0);
            lv3.focus();
            return false;
        }

        $('#postform').submit();
    });

    var confirmAction = function (msg, yes) {
        var index = layerconfirm(msg, 2, '确认', function () {
            yes();
        }, function () {
            layer.close(index);
        });
    }

    var openReasonLayer = function (confirmMsg) {
        var reasonLayer = $.layer({
            type: 1,
            title: '指派理由',
            area: ['400px', '240px'],
            offset: ['20%', '50%'],
            border: [6, 0.3, '#000', true],
            shade: [0],
            closeBtn: [0, true],
            page: {
                html: '<div style="width: 400px;height: 180px;text-align: center;padding-top: 20px;">' +
                '<textarea id="assignReason" rows="5" style="width: 80%;"></textarea>' +
                '<div style="margin-top: 10px;">' +
                '<input type="button" id="confirmAssignReason" class="btn btn-primary" style="width: 80px;margin-right: 10px;" value="确定" />' +
                '<input type="button" id="cancelAssignReason" class="btn" style="width: 80px;" value="取消" />' +
                '</div>' +
                '</div>'
            }
        });
        $('#confirmAssignReason').click(function () {
            if (!checkInputEmpty($('#assignReason'))) {
                layeralert('请输入指派理由', 5, '提示');
                return false;
            }
            $(':hidden[name=assignReason]').val($('#assignReason').val());
            $('#postform').submit();
            $('#cancelAssignReason').trigger('click');
        });
        $('#cancelAssignReason').click(function () {
            layer.close(reasonLayer);
        });
    };

    
    $('#jiechubtn').on('click', function () {
        $('#zpact').val('3');



        var l = layerconfirm('确定要解除吗？', 2, '确认', function () {
            $('#postform').submit();
        }, function () {
            layer.close(l);
        });


    })
});