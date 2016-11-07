jQuery(document).ready(function ($) {

    $('.filter-slidable').each(function (index, selector) {
        var $filter = $(selector);

        leftPos = $filter.attr('data-val-min') / $filter.attr('data-max') * $filter.width();
        $filter.find('.marker-container-max').width(((1 - (leftPos + $filter.find('.slider-marker-min').width()) / $filter.width()) * 100) + '%');
        $filter.find('.filter-bar').css('left', leftPos + $filter.find('slider-marker-min').width() / 2);
        $filter.find('.filter-bar').width($filter.width() - leftPos - parseInt($filter.find('.slider-marker-max').css('right')) - $filter.find('.slider-marker-min').width());
        $filter.find('.slider-marker-min').css('left', leftPos);

        rightPos = ($filter.attr('data-max') - $filter.attr('data-val-max')) / $filter.attr('data-max') * $filter.width();
        $filter.find('.slider-marker-max').css('right', rightPos);
        $filter.find('.marker-container-min').width(((1 - (rightPos + $filter.find('.slider-marker-max').width()) / $filter.width()) * 100) + '%');
        $filter.find('.filter-bar').width($filter.width() - parseInt($filter.find('.slider-marker-min').css('left')) - parseInt($filter.find('.slider-marker-max').css('right')) - $filter.find('.slider-marker-max').width());


        $filter.find('.slider-marker').draggable({
                containment: "parent",
                axis: "x",
                drag: function (event, ui) {
                    var $slidable = $(this).parent().parent();
                    if ($(this).hasClass('slider-marker-min')) {
                        $slidable.find('.marker-container-max').width(((1 - (ui.position.left + $(this).width()) / $slidable.width()) * 100) + '%');
                        var $filterBar = $slidable.find('.filter-bar');
                        $filterBar.css('left', ui.position.left + $(this).width() / 2);
                        $filterBar.width($slidable.width() - ui.position.left - parseInt($slidable.find('.slider-marker-max').css('right')) - $(this).width());

                        updateSliderValues($slidable);
                    }
                    if ($(this).hasClass('slider-marker-max')) {
                        leftPos = ui.position.left + $slidable.width() - $slidable.find('.marker-container-max').width();
                        rightPos = $slidable.width() - leftPos - $(this).width();
                        $(this).css('right', rightPos);
                        $slidable.find('.marker-container-min').width(((1 - (rightPos + $(this).width()) / $slidable.width()) * 100) + '%');
                        var $filterBar = $slidable.find('.filter-bar');
                        $filterBar.width($slidable.width() - parseInt($slidable.find('.slider-marker-min').css('left')) - parseInt($slidable.find('.slider-marker-max').css('right')) - $(this).width());

                        updateSliderValues($slidable);
                    }
                },
                stop: function (event, ui) {
                    var $slidable = $(this).parent().parent();
                    if ($(this).hasClass('slider-marker-max')) {
                        $(this).css('left', 'auto');
                    }

                    $newUri = updateQuery(window.location.href, $filter.attr('data-name'), getSliderMin($slidable) + $filter.attr('data-delimiter') + getSliderMax($slidable));
                    window.location.replace($newUri);
                }
            }
        );
    });

    (function (selector) {
        var $filter = $(selector);
        $filter.on('change', function () {
            window.location.replace($(this).find('option:selected').attr('url'));
        });
    })('.filter-dropdown');

    function getSliderMin($slidable) {
        return Math.round((parseInt($slidable.find('.slider-marker-min').css('left')) / $slidable.width()) * $slidable.attr('data-max'));
    }

    function getSliderMax($slidable) {
        return Math.round((1 - parseInt($slidable.find('.slider-marker-max').css('right')) / $slidable.width()) * $slidable.attr('data-max'));
    }

    function updateSliderValues($slidable) {
        var $minPrice = getSliderMin($slidable);
        var $maxPrice = getSliderMax($slidable);
        $slidable.find('.filter-values').html($minPrice + '&euro; - ' + $maxPrice + '&euro;');
    }

    function updateQuery(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        }
        else {
            return uri + separator + key + "=" + value;
        }
    }

});