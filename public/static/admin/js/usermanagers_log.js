/**
 * Created by ronghuo on 2020/2/27.
 */
$(function(){
    $('#btnClear').click(function () {
        $('form :input').attr('value', '');
        $('form')[0].submit();
    })
});