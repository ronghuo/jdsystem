
//+++++++++++++++++++++layer 弹出层插件相关  begin++++++++++++++++++++++++++++++

//信息显示,2s 后自动关闭
function layermsg(text, n) {
    layer.msg(text, 2, {tcolor: n});
}
//父窗口
function playermsg(text, n) {
    window.parent.layermsg(text, n);
}

//不同位置的信息显示,2s 后自动关闭
function layermsgpos(text, pos, n) {
    var position = {0: 'm-top', 1: 'left-top', 2: 'top', 3: 'right-top', 4: 'right-bottom', 5: 'bottom', 6: 'left-bottom', 7: 'left'};
    layer.msg(text, 3, {
        type: 9, rate: position[pos], shade: [0], tcolor: n
    });
}
//父窗口
function playermsgpos(text, pos, n) {
    window.parent.ayermsgpos(text, pos, n);
}

//确定操作
function layeralert(text, n, title) {
    layer.alert(text, n, title);
}
//子窗口关闭
function closePlayer() {
    window.parent.closelayer();
}
//总的关闭方法
function closelayer() {
    $('a.xubox_close', '.xubox_layer').click();
}
//弹出引用页面
function layeriframe(url, title, w, h) {
    $.layer({
        type: 2,
        title: title,
        shadeClose: true,
        maxmin: false,
        fix: false,
        area: [w + 'px', h + 'px'],
        iframe: {src: url}
    });
}
//操作确认方法
function layerconfirm(text, n, title, fnyes, fnno) {

    $.layer({
        shade: [0],
        area: ['auto', 'auto'],
        tcolor: n,
        title: title,
        dialog: {
            msg: text,
            btns: 2,
            type: 4,
            btn: ['确定', '取消'],
            yes: fnyes,
            no: fnno
        }
    });
}
//测试
function test() {

    layerconfirm('确定要删除吗？', 2, '确认', function () {
        layer.msg('确定', 2, {tcolor: 3});
    }, function () {
        layer.msg('取消', 2, {tcolor: 4});
    });
}

//弹出页面div内容
function layerdiv(options) {
    var defaults = {
        type: 1,
        title: '',
        area: ['200px', '200px'],
        offset: ['', '50%'],
        border: [6, 0.3, '#000'], ////默认边框
        shade: [0.1, '#000'], //遮罩
        fix: true,
        closeBtn: [0, true],
        shift: ['m-top', 300, 1], // || ['bottom', 300, 1]
        html: '',
        pclosebtn: '',
        callback: function () {
        }
    };

    var sets = $.extend({}, defaults, options);

    var fnpop = function (opts) {

        var pageii = $.layer({
            type: opts.type,
            title: opts.title,
            area: opts.area,
            offset: opts.offset,
            border: opts.border,
            shade: opts.shade,
            fix: opts.fix,
            closeBtn: opts.closeBtn,
            shift: opts.shift,
            page: {
                html: opts.html
            }, success: function (elem) {
                elem.find(opts.pclosebtn).on('click', function () {
                    layer.close(pageii);
                    opts.callback && opts.callback();
                });
            }
        });
        return pageii;
    };

    return fnpop(sets);
}

//+++++++++++++++++++++layer 弹出层插件相关  end++++++++++++++++++++++++++++++
//+++++++++++++++++++页内提示
var pageMesg = {
    init: function () {

        if ($('#page-mesg').find('.alert').length <= 0) {
            $('#page-mesg').html('<div class="alert hide"><button class="close" data-dismiss="alert"></button><span></span></div>');
        }
    },
    show: function (text, type) {
        pageMesg.init();
        var _class = ['alert-error', 'alert-success', '', 'alert-info'],
                _alert = $('#page-mesg').find('.alert');
        _alert.addClass(_class[type]).removeClass(_class[1 - type]).children('span').html(text).end().show();
        $('#page-mesg').show();
        scrollTo();
        if (type == 1) {
            setTimeout(function () {
                pageMesg.hide();
            }, 2000);
        }
    },
    hide: function () {
        $('#page-mesg').hide();
    }
};
//++++++++++页内提示
//++++++++++滑动开关操作+++begin++
var toggleBtn = {
    checkOn: function (obj) {
        setTimeout(function () {
            obj.attr('checked', true).parent('div').animate({'left': 0}, 600);
        }, 500);
    },
    checkOff: function (obj) {
        setTimeout(function () {
            obj.attr('checked', false).parent('div').animate({'left': '-50%'}, 600);
        }, 500);
    }
};
//++++++++++滑动开关操作+++end++
//+++++++++++表单验证提示
var formValid = {
    //显示错误
    showErr: function (obj, txt, ele) {
        var parent = obj.parent();
        if (!ele) {
            ele = 'help-inline';
        }
//        console.log(parent);
        if (parent.children('.' + ele).length <= 0) {
            parent.append('<div class="' + ele + ' hide"></div>');
        }
        obj.focus();
        $('.' + ele, parent).text(txt).addClass('error').removeClass('icon-ok hide');
        parent.closest('.control-group').addClass('error').removeClass('success');
        scrollTo(obj, -100);
    },
    hideErr: function (obj, ele) {
        var parent = obj.parent();
        if (!ele) {
            ele = 'help-inline';
        }
        $('.' + ele, parent).remove();
        parent.closest('.control-group').removeClass('error');
    },
    //显示成功
    showSuccess: function (obj, ele) {
        var parent = obj.parent();
        if (!ele) {
            ele = 'help-inline';
        }
        if (parent.children('.' + ele).length <= 0) {
            parent.append('<div class="' + ele + ' hide"></div>');
        }

        $('.' + ele, parent).text('').addClass('icon-ok').removeClass('error hide');
        parent.closest('.control-group').addClass('success').removeClass('error');
    },
    hideSuccess: function (obj, ele) {
        var parent = obj.parent();
        if (!ele) {
            ele = 'help-inline';
        }
        $('.' + ele, parent).remove();
        parent.closest('.control-group').removeClass('icon-ok').removeClass('success');
    }
};

function scrollTo(el, offeset) {
    var pos = el ? el.offset().top : 0;
    jQuery('html,body').animate({
        scrollTop: pos + (offeset ? offeset : 0)
    }, 'slow');
};

/////////////////////////

//中文key值
function getCNkey(k) {
    var keys = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];
    if (k <= 10) {
        return keys[k];
    }
    return k;
}

//同步的ajax
function noasyncajax(url, data, callback) {
    $.ajax({
        'type': 'post', 'url': url,
        'async': false, 'cache': false,
        'dataType': 'json',
        'data': data,
        'success': callback
    });
}
//隐藏表单的提示
function hideFormHelp() {
    var contp = $('.control-group');
    contp.removeClass('success error').find('.valid').removeClass('ok valid');
}

//鼠标坐标
function mousePos(e) {
    var e = event || window.event;
    var scrollX = document.documentElement.scrollLeft || document.body.scrollLeft;
    var scrollY = document.documentElement.scrollTop || document.body.scrollTop;
    var x = e.pageX || e.clientX + scrollX;
    var y = e.pageY || e.clientY + scrollY;

    return {'x': x, 'y': y};
}
;

/**
 * 浏览器检查
 * ua(window.navigator.userAgent) 
 * @param {type} useragent
 * @returns {String}
 */
function ua(useragent) {
    if (/MicroMessenger/i.test(useragent)) {
        return "wechat";
    }
    if (/firefox/i.test(useragent)) {
        return "firefox";
    }
    if (/chrome/i.test(useragent)) {
        return "chrome";
    }
    if (/opera/i.test(useragent)) {
        return "opera";
    }
    if (/safari/i.test(useragent)) {
        return "safari";
    }
    if (/msie 6/i.test(useragent)) {
        return "IE6";
    }
    if (/msie 7/i.test(useragent)) {
        return "IE7";
    }
    if (/msie 8/i.test(useragent)) {
        return "IE8";
    }
    if (/msie 9/i.test(useragent)) {
        return "IE9";
    }
    if (/msie 10/i.test(useragent)) {
        return "IE10";
    }
    if (/rv\:11/i.test(useragent)) {
        return "IE11";
    }
    return "other";
}
//计算天数差的函数，通用  
function  DateDiff(sDate1, sDate2) {    //sDate1和sDate2是2006-12-18格式  
    var aDate, oDate1, oDate2, iDays;
    aDate = sDate1.split("-");
    oDate1 = new Date(aDate[1] + '-' + aDate[2] + '-' + aDate[0]);   //转换为12-18-2006格式  
    aDate = sDate2.split("-");
    oDate2 = new Date(aDate[1] + '-' + aDate[2] + '-' + aDate[0]);
    iDays = parseInt(Math.abs(oDate1 - oDate2) / 1000 / 60 / 60 / 24);   //把相差的毫秒数转换为天数  
    return  iDays;
}

//日期转为时间戳
function strto_time(str_time) {
    var new_str = str_time.replace(/:/g, '-');
    new_str = new_str.replace(/ /g, '-');
    var arr = new_str.split("-");
//    console.log(new_str);
    var datum = new Date(Date.UTC(arr[0], arr[1] - 1, arr[2], arr[3] - 8, arr[4], arr[5]));
    return strtotime = datum.getTime() / 1000;
}
//时间戳转为日期
function date_time(unixtime) {
    var timestr = new Date(parseInt(unixtime) * 1000);
    var datetime = timestr.toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
    return datetime;
}
//比较两个日期的大小
function aGtB(d1,d2){
    //console.log(Date.parse(d1.replace(/-/g, "/")));
    //console.log(Date.parse(d2.replace(/-/g, "/")));
    return Date.parse(d1.replace(/-/g, "/")) > Date.parse(d2.replace(/-/g, "/"));
}
/**
 * 时间加减
 * @param {type} hours
 * @param {type} add
 * @returns {unresolved}
 */
function timesadd(hours, add) {
    var tmp = hours.split(':');
    var h = Math.floor(add / 60);

    tmp[0] = parseInt(tmp[0]) + h;
    tmp[1] = parseInt(tmp[1]) + add - h * 60;

    if (tmp[1] >= 60) {
        tmp[1] = tmp[1] - 60;
        tmp[0] += 1;
    }

    if (tmp[0] > 23) {
        tmp[0] = 0;
    }
    tmp[0] = tmp[0] < 10 ? '0' + tmp[0] : tmp[0];
    tmp[1] = tmp[1] < 10 ? '0' + tmp[1] : tmp[1];
    return tmp.join(':');
}

//判断是否在数组中
Array.prototype.S = String.fromCharCode(2);
Array.prototype.in_array = function (e) {
    var r = new RegExp(this.S + e + this.S);
    return (r.test(this.S + this.join(this.S) + this.S));
};

//删除数组中的元素
function delEleInArray(arr, ele) {
    var len = arr.length, i = 0;
    for (; i < len; i++) {
        if (arr[i] == ele) {
            arr.splice(i, 1);
            break;
        }
    }
    return arr;
}

//全选反选
function checkAll(obj, val) {
    obj.each(function () {
        this.checked = val;
    });
}
//检查input是否为空
function checkInputEmpty(obj) {

    if (!obj.val() || obj.val() == '') {
        return false;
    } else {
        return true;
    }
}
//检查select是否为空
function checkSelectEmpty(obj) {
    var val = obj.find('option:selected').val();
    if (!val || val == '' || val == 0) {
        return false;
    } else {
        return true;
    }
}
//检查radio是否为空
function checkRadioEmpty(obj) {
    var val = obj.filter(':checked').val();
    if (!val || val == '') {
        return false;
    } else {
        return true;
    }
}
//检查checkbox是否为空
function checkCheckboxEmpty(obj) {
    var chedlen = obj.filter(':checked').length;
    if (chedlen <= 0) {
        return false;
    } else {
        return true;
    }
}
//循环检查是否为空
function loopCheckEmpty(obj) {
    var res = true, len = obj.length, th = 0;
    for (var i = 0; i < len; i++) {
        var val = obj.eq(i).val();
        if (!val || val == '') {
            th = i;
            res = false;
        }
    }
    return res;
}

//是否是空的对象
function isEmptyObj(obj) {
    for (var i in obj) {
        if (obj[i]) {
            return false;
        }
    }
    return true;
}


//验证数字
function isNum(obj) {
    return /^\d+/.test(obj.val());
}
//验证数字
function isNum2(obj) {
    return /^[0-9]+/.test(obj.val());
}
//验证手机号
function isPhone(obj) {
    return /^1[3-9]{1}[0-9]{9}$/.test(obj.val());
}
//验证手机号
function isPhone2(p) {
    return /^1[3-9]{1}[0-9]{9}$/.test(p);
}
//验证邮箱
function isMail(obj) {
    var mailreg = /^[0-9a-z][_.0-9a-z-]{0,31}@([0-9a-z][0-9a-z-]{0,30}[0-9a-z]\.){1,4}[a-z]{2,4}$/gi;
    return mailreg.test(obj.val());
}

function isIdcard(card) {
    var Errors = new Array(
            "验证通过!",
            "身份证号码位数不对!",
            "身份证号码出生日期超出范围或含有非法字符!",
            "身份证号码校验错误!",
            "身份证地区非法!"
            );
    var area = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外"}
    var idcard, Y, JYM;
    var S, M;
    var idcard_array = new Array();
    idcard_array = idcard.split("");
    // 地区检验
    if (area[parseInt(idcard.substr(0, 2))] == null) {
        return false;// Errors[4];
    }
// 身份号码位数及格式检验
    switch (idcard.length) {
        case 15:
            if ((parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0 || ((parseInt(idcard.substr(6, 2)) + 1900) % 100 == 0 && (parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0)) {
                ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}$/;// 测试出生日期的合法性
            } else {
                ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}$/;// 测试出生日期的合法性
            }
            if (ereg.test(idcard)) {
                return true;// Errors[0];
            } else {
                return false;// Errors[2];
            }
            break;


        case 18:
            // 18位身份号码检测
            // 出生日期的合法性检查
            // 闰年月日:((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))
            // 平年月日:((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))
            if (parseInt(idcard.substr(6, 4)) % 4 == 0 || (parseInt(idcard.substr(6, 4)) % 100 == 0 && parseInt(idcard.substr(6, 4)) % 4 == 0)) {
                ereg = /^[1-9][0-9]{5}[1-2]{1}[0-9]{3}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}[0-9Xx]$/;// 闰年出生日期的合法性正则表达式
            } else {
                ereg = /^[1-9][0-9]{5}[1-2]{1}[0-9]{3}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}[0-9Xx]$/;// 平年出生日期的合法性正则表达式
            }
            // 测试出生日期的合法性
            if (ereg.test(idcard)) {
                // 计算校验位
                S = (parseInt(idcard_array[0]) + parseInt(idcard_array[10])) * 7
                        + (parseInt(idcard_array[1]) + parseInt(idcard_array[11])) * 9
                        + (parseInt(idcard_array[2]) + parseInt(idcard_array[12])) * 10
                        + (parseInt(idcard_array[3]) + parseInt(idcard_array[13])) * 5
                        + (parseInt(idcard_array[4]) + parseInt(idcard_array[14])) * 8
                        + (parseInt(idcard_array[5]) + parseInt(idcard_array[15])) * 4
                        + (parseInt(idcard_array[6]) + parseInt(idcard_array[16])) * 2
                        + parseInt(idcard_array[7]) * 1
                        + parseInt(idcard_array[8]) * 6
                        + parseInt(idcard_array[9]) * 3;
                Y = S % 11;
                M = "F";

                JYM = "10X98765432";
                M = JYM.substr(Y, 1);// 判断校验位
                if (M.toLowerCase() == idcard_array[17].toLowerCase()) {
                    return true;// Errors[0]; //检测ID的校验位
                } else {
                    return false;// Errors[3];
                }
            } else {
                return false;// Errors[2];
            }
            break;
        default:
            return false;// Errors[1];
            break;
    }

}
/**
 * 获取字符串长度中文算两个字符
 * @param str
 * @returns {Boolean}
 */
function getStrAsciLength(str) {
    var re = /([^u4e00-u9fa5]|[^ufe30-uffa0])/;
    var strLen = str.length;
    var strAsicLen = 0;
    for (var i = 0; i < strLen; i++) {
        var ch = str.substr(i, 1);
        //alert(ch);
        if (!re.test(ch)) {
            strAsicLen += 1;
        } else {
            strAsicLen += 2;
        }
    }
    return strAsicLen;
}

//********************小插件类***************************

/* 
 * 模拟ajax提交
 */
$.fn.ajaxform = function (options) {
    var defaults = {
        'target': 'post-iframe',
        'iheight': 0,
        'iwidth': 0
    };

    var opts = $.extend({}, defaults, options),
            ele = $('<iframe>'),
            _this = $(this);

    function init() {

        if (_this.attr('action') == '') {
            _this.attr({'target': opts.target, 'action': location.href});
        } else {
            _this.attr({'target': opts.target});
        }

        createIframe();
    }

    function createIframe() {
        ele.attr({
            'frameborder': 0,
            'height': opts.iheight,
            'width': opts.iwidth,
            'scrolling': 'no',
            'name': opts.target
        });
        $('body').append(ele);
    }
    init();
};

/**
 * a 标签 的单选
 */
$.fn.aRadio = function (options) {
    var defaults = {
        btnele: 'a.btn',
        inputele: '',
        okicon: '<i class="icon-ok"></i> ',
        okclass: 'green',
        checkAfter: function (val) {
        }
    };
    var opts = $.extend({}, defaults, options),
            _this = $(this);

    opts.inputele = _this.children('input' + opts.inputele);
    var daval = $.trim(opts.inputele.val());

    //有值的进行初始化
    if (daval != '') {
        var obj = $(opts.btnele + '[data-val="' + daval + '"]', _this);
//        console.log(daval);
        toggle(obj);
    }
    //事件绑定
    $(opts.btnele, _this).on('click', function () {
        toggle($(this));
        return false;
    });
    //事件处理
    function toggle(obj) {
        var val = obj.attr('data-val'),
                hasiele = obj.children('i').length;
        if (hasiele) {
            return false;
        }
        obj.addClass(opts.okclass).siblings(opts.btnele).removeClass(opts.okclass);
        $(opts.okicon).prependTo(obj);
        obj.siblings(opts.btnele).children('i').remove();
        opts.inputele.val(val);
        opts.checkAfter(val);
    }
};

/**
 * a 标签 的复选
 */
$.fn.aCheckbox = function (options) {
    var defaults = {
        btnele: 'a.btn',
        inputele: '',
        okicon: '<i class="icon-ok"></i> ',
        okclass: 'green',
        max: 0,
        maxerr: '最多可先N个',
        checkAfter: function (val, obj) {
        }
    };
    var opts = $.extend({}, defaults, options),
            _this = $(this);
    opts.inputele = _this.children('input' + opts.inputele);
    var daval = _getVal();
    if (daval.length > 0) {
        var i = 0, len = daval.length;
        for (; i < len; i++) {
            var obj = $(opts.btnele + '[data-val="' + daval[i] + '"]', _this);
            toggle(obj, false);
        }
    }
    //事件绑定
    $(opts.btnele, _this).on('click', function () {
        toggle($(this), true);
        return false;
    });

    //事件处理
    function toggle(obj, isclick) {
        var val = obj.attr('data-val'),
                hasiele = obj.children('i').length;
//        if(!val || val==''){
//             opts.checkAfter(val,obj);
//            return false;
//        }
        if (hasiele) {
            obj.removeClass(opts.okclass).children('i').remove();
            _updateVal(val, '-');
            opts.checkAfter(val, obj);
            return false;
        }
        //添加之前进行max判断
        var daval = _getVal();
        if (opts.max > 0 && ((isclick && daval.length >= opts.max) || (!isclick && daval.length > opts.max))) {
            formValid.showErr($(opts.inputele), opts.maxerr);
            return false;
        }

        obj.addClass(opts.okclass);
        $(opts.okicon).prependTo(obj);
        _updateVal(val, '+');
        opts.checkAfter(val, obj);
    }

    //值的处理
    function _updateVal(v, act) {
        var inputval = _getVal(),
                v = $.trim(v);
        if (!v || v == '') {
            return;
        }
        if (act == '+' && !inputval.in_array(v)) {

            inputval.push(v);
        } else if (act == '-') {
            var i = 0, len = inputval.length;
            for (; i < len; i++) {
                if (inputval[i] == v) {
                    inputval.splice(i, 1);
                }
            }
        }

        if (inputval.length == 0) {
            opts.inputele.val('');
        } else {
            opts.inputele.val(inputval.join('|'));
        }

    }

    //获取值
    function _getVal() {
        var datas = opts.inputele.val();
        if (!datas) {
            datas = '';
        }
        return datas.length == 0 ? [] : datas.split('|');
    }

};
function deldata(obj){
    var _url = $(obj).attr('data-href');
    if(!_url){
        return false;
    }

    layerconfirm('删除后不可恢复，确定要删除吗？', 2, '操作确认', function () {
        location.href = _url;
    }, function () {
        closelayer();
    });
}
$(function () {


    $('.del_localfile').on('click',function(){
        var _this = $(this),
            type = _this.attr('data-type'),
            id = _this.attr('data-id');
            if(!type || !id){
                return false;
            }
        del_mcfile({"type":type,"id":id},function(d){
            _this.closest('.docli').remove();
            closelayer();
        });
    });

    function del_mcfile(data,after_act) {
        layerconfirm('删除后不可恢复，确定要删除吗？', 2, '操作确认', function () {
            $.post('/gerent/file/del_doc',data,function(d){
                if(d.err=='0'){
                    after_act && after_act(d);
                }else{
                    layeralert(d.msg,4,'操作提示');
                }
            },'json');
        }, function () {
            closelayer();
        });

    }

});

//go-top
var gotop = {
    tagele: '.go-top',
    init: function () {
        if ($(this.tagele).length <= 0) {
            return false;
        }

        $(window).on('scroll', function () {
            var ishide = true;
            if ($(this).scrollTop() > 200 && ishide) {
                $(gotop.tagele).show();
                ishide = false;
            } else {
                $(gotop.tagele).hide();
                ishide = true;
            }
            return false;
        });
    }
};

var fileview = {
    ele: '.jdfileinput',
    btn: '.jdselectimg',
    allowExts: '-image/png-image/jpg-image/jpeg',
    localKey: 'selectFile',

    init: function () {
        var self = this;
        $(this.btn).on('click',function(){
            console.log('btn click')
            $(this).next('input'+self.ele).click();
        });
        // var selected = JSON.parse(localStorage.getItem(this.localKey));
        // if(selected){
        //     $(this.ele).closest('.mcdoc_upload_box').children('.update').html('已选新图片：' + selected.name);
        // }

        $(this.ele).on('change', function(e){
            console.log(e);
            var file = e.target.files[0];

            if(!file){
                console.log('no file')
                return;
            }
            if(self.allowExts.indexOf(file.type) ==-1){
                layermsg('图片格式有误')
            }
            self.saveSelected({
                'name': file.name
            })
            var update = $(this).closest('.mcdoc_upload_box').children('.update');
            update.html('已选新图片：'+file.name)
        });
    },
    saveSelected:function(obj){
        // localStorage.setItem(this.localKey, JSON.stringify(obj))
    },
    clear: function () {
        // localStorage.removeItem(this.localKey)
    }
};

/**
 * 克隆对象
 * @param obj
 * @returns {{}}
 */
var cloneObj = function (obj) {
    var newObj = {};
    if (obj instanceof Array) {
        newObj = [];
    }
    for (var key in obj) {
        var val = obj[key];
        //newObj[key] = typeof val === 'object' ? arguments.callee(val) : val; //arguments.callee 在哪一个函数中运行，它就代表哪个函数, 一般用在匿名函数中。
        newObj[key] = typeof val === 'object' ? cloneObj(val): val;
    }
    return newObj;
};

// jquery特殊字符转义
var escapeJquery = function(srcString) {
    // 转义之后的结果
    var escapseResult = srcString;

    // javascript正则表达式中的特殊字符
    var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
        "]", "|", "{", "}"];

    // jquery中的特殊字符,不是正则表达式中的特殊字符
    var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
        ":", ";", "<", ">", ",", "/"];

    for (var i = 0; i < jsSpecialChars.length; i++) {
        escapseResult = escapseResult.replace(new RegExp("\\"
            + jsSpecialChars[i], "g"), "\\"
            + jsSpecialChars[i]);
    }

    for (var i = 0; i < jquerySpecialChars.length; i++) {
        escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
            "g"), "\\" + jquerySpecialChars[i]);
    }

    return escapseResult;
}