/**
 * Created by chenxh on 2019/3/28.
 */
$(function(){

    fileview.init();

    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });

    $('#dmmcsbox').levelSelect({
        url:'/static/plugin/cate/dmmcs-43.json?vv='+JD_VERSION
    });


    $('#subbtn').on("click",function(){


        return true;
    });


});