/**
 * Created by xiaohui on 2015/7/17.
 */

$(function () {

    //表单检查
    $('#subform').on('click', function () {
       var name = $('input[name="NAME"]');
        if(!checkInputEmpty(name)){
            formValid.showErr(name,'请填写角色名称');
            return false;
        }else{
            formValid.showSuccess(name);
        }
        //return false;
        $('#roleform').submit();
    });
    //节点选择
    accchek.init();
});

//权限选择与取消
var accchek = {
    abtn: '.accchk',
    init: function () {
        this._bindEvent();
    },
    _bindEvent: function () {
        $(this.abtn).on('change', function () {

            var chkd = this.checked, _id = $(this).attr('id'), slibid = [],len=0;
            //acc12-13-46
            if (_id.indexOf('-') > -1) {
                var ids = _id.split('-');
                len = ids.length;
                slibid.push(ids[0]);
                if(len>2){
                    slibid.push(ids[0]+'-'+ids[1]);
                }

            }
            if(len>0){
                for(var i=0;i<len;i++){
                    if(chkd){
                        accchek._setChecked($('#'+slibid[i]), true);
                    }else{

                        if($("input[id^='" + slibid[i] + "-']").length<=1){
                            accchek._setChecked($('#'+slibid[i]), false);
                        }
                    }

                }
            }
            var chkes = $("input[id^='" + _id + "']");
            //选中
            if (chkd) {
                accchek._setChecked(chkes, true);
            }
            //取消选中
            else {
                accchek._setChecked(chkes, false);
            }

        });
    },
    _setChecked: function (obj, chked) {
        if (obj.length <= 0) {
            return;
        }
        obj.each(function (i) {
            this.checked = chked;
            /*if (chked) {
                $(this).closest('span').addClass('checked');
            } else {
                $(this).closest('span').removeClass('checked');
            }*/
        });
    }
};
