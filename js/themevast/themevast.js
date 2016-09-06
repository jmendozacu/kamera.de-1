
jQuery(document).ready(function($){
	var specialOffer = jQuery('#header-special-offer');
	specialOffer.find('.header-special-offer-close').click(function() {
		specialOffer.slideUp('slow');
	});
	jQuery("#back-top").hide();
	// fade in #back-top
	jQuery(function () {
		jQuery(window).scroll(function () {
			if (jQuery(this).scrollTop() > 100) {
				jQuery('#back-top').fadeIn();
			} else {
				jQuery('#back-top').fadeOut();
			}
		});
		// scroll body to 0px on click
		jQuery('#back-top').click(function () {
			jQuery('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	});

});

jQuery(document).ready(function($) {
	$('.block-social-right .social-icon').mouseenter(function () {
	    $(this).stop();
	    $(this).animate({width: '160'}, 500, 'swing', function () {
	    });
	});
	$('.block-social-right .social-icon').mouseleave(function () {
	    $(this).stop();
	    $(this).animate({width: '43'}, 500, 'swing', function () {
	    });
	});        
}); 

