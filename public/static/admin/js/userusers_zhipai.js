
$(function () {

    var UP_LEVEL_NAME = '<<上一级';

    // 确保levelSelect中获取json数据为同步请求
    $.ajaxSettings.async = false;
    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION,
        nodatahtml: '<option value="">' + UP_LEVEL_NAME + '</option>'
    });
    // 恢复全局ajax为异步请求
    $.ajaxSettings.async = true;

    // 默认“市辖区”为“市级”区域
    var CITY_ID = 431201000000,
        CITY_NAME = '市辖区';
    // 移除默认选项
    $('#lv1 option:first').remove();

    /*
    // 新增“上级”选项
    var addUpLevelOption = function ($target, $next) {
        var curOpt = $target.children('option:selected');
        $children = $next.children();
        if ($children.length > 0) {
            upLevelOption = '<option value="' + curOpt.val() + '">' + curOpt.text() + '（上级）</option>';
            $($children[0]).after(upLevelOption);
        }
    };
    $('#lv1').on('change', function () {
        addUpLevelOption($(this), $('#lv2'));
    });
    if (lv1value.length > 0) {
        addUpLevelOption($('#lv1'), $('#lv2'));
    }

    $('#lv2').on('change', function () {
        addUpLevelOption($(this), $('#lv3'));
    });
    if (lv2value.length > 0) {
        addUpLevelOption($('#lv2'), $('#lv3'));
    }
    */
    // 根据权限级别，过滤掉非自己管辖范围内的地区
    $('select[powerLevel]').each(function () {
        var self = $(this);
        if (self.attr('powerLevel') == POWER_LEVEL) {
            var myArea = self.attr('data-value');
            self.children().not(':first').not('[value=' + myArea + ']').remove();
        }
    });
    /*
    $('select[powerLevel]').on('change', function () {
        var powerLevel = $(this).attr('powerLevel');
        if (powerLevel > POWER_LEVEL) {
            return;
        }
        var curVal = $(this).val();
        var oriVal = $(this).attr('data-value');
        if (POWER_LEVEL == powerLevel) {
            if (curVal == CITY_ID || curVal == '') {
                return;
            }
            if (curVal == oriVal) {
                return;
            }
            layeralert('同级别区域不能跨域指派', 5, '警告');
        }
        $(this).val(oriVal);
    });
    */

    $('#zhipaiok').on('click', function () {
        var lv1 = $('#lv1'),
            lv2 = $('#lv2'),
            lv3 = $('#lv3');

        $('#zpact').val('1');

        /*if (!checkSelectEmpty(lv1)) {
            pageMesg.show('请选择县市区', 0);
            lv1.focus();
            return false;
        }
        if (lv2.children().length > 0 && !checkSelectEmpty(lv2)) {
            pageMesg.show('请选择乡镇（街道）', 0);
            lv2.focus();
            return false;
        }
        if (lv3.children().length > 0 && !checkSelectEmpty(lv3)) {
            pageMesg.show('请选择村（社区）', 0);
            lv3.focus();
            return false;
        }*/
        pageMesg.hide();

        var returnToCity = POWER_LEVEL == 2 && lv1.val() == CITY_ID;
        var returnToCounty = POWER_LEVEL == 3 && lv2.val() == '';
        var returnToStreet = POWER_LEVEL == 4 && lv3.val() == '';
        var action = (returnToCity || returnToCounty || returnToStreet) ? '退回' : '指派';
        var lv1text = lv1.find('option:selected').text(),
            lv2text = lv2.find('option:selected').text(),
            lv3text = lv3.find('option:selected').text();
        lv2text = lv2text == UP_LEVEL_NAME ? '' : lv2text;
        lv3text = lv3text == UP_LEVEL_NAME ? '' : lv3text;
        var area;
        if (returnToCity) {
            area = CITY_NAME;
        }
        else if (returnToCounty) {
            area = lv1text;
        }
        else if (returnToStreet) {
            area = lv1text + ' ' + lv2text;
        }
        else {
            area = lv1text + ' ' + lv2text + ' ' + lv3text;
        }
        var confirmMsg = '确定要将该人员' + action + '到【' + area + '】吗？';

        if (returnToCity || returnToCounty || returnToStreet) {
            confirmAction(confirmMsg, function () {
                openReasonLayer();
            });
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
            layer.closeAll();
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