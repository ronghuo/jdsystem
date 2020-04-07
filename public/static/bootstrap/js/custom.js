/*================================
	SCROLL TOP
=================================*/
$(function () {
    $(".scroll-top").hide();
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.scroll-top').fadeIn();
        } else {
            $('.scroll-top').fadeOut();
        }
    });

    $('.scroll-top a').click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 500);
        return false;
    });
});


/*================================
	LEFT BAR TAB
=================================*/

$(function () {

    /*$('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });*/
	$('#myTab1 a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
	$('#myTab2 a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
	$('#chat-tab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    /*$('.left-primary-nav li a').tooltip({
        placement: 'right'
    });*/
	$('.row-action .btn').tooltip({
        placement: 'top'
    });

	$('#mc-left-menu a.lv1').on('click',function(){
	    var _this = $(this),
            _thisi = _this.children('i');
	    if(_thisi.hasClass('icon-chevron-down')){
            _thisi.removeClass('icon-chevron-down').addClass('icon-chevron-up');
        }else{
            _thisi.removeClass('icon-chevron-up').addClass('icon-chevron-down');
        }
	    _this.next('ul.accordion-nav').toggle();
    });
});


/*================================
	TOP TOOLBAR TOOL TIP
=================================*/

$(function () {

    $('.top-right-toolbar a').tooltip({
        placement: "top"
    });


});


/*================================
	SYNTAX HIGHLIGHTER
=================================*/
$(function () {
// make code pretty
window.prettyPrint && prettyPrint()
    $('.uploadimgbox').on('mouseover',function(){
        $(this).find('.uptoolbar').css({'bottom':0});
    });
    $('.uploadimgbox').on('mouseout',function(){
        $(this).find('.uptoolbar').css({'bottom':-30+'px'});
    });
});


/*================================
RESPONSIVE NAV $ THEME SELECTOR
=================================*/
$(function() {
		  
			  $('.responsive-leftbar').click(function()
			{
				$('.leftbar').toggleClass('leftbar-close expand',500, 'easeOutExpo');
				});

			}); 
	$(function() {
		  
			  $('.theme-setting').click(function()
			{
				$('.theme-slector').toggleClass('theme-slector-close theme-slector-open',500, 'easeOutExpo');
				});

			}); 



		$(function()
		{
			$('.theme-color').click(function()
			{
				var stylesheet = $(this).attr('title').toLowerCase();
				$('#themes').attr('href','css'+'/'+stylesheet+'.css');
				});
			});
			
			$(function(){
				$('.theme-default').click(function(){
				$('#themes').removeAttr("href");
			});
	});
