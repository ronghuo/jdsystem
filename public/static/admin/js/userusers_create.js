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
    var utype_id_218 = $('#utype_id_218');
    var ustatus = $('#ustatus');

    if(ustatus.val() == '7'){
        utype_id_218.show();
    }

    ustatus.on('change', function () {
       var val = $(this).val();
       if(val == '7'){
           utype_id_218.show();
       }else{
           utype_id_218.hide();
       }
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
            //alias_name = $('#alias_name'),
            gender = $('#gender'),
            utype = $('#utype'),
            utype_id_218 = $('#utype_id_218'),
            jd_start_time = $('#jd_start_time'),
            jd_end_time = $('#jd_end_time'),
            nationality = $('#nationality'),
            nation = $('#nation'),//se
            //height = $('#height'),
            //education = $('#education'),//se
            //job_status = $('#job_status'),//se
            //martial_status = $('#martial_status'),//se
            domicile_address = $('#domicile_address'),
            domicile_police_name = $('#domicile_police_name'),
            //domicile_police_code = $('#domicile_police_code'),
            live_address = $('#live_address'),
            live_police_name = $('#live_police_name'),
            //live_police_code = $('#live_police_code'),
            //drug_type = $('#drug_type'),//se
            //narcotics_type = $('#narcotics_type'),//se
            //police_code = $('#police_code'),
            //police_name = $('#police_name'),
            // police_mobile = $('#police_mobile'),
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

        // if(!checkInputEmpty(alias_name)){
        //     formValid.showErr(alias_name,'缺少绰号');
        //     return false;
        // }else{
        //     formValid.showSuccess(alias_name);
        // }

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
        var statusId = ustatus.val()
        if(statusId == '7' && !checkSelectEmpty(utype_id_218)){
            formValid.showErr(utype_id_218,'请选择社区康复年限');
            return false;
        }else{
            formValid.showSuccess(utype_id_218);
        }
        // if (statusId ==6 || statusId==7 || statusId==8){
        //     //jd_end_time
        //     if(!checkInputEmpty(jd_start_time)){
        //         formValid.showErr(jd_start_time,'缺少起始时间');
        //         return false;
        //     }else{
        //         formValid.showSuccess(jd_start_time);
        //     }
        //
        //     if(!checkInputEmpty(jd_end_time)){
        //         formValid.showErr(jd_end_time,'缺少截止时间');
        //         return false;
        //     }else{
        //         formValid.showSuccess(jd_end_time);
        //     }
        // }
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


        // if(!checkInputEmpty(height)){
        //     formValid.showErr(height,'缺少身高');
        //     return false;
        // }else{
        //     formValid.showSuccess(height);
        // }

        // if(!checkSelectEmpty(education)){
        //     formValid.showErr(education,'缺少文化程度');
        //     return false;
        // }else{
        //     formValid.showSuccess(education);
        // }
        // if(!checkSelectEmpty(job_status)){
        //     formValid.showErr(job_status,'缺少就业信息');
        //     return false;
        // }else{
        //     formValid.showSuccess(job_status);
        // }
        // if(!checkSelectEmpty(martial_status)){
        //     formValid.showErr(martial_status,'缺少婚姻状况');
        //     return false;
        // }else{
        //     formValid.showSuccess(martial_status);
        // }
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

        // if(!checkInputEmpty(domicile_police_code)){
        //     formValid.showErr(domicile_police_code,'缺少户籍地派出所代码');
        //     return false;
        // }else{
        //     formValid.showSuccess(domicile_police_code);
        // }

        // 居住地 areas2
        var areas2 = $('#areas2'),
            lv3 = areas2.children('select.lv3').val();
        if(!lv3){
            formValid.showErr(areas2,'缺少居住地');
            return false;
        }else{
            formValid.showSuccess(areas2);
        }

        if(!checkInputEmpty(live_address)){
            formValid.showErr(live_address,'缺少居住地详细地址');
            return false;
        }else{
            formValid.showSuccess(live_address);
        }

        if(!checkInputEmpty(live_police_name)){
            formValid.showErr(live_police_name,'缺少居住地派出所名称');
            return false;
        }else{
            formValid.showSuccess(live_police_name);
        }

        // if(!checkInputEmpty(live_police_code)){
        //     formValid.showErr(live_police_code,'缺少居住地派出所代码');
        //     return false;
        // }else{
        //     formValid.showSuccess(live_police_code);
        // }

        // if(!checkSelectEmpty(drug_type)){
        //     formValid.showErr(drug_type,'缺少吸毒方式');
        //     return false;
        // }else{
        //     formValid.showSuccess(drug_type);
        // }
        // if(!checkSelectEmpty(narcotics_type)){
        //     formValid.showErr(narcotics_type,'缺少毒品种类');
        //     return false;
        // }else{
        //     formValid.showSuccess(narcotics_type);
        // }


        // 所在社区 areas3
        var areas3 = $('#areas3'),
            lv1 = areas3.children('select.lv1').val();
        if(!lv1){
            formValid.showErr(areas3,'缺少所在社区');
            return false;
        }else{
            formValid.showSuccess(areas3);
        }


        // 所属警务 areas4

        var areas4 = $('#areas4'),
            dmmcs = areas4.find('select:not(:first)');
        if (!$(dmmcs[0]).val()) {
            formValid.showErr(areas4,'缺少所属禁毒办');
            return false;
        }else{
            formValid.showSuccess(areas4);
        }



        // if(!checkInputEmpty(police_code)){
        //     formValid.showErr(police_code,'缺少责任民警警号');
        //     return false;
        // }else{
        //     formValid.showSuccess(police_code);
        // }
        //
        // if(!checkInputEmpty(police_name)){
        //     formValid.showErr(police_name,'缺少责任民警姓名');
        //     return false;
        // }else{
        //     formValid.showSuccess(police_name);
        // }
        //
        // if(!checkInputEmpty(police_mobile)){
        //     formValid.showErr(police_mobile,'缺少责任民警联系电话');
        //     return false;
        // }else{
        //     formValid.showSuccess(police_mobile);
        // }

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


        return true;
    });
});

/**
 * 初始化图片查看器
 */
var initImageViewer = function() {
    fileview.init();

    // 未报到已移交-告诫书
    var wbdyyjGjs = cloneObj(fileview);
    wbdyyjGjs.ele = '.wbdyyj-gjs-file';
    wbdyyjGjs.btn = '.wbdyyj-gjs-selector';
    wbdyyjGjs.localKey = 'wbdyyjGjs';
    wbdyyjGjs.init();

    // 未报到已移交-逾期未报到证明
    var wbdyyjYqwbdzm = cloneObj(fileview);
    wbdyyjYqwbdzm.ele = '.wbdyyj-yqwbdzm-file';
    wbdyyjYqwbdzm.btn = '.wbdyyj-yqwbdzm-selector';
    wbdyyjYqwbdzm.localKey = 'wbdyyjYqwbdzm';
    wbdyyjYqwbdzm.init();

    // 未报到已移交-移交回执
    var wbdyyjYjhz = cloneObj(fileview);
    wbdyyjYjhz.ele = '.wbdyyj-yjhz-file';
    wbdyyjYjhz.btn = '.wbdyyj-yjhz-selector';
    wbdyyjYjhz.localKey = 'wbdyyjYjhz';
    wbdyyjYjhz.init();

    // 违反协议已移交-告诫书
    var wfxyyyjGjs = cloneObj(fileview);
    wfxyyyjGjs.ele = '.wfxyyyj-gjs-file';
    wfxyyyjGjs.btn = '.wfxyyyj-gjs-selector';
    wfxyyyjGjs.localKey = 'wfxyyyjGjs';
    wfxyyyjGjs.init();

    // 违反协议已移交-吸毒检测通知书
    var wfxyyyjXdjctzs = cloneObj(fileview);
    wfxyyyjXdjctzs.ele = '.wfxyyyj-xdjctzs-file';
    wfxyyyjXdjctzs.btn = '.wfxyyyj-xdjctzs-selector';
    wfxyyyjXdjctzs.localKey = 'wfxyyyjXdjctzs';
    wfxyyyjXdjctzs.init();

    // 违反协议已移交-严重违反协议证明
    var wfxyyyjYzwfxyzm = cloneObj(fileview);
    wfxyyyjYzwfxyzm.ele = '.wfxyyyj-yzwfxyzm-file';
    wfxyyyjYzwfxyzm.btn = '.wfxyyyj-yzwfxyzm-selector';
    wfxyyyjYzwfxyzm.localKey = 'wfxyyyjYzwfxyzm';
    wfxyyyjYzwfxyzm.init();

    // 违反协议已移交-移交回执
    var wfxyyyjYjhz = cloneObj(fileview);
    wfxyyyjYjhz.ele = '.wfxyyyj-yjhz-file';
    wfxyyyjYjhz.btn = '.wfxyyyj-yjhz-selector';
    wfxyyyjYjhz.localKey = 'wfxyyyjYjhz';
    wfxyyyjYjhz.init();

    // 关于中止社区戒毒（康复）程序说明
    var suspendCxsm = cloneObj(fileview);
    suspendCxsm.ele = '.suspend-cxsm-file';
    suspendCxsm.btn = '.suspend-cxsm-selector';
    suspendCxsm.localKey = 'suspendCxsm';
    suspendCxsm.init();

    // 关于终止社区戒毒（康复）程序说明
    var terminateCxsm = cloneObj(fileview);
    terminateCxsm.ele = '.terminate-cxsm-file';
    terminateCxsm.btn = '.terminate-cxsm-selector';
    terminateCxsm.localKey = 'terminateCxsm';
    terminateCxsm.init();

    // 双向管控函
    var sxgkh = cloneObj(fileview);
    sxgkh.ele = '.sxgkh-file';
    sxgkh.btn = '.sxgkh-selector';
    sxgkh.localKey = 'sxgkh';
    sxgkh.init();

    // 解除书
    var jcs = cloneObj(fileview);
    jcs.ele = '.jcs-file';
    jcs.btn = '.jcs-selector';
    jcs.localKey = 'jcs';
    jcs.init();
};

var START_TIME = 'JD_START_TIME';
var END_TIME = 'JD_END_TIME';

var switchVisibility4StatusRelations = function(status, subStatus, trigger) {
    if (trigger == 'primary') {
        $('.status-relation,.sub-status-relation').hide();
        $('.status-relation,.sub-status-relation').find(':input').each(function () {
            $(this).val('');
            formValid.hideErr($(this));
            formValid.hideSuccess($(this));
        });
    }
    else if (trigger == 'secondary') {
        $('.sub-status-relation').hide();
        $('.sub-status-relation :input').each(function () {
            $(this).val('');
            formValid.hideErr($(this));
            formValid.hideSuccess($(this));
        });
    }
    else {
        $('.status-relation,.sub-status-relation').hide();
    }
    if ('社区戒毒中' == status) {
        showRelations(status, [START_TIME, END_TIME, 'USER_SUB_STATUS_ID']);
    }
    else if ('社区康复中' == status) {
        showRelations(status, [START_TIME, END_TIME, 'USER_SUB_STATUS_ID']);
    }
    else if ('强制戒毒中' == status) {
        showRelations(status, [START_TIME, END_TIME, 'executePlace']);
    }
    else if ('自愿戒毒中' == status) {
        showRelations(status, [START_TIME, 'executePlace']);
    }
    else if ('服刑中' == status) {
        showRelations(status, [START_TIME, END_TIME, 'servePlace']);
    }
    else if ('拘留中' == status) {
        showRelations(status, [START_TIME, END_TIME, 'detainPlace']);
    }
    else if ('已死亡' == status) {
        showRelations(status, [START_TIME]);
    }
    else if ('出国中' == status) {
        showRelations(status, [START_TIME, 'country']);
    }
    else if ('未报到已移交' == status) {
        showRelations(status, [START_TIME, 'wbdyyjGJS']);
    }
    else if ('违反协议已移交' == status) {
        showRelations(status, [START_TIME, 'wfxyyyjGJS']);
    }
    if (subStatus) {
        if ('请假中' == subStatus) {
            showRelations(status, ['leaveBeginTime']);
        }
        else if ('中止' == subStatus) {
            showRelations(status, ['suspendBeginTime', 'suspendZZCXSM', 'suspendReason']);
        }
        else if ('终止' == subStatus) {
            showRelations(status, ['terminateTime', 'terminateZZCXSM', 'terminateReason']);
        }
        else if ('双向管控中' == subStatus) {
            showRelations(status, ['sxgkBeginTime', 'SXGKH', 'sxgkReason']);
        }
        else if ('已解除社区戒毒' == subStatus) {
            showRelations(status, ['relieveTime', 'JCS']);
        }
        else if ('已解除社区康复' == subStatus) {
            showRelations(status, ['relieveTime', 'JCS']);
        }
    }
};

var validStatusRelations = function () {
    var status = $('#ustatus').children('option:selected').text();
    if ('社区戒毒中' == status) {
        if (!nonNullValid('JD_START_TIME', '缺少起始时间')) {
            return false;
        }
        if (!nonNullValid('JD_END_TIME', '缺少截止时间')) {
            return false;
        }
    }
    else if ('社区康复中' == status) {
        if (!nonNullValid('JD_START_TIME', '缺少起始时间')) {
            return false;
        }
        if (!nonNullValid('JD_END_TIME', '缺少截止时间')) {
            return false;
        }
    }
    else if ('强制戒毒中' == status) {
        if (!nonNullValid('qzjdBeginTime', '缺少强制戒毒起始时间')) {
            return false;
        }
        if (!nonNullValid('qzjdEndTime', '缺少强制戒毒截止时间')) {
            return false;
        }
        if (!nonNullValid('executePlace', '缺少强制戒毒执行地点')) {
            return false;
        }
    }
    else if ('自愿戒毒中' == status) {
        if (!nonNullValid('zyjdBeginTime', '缺少自愿戒毒开始时间')) {
            return false;
        }
        if (!nonNullValid('executePlace', '缺少自愿戒毒执行地点')) {
            return false;
        }
    }
    else if ('服刑中' == status) {
        if (!nonNullValid('serveBeginTime', '缺少服刑起始时间')) {
            return false;
        }
        if (!nonNullValid('serveEndTime', '缺少服刑截止时间')) {
            return false;
        }
        if (!nonNullValid('servePlace', '缺少服刑地点')) {
            return false;
        }
    }
    else if ('拘留中' == status) {
        if (!nonNullValid('detainBeginTime', '缺少拘留起始时间')) {
            return false;
        }
        if (!nonNullValid('detainEndTime', '缺少拘留截止时间')) {
            return false;
        }
        if (!nonNullValid('detainPlace', '缺少拘留地点')) {
            return false;
        }
    }
    else if ('已死亡' == status) {
        if (!nonNullValid('deathTime', '缺少死亡时间')) {
            return false;
        }
    }
    else if ('出国中' == status) {
        if (!nonNullValid('abroadTime', '缺少出国时间')) {
            return false;
        }
        if (!nonNullValid('country', '缺少国家名称')) {
            return false;
        }
    }
    else if ('未报到已移交' == status) {
        if (!nonNullValid('transferTime', '缺少移交时间')) {
            return false;
        }
        if (isUriEmpty('wbdyyjGJS') && !nonNullValid('wbdyyjGJS', '缺少告诫书')) {
            return false;
        }
        if (isUriEmpty('wbdyyjYQWBDZM') && !nonNullValid('wbdyyjYQWBDZM', '缺少逾期未报到证明')) {
            return false;
        }
        if (isUriEmpty('wbdyyjYJHZ') && !nonNullValid('wbdyyjYJHZ', '缺少移交回执')) {
            return false;
        }
    }
    else if ('违反协议已移交' == status) {
        if (!nonNullValid('transferTime', '缺少移交时间')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjGJS') && !nonNullValid('wfxyyyjGJS', '缺少告诫书')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjXDJCTZS') && !nonNullValid('wfxyyyjXDJCTZS', '缺少吸毒检测通知书')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjYZWFXYZM') && !nonNullValid('wfxyyyjYZWFXYZM', '缺少严重违反协议证明')) {
            return false;
        }
        if (isUriEmpty('wfxyyyjYJHZ') && !nonNullValid('wfxyyyjYJHZ', '缺少移交回执')) {
            return false;
        }
    }
    var subStatus = $('#userSubStatusSelector').children('option:selected').text();
    if (subStatus) {
        if ('请假中' == subStatus) {
            if (!nonNullValid('leaveBeginTime', '缺少请假起始时间')) {
                return false;
            }
            if (!nonNullValid('leaveEndTime', '缺少请假截止时间')) {
                return false;
            }
        }
        else if ('中止' == subStatus) {
            if (!nonNullValid('suspendBeginTime', '缺少中止起始时间')) {
                return false;
            }
            if (!nonNullValid('suspendEndTime', '缺少中止截止时间')) {
                return false;
            }
            if (isUriEmpty('suspendZZCXSM') && !nonNullValid('suspendZZCXSM', '缺少中止程序说明')) {
                return false;
            }
            if (!nonNullValid('suspendReason', '缺少中止原因')) {
                return false;
            }
        }
        else if ('终止' == subStatus) {
            if (!nonNullValid('terminateTime', '缺少终止时间')) {
                return false;
            }
            if (!nonNullValid('terminateZZCXSM', '缺少终止程序说明')) {
                return false;
            }
            if (!nonNullValid('terminateReason', '缺少终止原因')) {
                return false;
            }
        }
        else if ('双向管控中' == subStatus) {
            if (!nonNullValid('sxgkBeginTime', '缺少双向管控开始时间')) {
                return false;
            }
            if (isUriEmpty('SXGKH') && !nonNullValid('SXGKH', '缺少双向管控函')) {
                return false;
            }
            if (!nonNullValid('sxgkReason', '缺少双向管控原因')) {
                return false;
            }
        }
        else if ('已解除社区戒毒' == subStatus) {
            if (!nonNullValid('relieveTime', '缺少解除时间')) {
                return false;
            }
            if (isUriEmpty('JCS') && !nonNullValid('JCS', '缺少解除书')) {
                return false;
            }
        }
        else if ('已解除社区康复' == subStatus) {
            if (!nonNullValid('relieveTime', '缺少解除时间')) {
                return false;
            }
            if (isUriEmpty('JCS') && !nonNullValid('JCS', '缺少解除书')) {
                return false;
            }
        }
    }
    return true;
};

var getObjByName = function (name) {
    return $(':input[name=' + name + ']');
};

var nonNullValid = function (inputName, errMsg, inputType) {
    var obj = getObjByName(inputName);
    if (!obj) {
        return false;
    }
    var result = true;
    if (!inputType || 'text' == inputType) {
        result = checkInputEmpty(obj);
    }
    else if ('select' == inputType) {
        result = checkSelectEmpty(obj);
    }
    if (!result) {
        formValid.showErr(obj, errMsg);
    } else {
        formValid.showSuccess(obj);
    }
    return result;
};

var isUriEmpty = function (inputName) {
    var obj = getObjByName(inputName + '_uri');
    if (!obj) {
        return true;
    }
    return !checkInputEmpty(obj);
};

var showRelations = function(status, selectors) {
    if (!selectors) {
        return;
    }
    resetDateElements();
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
    return controlName == START_TIME || controlName == END_TIME;
}

var resetDateElements = function () {
    $('.date-member').hide();
    $('.date-label').hide();
}

var dealWithDate = function (status, obj) {
    obj.show();
    $('.date-label[status=' + status + ']').show();
    if ($('.date-member[type=date]:visible').length == 2)
        $('#dateConnector').show();
};