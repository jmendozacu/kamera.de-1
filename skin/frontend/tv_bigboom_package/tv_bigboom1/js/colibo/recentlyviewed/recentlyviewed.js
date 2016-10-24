(function ($) {
    $( document ).on('ready',function() {

        $('.recently-viewed-clapper ul').hide();

        $('.recently-viewed-clapper').on('click', function () {
            $('.recently-viewed-collapsible').toggleClass("collapsed");
            $( ".recently-viewed-collapsible ul" ).slideToggle( "fast", function() {});
        });

    });
})
(jQuery);