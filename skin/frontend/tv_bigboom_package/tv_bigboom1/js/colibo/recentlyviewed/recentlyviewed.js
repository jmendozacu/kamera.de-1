(function ($) {
    $(document).on('ready', function () {
        $('.recently-viewed-clapper').on('click', function () {
            $('.recently-viewed-collapsible').toggleClass("collapsed").find('ul').slideToggle("fast", function () {
            });
        });
    });
})
(jQuery);