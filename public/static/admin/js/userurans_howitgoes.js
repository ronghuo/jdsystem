/**
 * Created by chenxh on 2019/4/3.
 */
$(function(){
    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });
    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url + '?ref=' + encodeURIComponent(window.location.href), '设置社戒社康时间', 500, 300);
    });

    $('#btnClear').on('click', function () {
        var protocal = window.location.protocol;
        var host = window.location.host;
        var pathname = window.location.pathname;
        window.location.href = protocal + '//' + host + pathname;
    });

});