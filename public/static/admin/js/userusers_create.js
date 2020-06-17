/**
 * Created by chenxh on 2019/3/31.
 */
$(function () {

    initImageViewer();

    switchVisibility4StatusRelations(STATUS, SUB_STATUS);

    $('#areas1').levelSelect({
        url:'/static/plugin/cate/areas-all.json?vv='+JD_VERSION
    });

    $('#areas2').levelSelect({
        url:'/static/plugin/cate/areas-all.json?vv='+JD_VERSION
    });
    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });

    $('#areas4').levelSelect({
        url:'/static/plugin/cate/dmmcs-43.json?vv='+JD_VERSION
    });

    //编辑器
    if($('#remarks').length>0){
        ue = UE.getEditor('remarks', {toolbars: [myueditorconfig],serverUrl:ed_url});
    }

    $('#getpolices').on('click',function(){
        var self = $(this),
            dmid = $('#areas4 .lv4').val();
        if(!dmid){
            dmid = $('#areas4 .lv3').val();
        }

        if(!dmid){
            layeralert('请先选择管辖警务处',4,'提示');
        }

        //console.log(dmid);

        $.get(policeurl,{'dmid':dmid},function(d){
            console.log(d);

            if(d.err=='0'){

                if(d.len<=0){
                    return false;
                }
                var option_html = '';
                for(var i=0;i<d.len;i++){
                    option_html += '<option value="'+d.data[i]['ID']+'">'+d.data[i]['NAME']+'('+d.data[i]['UCODE']+')['+d.data[i]['MOBILE']+']</option>';
                }
                self.html(option_html);
                self.off('click');
            }


        },'json');
    });
    var ustatus = $('#ustatus');

    ustatus.on('change', function () {
       var val = $(this).val();
       formValid.hideErr($(this));
       var status = $(this).children('option:selected').text();
       $('#userStatusName').val(status);
       switchVisibility4StatusRelations(status, null, 'primary');
    });

    $('#userSubStatusSelector').on('change', function () {
        var status = ustatus.children('option:selected').text();
        var subStatus = $(this).children('option:selected').text();
        $('#userSubStatusName').val(subStatus);
        switchVisibility4StatusRelations(status, subStatus, 'secondary');
    });

    $('#statusChangesViewer').click(function () {
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url, '历史变更记录', 600, 300);
    });

    $('#subbtn').on('click',function(){

        var name = $('#name'),
            idnumbertype = $('#idnumbertype'),
            idnumber = $('#idnumber'),
            mobile = $('#mobile'),
            pwsd = $('#pwsd'),
            gender = $('#gender'),
            nationality = $('#nationality'),
            nation = $('#nation'),
            domicile_address = $('#domicile_address'),
            domicile_police_name = $('#domicile_police_name'),
            live_address = $('#live_address'),
            live_police_name = $('#live_police_name'),
            jd_zhuangan = $('#jd_zhuangan'),
            jd_zhuangan_mobile = $('#jd_zhuangan_mobile');

        if(!checkInputEmpty(name)){
            formValid.showErr(name,'缺少姓名');
            return false;
        }else{
            formValid.showSuccess(name);
        }
        //idnumbertype
        if(!checkSelectEmpty(idnumbertype)){
            formValid.showErr(idnumbertype,'缺少证件类型');
            return false;
        }else{
            formValid.showSuccess(idnumbertype);
        }

        if(!checkInputEmpty(idnumber)){
            formValid.showErr(idnumber,'缺少证件号码');
            return false;
        }else{
            formValid.showSuccess(idnumber);
        }
        if(!checkInputEmpty(mobile)){
            formValid.showErr(mobile,'缺少手机号码');
            return false;
        }else{
            formValid.showSuccess(mobile);
        }

        if(pwsd.length>0){
            if(!checkInputEmpty(pwsd)){
                formValid.showErr(pwsd,'缺少登录密码');
                return false;
            }else{
                formValid.showSuccess(pwsd);
            }
        }

        if(!checkSelectEmpty(gender)){
            formValid.showErr(gender,'缺少性别');
            return false;
        }else{
            formValid.showSuccess(gender);
        }
        //utype
        if(!checkSelectEmpty(ustatus)){
            formValid.showErr(ustatus,'缺少人员状态');
            return false;
        }else{
            formValid.showSuccess(ustatus);
        }
        if (!validStatusRelations()) {
            return false;
        }
        if(!checkSelectEmpty(nationality)){
            formValid.showErr(nationality,'缺少国藉');
            return false;
        }else{
            formValid.showSuccess(nationality);
        }

        if(!checkSelectEmpty(nation)){
            formValid.showErr(nation,'缺少民族');
            return false;
        }else{
            formValid.showSuccess(nation);
        }
        // 户籍地 areas1
        var areas1 = $('#areas1'),
            lv3 = areas1.children('select.lv3').val();
        if(!lv3){
            formValid.showErr(areas1,'缺少户籍地');
            return false;
        }else{
            formValid.showSuccess(areas1);
        }

        if(!checkInputEmpty(domicile_address)){
            formValid.showErr(domicile_address,'缺少户籍地详细地址');
            return false;
        }else{
            formValid.showSuccess(domicile_address);
        }

        if(!checkInputEmpty(domicile_police_name)){
            formValid.showErr(domicile_police_name,'缺少户籍地派出所名称');
            return false;
        }else{
            formValid.showSuccess(domicile_police_name);
        }

        // 现居地 areas2
        var areas2 = $('#areas2'),
            lv3 = areas2.children('select.lv3').val();
        if(!lv3){
            formValid.showErr(areas2,'缺少现居地');
            return false;
        }else{
            formValid.showSuccess(areas2);
        }

        if(!checkInputEmpty(live_address)){
            formValid.showErr(live_address,'缺少现居地详细地址');
            return false;
        }else{
            formValid.showSuccess(live_address);
        }

        if(!checkInputEmpty(live_police_name)){
            formValid.showErr(live_police_name,'缺少现居地派出所名称');
            return false;
        }else{
            formValid.showSuccess(live_police_name);
        }

        // 所属辖区 areas3
        var areas3 = $('#areas3'),
            lv1 = areas3.children('select.lv1').val();
        if(!lv1){
            formValid.showErr(areas3,'缺少所属辖区');
            return false;
        }else{
            formValid.showSuccess(areas3);
        }

        if(!checkInputEmpty(jd_zhuangan)){
            formValid.showErr(jd_zhuangan,'缺少负责专干姓名');
            return false;
        }else{
            formValid.showSuccess(jd_zhuangan);
        }

        if(!checkInputEmpty(jd_zhuangan_mobile)){
            formValid.showErr(jd_zhuangan_mobile,'缺少负责专干联系电话');
            return false;
        }else{
            formValid.showSuccess(jd_zhuangan_mobile);
        }

        $('.status-file-view-box :file').each(function () {
            if ($(this).val() == '') {
                $(this).attr('disabled', true);
            }
        })

        return true;
    });
});

/**
 * 初始化图片查看器
 */
var initImageViewer = function() {
    fileview.init();

    var statusFileViews = [];

    // 未报到已移交-告诫书
    var wbdyyjGjs = cloneObj(fileview);
    wbdyyjGjs.ele = '.wbdyyj-gjs-file';
    wbdyyjGjs.btn = '.wbdyyj-gjs-selector';
    wbdyyjGjs.localKey = 'wbdyyjGjs';
    statusFileViews.push(wbdyyjGjs);

    // 未报到已移交-逾期未报到证明
    var wbdyyjYqwbdzm = cloneObj(fileview);
    wbdyyjYqwbdzm.ele = '.wbdyyj-yqwbdzm-file';
    wbdyyjYqwbdzm.btn = '.wbdyyj-yqwbdzm-selector';
    wbdyyjYqwbdzm.localKey = 'wbdyyjYqwbdzm';
    statusFileViews.push(wbdyyjYqwbdzm);

    // 未报到已移交-移交回执
    $(":file[id^='wbdyyj-yjhz-file']").each(function (i) {
        var wbdyyjYjhz = cloneObj(fileview);
        wbdyyjYjhz.ele = '#wbdyyj-yjhz-file' + '_' + (i + 1);
        wbdyyjYjhz.btn = '#wbdyyj-yjhz-selector' + '_' + (i + 1);
        wbdyyjYjhz.localKey = 'wbdyyjYjhz';
        $(this).parent().children('.file-view-add').bind('click', fileviewAdd);
        $(this).parent().children('.file-view-remove').bind('click', fileviewRemove);
        statusFileViews.push(wbdyyjYjhz);
    });

    // 违反协议已移交-告诫书
    var wfxyyyjGjs = cloneObj(fileview);
    wfxyyyjGjs.ele = '.wfxyyyj-gjs-file';
    wfxyyyjGjs.btn = '.wfxyyyj-gjs-selector';
    wfxyyyjGjs.localKey = 'wfxyyyjGjs';
    statusFileViews.push(wfxyyyjGjs);

    // 违反协议已移交-吸毒检测通知书
    var wfxyyyjXdjctzs = cloneObj(fileview);
    wfxyyyjXdjctzs.ele = '.wfxyyyj-xdjctzs-file';
    wfxyyyjXdjctzs.btn = '.wfxyyyj-xdjctzs-selector';
    wfxyyyjXdjctzs.localKey = 'wfxyyyjXdjctzs';
    statusFileViews.push(wfxyyyjXdjctzs);

    // 违反协议已移交-严重违反协议证明
    var wfxyyyjYzwfxyzm = cloneObj(fileview);
    wfxyyyjYzwfxyzm.ele = '.wfxyyyj-yzwfxyzm-file';
    wfxyyyjYzwfxyzm.btn = '.wfxyyyj-yzwfxyzm-selector';
    wfxyyyjYzwfxyzm.localKey = 'wfxyyyjYzwfxyzm';
    statusFileViews.push(wfxyyyjYzwfxyzm);

    // 违反协议已移交-移交回执
    $(":file[id^='wfxyyyj-yjhz-file']").each(function (i) {
        var wfxyyyjYjhz = cloneObj(fileview);
        wfxyyyjYjhz.ele = '#wfxyyyj-yjhz-file' + '_' + (i + 1);
        wfxyyyjYjhz.btn = '#wfxyyyj-yjhz-selector' + '_' + (i + 1);
        wfxyyyjYjhz.localKey = 'wfxyyyjYjhz';
        $(this).parent().children('.file-view-add').bind('click', fileviewAdd);
        $(this).parent().children('.file-view-remove').bind('click', fileviewRemove);
        statusFileViews.push(wfxyyyjYjhz);
    });

    // 关于中止社区戒毒（康复）程序说明
    var suspendCxsm = cloneObj(fileview);
    suspendCxsm.ele = '.suspend-cxsm-file';
    suspendCxsm.btn = '.suspend-cxsm-selector';
    suspendCxsm.localKey = 'suspendCxsm';
    statusFileViews.push(suspendCxsm);

    // 双向管控函
    var sxgkh = cloneObj(fileview);
    sxgkh.ele = '.sxgkh-file';
    sxgkh.btn = '.sxgkh-selector';
    sxgkh.localKey = 'sxgkh';
    statusFileViews.push(sxgkh);

    for (var i = 0; i < statusFileViews.length; i++) {
        statusFileViews[i].onChange = function () {
            $(this.ele).siblings(":hidden[name*='_uri']").val("");
        }
        statusFileViews[i].init();
    }
};

var resetFileView = function ($obj) {
    $obj.children('.file-view-add').addClass('hide');
    var $btnRemove = $obj.children('.file-view-remove');
    $btnRemove.removeClass('hide');
    $btnRemove.bind('click', fileviewRemove);

    var $file = $obj.children(':file');
    var fileId = $file.attr('id').split('_')[0];
    var $siblings = $(":file[id^=" + fileId + "]");
    $file.val("");
    $file.attr('id', fileId + "_" + $siblings.length);

    var $btn = $obj.children('button');
    var btnId = $btn.attr('id').split('_')[0];
    $btn.attr('id', btnId + "_" + $siblings.length);

    $obj.children('.update').text("");
    $obj.children(':hidden').val("");
    $obj.children('.error').remove();
    $obj.children('img').remove();
    var newFileView = cloneObj(fileview);
    newFileView.ele = "#" + $file.attr('id');
    newFileView.btn = "#" + $btn.attr('id');
    newFileView.localKey = $file.attr('id');
    newFileView.init();
}

var fileviewAdd = function () {
    var $siblings = $(":file[id^=" + $(this).parent().children(':file').attr('id').split('_')[0] + "]");
    if ($siblings.length >= 3) {
        layeralert("超出最大文件数量限制");
        return;
    }
    var $newObj = $(this).parent().clone();
    $newObj.appendTo($(this).parent().parent());
    resetFileView($newObj);
};

var fileviewRemove = function () {
    var $self = $(this);
    layerconfirm('确定要删除吗？', 2, '确认', function () {
        $self.parent().remove();
        layer.closeAll();
    }, function () {
        layer.closeAll();
    });
};

var STATUS_START_TIME = 'JD_START_TIME';
var STATUS_END_TIME = 'JD_END_TIME';
var SUB_STATUS_START_TIME = 'SUB_STATUS_START_TIME';
var SUB_STATUS_END_TIME = 'SUB_STATUS_END_TIME';
var ZT_SQJDZ = '社区戒毒中',
    ZT_SQKFZ = '社区康复中',
    ZT_QZJDZ = '强制戒毒中',
    ZT_ZYJDZ = '自愿戒毒中',
    ZT_SJSKZZ = '社戒社康终止',
    ZT_JYJLZ = '羁押拘留中',
    ZT_YSW = '已死亡',
    ZT_CGZ = '出国中',
    ZT_WBDYYJ = '未报到已移交',
    ZT_WFXYYYJ = '违反协议已移交',
    ZT_SHMFXZ = '社会面服刑中',
    ZT_JDSNWFXFXZ = '戒断三年未复吸服刑中',
    EJZT_QJZ = '请假中',
    EJZT_ZZ = '中止',
    EJZT_SXGKZ = '双向管控中';



var switchVisibility4StatusRelations = function(status, subStatus, trigger) {
    if (trigger == 'primary') {
        $('.status-relation,.sub-status-relation').hide();
        $('.status-relation,.sub-status-relation').find(':input:not([type=hidden])').each(function () {
            $(this).val("");
            formValid.hideErr($(this));
            formValid.hideSuccess($(this));
        });
    }
    else if (trigger == 'secondary') {
        $('.sub-status-relation').hide();
        $('.sub-status-relation :input:not([type=hidden])').each(function () {
            $(this).val("");
            formValid.hideErr($(this));
            formValid.hideSuccess($(this));
        });
    }
    else {
        $('.status-relation,.sub-status-relation').hide();
    }
    if (ZT_SQJDZ == status) {
        showRelations(status, [STATUS_START_TIME, 'USER_SUB_STATUS_ID']);
    }
    else if (ZT_SQKFZ == status) {
        showRelations(status, [STATUS_START_TIME, 'USER_SUB_STATUS_ID']);
    }
    else if (ZT_QZJDZ == status) {
        showRelations(status, [STATUS_START_TIME, STATUS_END_TIME, 'executePlace']);
    }
    else if (ZT_ZYJDZ == status) {
        showRelations(status, [STATUS_START_TIME, 'executePlace']);
    }
    else if (ZT_SJSKZZ == status) {
        showRelations(status, [STATUS_START_TIME]);
    }
    else if (ZT_JYJLZ == status) {
        showRelations(status, [STATUS_START_TIME, STATUS_END_TIME, 'detainPlace']);
    }
    else if (ZT_YSW == status) {
        showRelations(status, [STATUS_START_TIME]);
    }
    else if (ZT_CGZ == status) {
        showRelations(status, [STATUS_START_TIME, 'country']);
    }
    else if (ZT_WBDYYJ == status) {
        showRelations(status, [STATUS_START_TIME, 'wbdyyjGJS']);
    }
    else if (ZT_WFXYYYJ == status) {
        showRelations(status, [STATUS_START_TIME, 'wfxyyyjGJS']);
    }
    else if (ZT_SHMFXZ == status) {
        showRelations(status, [STATUS_START_TIME]);
    }
    else if (ZT_JDSNWFXFXZ == status) {
        showRelations(status, [STATUS_START_TIME]);
    }
    if (subStatus) {
        if (EJZT_QJZ == subStatus) {
            showRelations(subStatus, [SUB_STATUS_START_TIME, SUB_STATUS_END_TIME], true);
        }
        else if (EJZT_ZZ == subStatus) {
            showRelations(subStatus, [SUB_STATUS_START_TIME, SUB_STATUS_END_TIME, 'suspendZZCXSM', 'suspendReason'], true);
        }
        else if (EJZT_SXGKZ == subStatus) {
            showRelations(subStatus, [SUB_STATUS_START_TIME, 'SXGKH', 'sxgkReason'], true);
        }
    }
};

var validStatusRelations = function () {
    var status = $('#ustatus').children('option:selected').text();
    if (ZT_SQJDZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少协议开始时间')) {
            return false;
        }
    }
    else if (ZT_SQKFZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少协议开始时间')) {
            return false;
        }
    }
    else if (ZT_QZJDZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少起始时间')) {
            return false;
        }
        if (!nonNullValid(STATUS_END_TIME, '缺少截止时间')) {
            return false;
        }
        if (!nonNullValid('executePlace', '缺少执行地点')) {
            return false;
        }
    }
    else if (ZT_ZYJDZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少开始时间')) {
            return false;
        }
        if (!nonNullValid('executePlace', '缺少执行地点')) {
            return false;
        }
    }
    else if (ZT_SJSKZZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少终止时间')) {
            return false;
        }
    }
    else if (ZT_JYJLZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少起始时间')) {
            return false;
        }
        if (!nonNullValid(STATUS_END_TIME, '缺少截止时间')) {
            return false;
        }
        if (!nonNullValid('detainPlace', '缺少拘留地点')) {
            return false;
        }
    }
    else if (ZT_YSW == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少死亡时间')) {
            return false;
        }
    }
    else if (ZT_CGZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少出国时间')) {
            return false;
        }
        if (!nonNullValid('country', '缺少国家名称')) {
            return false;
        }
    }
    else if (ZT_WBDYYJ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少移交时间')) {
            return false;
        }
        if (isUriEmpty('wbdyyjGJS_uri') && !nonNullValid('wbdyyjGJS', '缺少告诫书')) {
            return false;
        }
        if (isUriEmpty('wbdyyjYQWBDZM_uri') && !nonNullValid('wbdyyjYQWBDZM', '缺少逾期未报到证明')) {
            return false;
        }
        if (!checkInputAndUri('wbdyyjYJHZ[]', 'wbdyyjYJHZ_uri[]', '缺少移交回执')) {
            return false;
        }
    }
    else if (ZT_WFXYYYJ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少移交时间')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjGJS_uri') && !nonNullValid('wfxyyyjGJS', '缺少告诫书')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjXDJCTZS_uri') && !nonNullValid('wfxyyyjXDJCTZS', '缺少吸毒检测通知书')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjYZWFXYZM_uri') && !nonNullValid('wfxyyyjYZWFXYZM', '缺少严重违反协议证明')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjYJHZ_uri') && !nonNullValid('wfxyyyjYJHZ', '缺少移交回执')) {
            return false;
        }
    }
    else if (ZT_SHMFXZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少开始时间')) {
            return false;
        }
    }
    else if (ZT_JDSNWFXFXZ == status) {
        if (!nonNullValid(STATUS_START_TIME, '缺少开始时间')) {
            return false;
        }
    }
    var subStatus = $('#userSubStatusSelector').children('option:selected').text();
    if (subStatus) {
        if (EJZT_QJZ == subStatus) {
            if (!nonNullValid(SUB_STATUS_START_TIME, '缺少起始时间')) {
                return false;
            }
            if (!nonNullValid(SUB_STATUS_END_TIME, '缺少截止时间')) {
                return false;
            }
        }
        else if (EJZT_ZZ == subStatus) {
            if (!nonNullValid(SUB_STATUS_START_TIME, '缺少起始时间')) {
                return false;
            }
            if (!nonNullValid(SUB_STATUS_END_TIME, '缺少截止时间')) {
                return false;
            }
            if (isUriEmpty('suspendZZCXSM_uri') && !nonNullValid('suspendZZCXSM', '缺少中止程序说明')) {
                return false;
            }
            if (!nonNullValid('suspendReason', '缺少中止原因')) {
                return false;
            }
        }
        else if (EJZT_SXGKZ == subStatus) {
            if (!nonNullValid(SUB_STATUS_START_TIME, '缺少开始时间')) {
                return false;
            }
            if (isUriEmpty('SXGKH_uri') && !nonNullValid('SXGKH', '缺少双向管控函')) {
                return false;
            }
            if (!nonNullValid('sxgkReason', '缺少双向管控原因')) {
                return false;
            }
        }
    }
    return true;
};

var getObjByName = function (name) {
    return $(":input[name='" + name + "']");
};

var nonNullValid = function (inputName, errMsg, inputType) {
    var inputs = getObjByName(inputName);
    if (!inputs) {
        return false;
    }
    var result = true;
    for (var i = 0; i < inputs.length; i++) {
        var input = $(inputs[i]);
        if (!inputType || 'text' == inputType) {
            result = checkInputEmpty(input);
        }
        else if ('select' == inputType) {
            result = checkSelectEmpty(input);
        }
        if (!result) {
            formValid.showErr(input, errMsg);
            break;
        } else {
            formValid.showSuccess(input);
        }
    }
    return result;
};

var checkInputAndUri = function (inputName, uriName, errMsg) {
    $inputs = getObjByName(inputName);
    $uris = getObjByName(uriName);
    if ($inputs.length != $uris.length) {
        return false;
    }
    for (var i = 0; i < $inputs.length; i++) {
        $uri = $($uris[i]);
        if (checkInputEmpty($uri)) {
            continue;
        }
        $input = $($inputs[i]);
        if (!checkInputEmpty($input)) {
            formValid.showErr($input, errMsg);
            return false;
        } else {
            formValid.showSuccess($input);
        }
    }
    return true;
}

var isUriEmpty = function (inputName) {
    var obj = getObjByName(inputName);
    if (!obj) {
        return true;
    }
    return !checkInputEmpty(obj);
};

var showRelations = function(status, selectors, isSub) {
    if (!selectors) {
        return;
    }
    resetDateElements(isSub);
    if (selectors instanceof Array) {
        $(selectors).each(function (i) {
            var obj = getObjByName(selectors[i]);
            formValid.hideErr(obj);
            obj.closest('.control-group').show();
            if (isDateControl(selectors[i])) {
                dealWithDate(status, obj);
            }
        })
    } else {
        var obj = getObjByName(selectors);
        formValid.hideErr(obj);
        obj.closest('.control-group').show();
        if (isDateControl(selectors[i])) {
            dealWithDate(status, obj);
        }
    }
};

var isDateControl = function (controlName) {
    if (!controlName) {
        return false;
    }
    return controlName == STATUS_START_TIME || controlName == STATUS_END_TIME
        || controlName == SUB_STATUS_START_TIME || controlName == SUB_STATUS_END_TIME;
}

var resetDateElements = function (isSub) {
    if (!isSub) {
        $('.date-member').hide();
        $('.date-label').hide();
    } else {
        $('.sub-status-relation .date-member').hide();
        $('.sub-status-relation .date-label').hide();
    }
}

var dealWithDate = function (status, obj) {
    obj.show();
    $('.date-label[status=' + status + ']').show();
    if (obj.siblings('[type=date]:visible').length > 0) {
        obj.siblings('span').show();
    }
};