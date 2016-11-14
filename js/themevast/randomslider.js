jQuery(document).ready(function ($) {
    (function (selector) {
        var $content = $(selector);
        var $slider = $('.products-grid', $content);
        var slider = $slider.bxSlider({
            auto: 0, speed: 300, controls: 1, pager: 0, maxSlides: 2, slideWidth: 280, infiniteLoop: false,
            moveSlides: 2,
            slideMargin: 10,
            autoHover: true, // stop while hover <=> slider.stopAuto(); + slider.startAuto();
        })

    })(".randomslider");
});