$(function() {

    fileview.clear();
    //添加/编辑部门
    $('a.openlayerwin').on('click', function () {
        var url = $(this).attr('data-url');
        if (!url) {
            return false;
        }
        layeriframe(url, '轮播编辑', 400, 450);
    });
});