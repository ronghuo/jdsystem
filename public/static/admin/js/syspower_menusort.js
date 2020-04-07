/**
 * Created by xiaohui on 2015/8/1.
 */
$(function () {
    var url = location.href;
    ddsort.initsort(url);

});
//排序处理
var ddsort = {

    'pdata':{},
    'purl':'',

    getSubMenu:function(obj){
        var me = $(obj),
            _id = me.attr('data-id'),
            olele = me.closest('.dd-list'),
            tarele = olele.attr('data-target'),
            isg = olele.attr('data-gp');
        if(tarele==''){
            return false;
        }
        ddsort.pdata = {
            'pid':_id,'isg':isg,'act':'getsub','ajax':1
        };
        ddsort._post(1,tarele);
    },
    initsort:function(url){
        this.purl = url;
        $('.srotbox').dragsort({
            //容器拖动手柄
            dragSelector: "div.dd-handle",
            //执行之后的回调函数
            dragEnd:ddsort._saveSort,
            //指定不会执行动作的元素
            dragSelectorExclude : "a"
        });

    },
    _post:function(type,ele){
        if(!this.pdata || isEmptyObj(this.pdata)){
            return false;
        }
        $.post(this.purl,this.pdata,function(d){
            if(d.err=='1'){
                layeralert(d.mesg,0,'操作提示');
                return false;
            }else{
                if(type==1){
                    ddsort._putoutli(d.data,ele);
                }else if(type==2){
                    layermsgpos('保存成功',2,1);
                }
            }

            return false;
        },'json');
    },

    _putoutli:function(d,ele){
        var len = d.length,i= 0,str='';
        for(;i<len;i++){
            str += this._litpl(d[i]);
        }
        $(ele).html(str);
    },
    _litpl:function(d){
        return '<li class="dd-item" data-id="'+ d['ID']+'">'
            +'<div class="dd-handle">'
            +'<a href="javascript:;" onclick="ddsort.getSubMenu(this);" data-id="'+ d['ID']+'"><i class="'+ d['ICON']+'"></i> '+d['NAME']+'</a>'
            +'</div></li>';
    },

    _saveSort:function(event){
        event = event ? event : window.event;
        var olele = $(event.target).closest('ol'),
            isg = olele.attr('data-gp'),
            ids = [];
        olele.children('li').each(function(i){
            ids.push($(this).attr('data-id'));
        });
        ddsort.pdata = {
            'ids':ids,'isg':isg,'act':'savesort','ajax':1
        };
        ddsort._post(2,'');
    }

};