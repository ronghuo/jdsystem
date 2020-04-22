/**
 *
 * <div id="level">
 * <select class="lv1" data-value=""></select>
 * <select class="lv2" data-value=""></select>
 * <select class="lv3" data-value=""></select>
 * <select class="lv4" data-value=""></select>
 * </div>
 *
 *
 * $('#level').levelSelect({
 *    url:'/static/cate/areasubs.json'
 * });
 *
 */
(function($){
    $.fn.levelSelect=function(settings){
        if(this.length<1){return;};

        //
        settings=$.extend({
            url:"/static/cate/areas.json",
            lv1value:'',
            lv2value:'',
            lv3value:'',
            lv4value:'',
            nodata:true,
            nodatahtml:'<option value="">请选择</option>'
        },settings);

        var self = this,
            lv1obj = self.find('.lv1'),
            lv2obj = self.find('.lv2'),
            lv3obj = self.find('.lv3'),
            lv4obj = self.find('.lv4'),
            jsondata = null;

        var init = function(){
//                console.log(jsondata);
            initValues();
            renderLv1();
            if(settings.lv1value>0){
                renderLv2(settings.lv1value,settings.lv2value);
            }

            if(settings.lv2value>0){
                renderLv3(settings.lv1value,settings.lv2value,settings.lv3value);
            }

            if(settings.lv3value>0){
                renderLv4(settings.lv1value,settings.lv2value,settings.lv3value,settings.lv4value);
            }
            changeEvents();
        };

        var initValues = function(){
            if(!settings.lv1value){
                if(lv1obj.length>0){
                    settings.lv1value = lv1obj.attr('data-value');
                }

            }
            if(!settings.lv2value){
                if(lv2obj.length>0){
                    settings.lv2value = lv2obj.attr('data-value');
                }

            }
            if(!settings.lv3value){
                if(lv3obj.length>0){
                    settings.lv3value = lv3obj.attr('data-value');
                }

            }
            if(!settings.lv4value){
                if(lv4obj.length>0){
                    settings.lv4value = lv4obj.attr('data-value');
                }

            }
        };

        var changeEvents = function () {

            lv1obj.on('change',function(){
                settings.lv1value = $(this).val();
                clearValue(1);
                renderLv2(settings.lv1value,settings.lv2value);
            });

            lv2obj.on('change',function(){
                settings.lv2value = $(this).val();
                clearValue(2);
                renderLv3(settings.lv1value,settings.lv2value,settings.lv3value);
            });

            lv3obj.on('change',function(){
                settings.lv3value = $(this).val();
                clearValue(3);
                renderLv4(settings.lv1value,settings.lv2value,settings.lv3value,settings.lv4value);
            });

        };

        var clearValue = function(level){
            if(level<=3){
                settings.lv4value = '';
                if(lv4obj.length>0){
                    lv4obj.html('');
                }
            }
            if(level<=2){
                settings.lv3value = '';
                if(lv3obj.length>0){
                    lv3obj.html('');
                }
            }
            if(level<=1){
                settings.lv2value = '';
                if(lv2obj.length>0){
                    lv2obj.html('');
                }
            }
        };

        var renderLv1 = function(){
            settings.lv1value = renderOpteions(lv1obj,jsondata,settings.lv1value);
        };

        var renderLv2 = function(lv1val,lv2val){
            if(!lv1val){
                return false;
            }
               // console.log([
               //     lv1val,
               //     jsondata[lv1val],
               //     jsondata[lv1val]['c']
               // ]);
            renderOpteions(lv2obj,jsondata[lv1val]['c'],lv2val);
        };

        var renderLv3 = function(lv1val,lv2val,lv3val){
            if(!lv1val || !lv2val){
                return false;
            }
            try{
                //var data = jsondata[lv1val]['c'][lv2val]['c'];
                renderOpteions(lv3obj,jsondata[lv1val]['c'][lv2val]['c'],lv3val);
            }catch (e){
                console.log(e);
                return '';
            }

        };

        var renderLv4 = function(lv1val,lv2val,lv3val,lv4val){
            if(!lv1val || !lv2val || !lv3val){
                return false;
            }

            try{
                //var data = jsondata[lv1val]['c'][lv2val]['c'];
                renderOpteions(lv4obj,jsondata[lv1val]['c'][lv2val]['c'][lv3val]['c'],lv4val);
            }catch (e){
                console.log(e);
                return '';
            }
        };

        var renderOpteions = function(selectobj,data,selectedval){

            if(selectobj.length<=0 || !data){
                return '';
            }
            var tpl = '',firstvalue = '';
            if(settings.nodata){
                tpl += settings.nodatahtml;
            }
            for(k in data){
                if(!settings.nodata && !firstvalue){
                    firstvalue = k;
                }

                var selected = '';
                if(k==selectedval){
                    selected = 'selected';
                }
                tpl += '<option value="'+k+'" '+selected+'>'+data[k]['n']+'</option>';
            }

            selectobj.html(tpl);

            return selectedval? selectedval : firstvalue;
        };

        // 设置省市json数据
        if(typeof(settings.url)=="string"){

            $.get(settings.url,function(json){
                jsondata=json;
                init();
            },'json');
        }else{
            jsondata=settings.url;
            init();
        };

    }
})(jQuery);