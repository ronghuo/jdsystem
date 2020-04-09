$(function () {
    $('#areas4').levelSelect({
        url:'/static/plugin/cate/dmmcs-43.json?vv='+JD_VERSION,
        lv1value: $('#lv1').attr('data-value')
    });

    $('.lv2togglebtn').on('click', function () {
        var self = $(this),
            _id = self.attr('data-id'),
            _open = self.attr('data-open')
            ;

        if(!_id){
            return false;
        }

        if(_open == '1'){
            // $(`.lv2-${_id}`).slideUp();
            self.attr('data-open', '0')
            self.children('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');

        }else{
            // $(`.lv2-${_id}`).slideDown();
            self.attr('data-open', '1')
            self.children('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
        }
        $('.lv2-' + _id).toggle();
    })


    $('a.openlayerwin').on('click',function(){
        var url = $(this).attr('data-url');
        if(!url){
            return false;
        }
        layeriframe(url, '编辑机构信息', 500, 450);
    });
})