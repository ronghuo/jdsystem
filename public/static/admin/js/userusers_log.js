/**
 * Created by ronghuo on 2020/2/28.
 */
$(function(){
    $('#btnClear').click(function () {
        $('form :input').attr('value', '');
        $('form')[0].submit();
    });
    $('a[id^=contentViewer]').click(function () {
        var index = this.id.split('_')[1];
        var content = $('#content_' + index).val();
        openContentLayer(content);
    });
});

var openContentLayer = function (content) {
    var contentLayer = $.layer({
        type: 1,
        title: '日志内容',
        area: ['400px', '320px'],
        offset: ['20%', '50%'],
        border: [6, 0.3, '#000', true],
        shade: [0],
        closeBtn: [0, true],
        page: {
            html: '<div style="width: 400px;height: 300px;text-align: center;padding-top: 20px;">' +
            '<div id="contentBox" style="height: 200px;text-align: left;padding-left: 5px;overflow: auto;"></div>' +
            '<div style="margin-top: 10px;">' +
            '<input type="button" id="layerCloser" class="btn btn-primary" style="width: 80px;margin-right: 10px;" value="确定" />' +
            '</div>' +
            '</div>'
        }
    });

    $('#contentBox').html(content);

    $('#layerCloser').click(function () {
        layer.close(contentLayer);
    });
};