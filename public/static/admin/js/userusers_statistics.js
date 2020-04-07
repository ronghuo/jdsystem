/**
 * Created by ronghuo on 2020/3/30.
 */
$(function(){

    $('#areas3').levelSelect({
        url:'/static/plugin/cate/levelareas.json?vv='+JD_VERSION
    });

    $('#btnExport').click(function () {
        $('form').attr('action', EXPORT_URL);
        $('form').submit();
    });

    $('#btnStatistics').click(function () {
        $('form').attr('action', STATISTICS_URL);
        $('form').submit();
    });

});