/**
 * Global Notify Options
 * ---------------------
 */
var $notifyOptions = {
    multiline: true,
    autohide: true,
    timeout: 5000,
    clickable: true,
    offset: 17
};


/**
 * Xhr Loading Handler
 * -------------------
 */
jQuery(document)
    .bind("ajaxStart.mine", function () {
        jQuery("#loader").show();
    })
    .bind("ajaxStop.mine", function () {
        jQuery("#loader").hide();
    });


/**
 * Show Notification
 * -----------------
 * @param $title
 * @param $message
 * @param $trace
 * @param $type
 */
function showMessage($title, $message, $trace, $type) {

    var $content = '';

    $title = $title || "";
    $trace = $trace || "";
    $message = $message || "";

    if ($title.length) {
        $content += "<strong>" + $title + "</strong>";
    }

    if ($message.length) {
        $content += "<span>" + $message + "</span>";
    }

    if ($trace.length) {
        $content += "<pre>" + $trace + "</pre>";
    }

    notif(jQuery.extend($notifyOptions, {
        position: "right",
        msg: $content,
        type: $type
    }));
}


/**
 * Update Product Type.
 * --------------------
 */
function updateProductTypes($data) {

    var $results = jQuery('#results');
    jQuery($results.find('table tbody tr')).each(function () {

        /** Init Product Selectors */
        var $row = jQuery(this);
        var $form = $row.find('.type-form');
        var $amazonProductType = $form.find('[name="amazon_product_type"]');
        var $attributeSet = $form.find('[name="attribute_set_id"]');
        var $category = $form.find('[name="category_id"]');

        /** Loop DB mapping */
        jQuery($data).each(function ($index, $item) {
            if ($amazonProductType.val() == $item.amazon_product_type) {
                $attributeSet.val($item.attribute_set_id);
                $category.val($item.category_id);
            }
        });

        checkProductReadiness($row);
    });
}


/**
 * Update Product Type.
 * --------------------
 */
function checkProductReadiness($row) {

    /** Init Product Selectors */
    var $form = $row.find('.type-form');
    var $attributeSet = $form.find('[name="attribute_set_id"]');
    var $category = $form.find('[name="category_id"]');

    if ($attributeSet.val() != '' && $category.val() != '') {
        $row.find('td.product-status').removeClass('pending').addClass('ready');
    } else {
        $row.find('td.product-status').removeClass('ready').addClass('pending');
    }
}


jQuery(document).ready(function () {

    /**
     * Search Products.
     * ----------------
     */
    jQuery('form.search-form').on('submit', function () {

        var $form = jQuery(this);

        jQuery.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            dataType: 'json',
            beforeSend: function () {
                jQuery('.results-block').hide();
                jQuery('#results').empty();
            }
        }).done(function ($response) {

            if ($response.status) {

                jQuery('#results').append($response.html);
                updateProductTypes($response.types);

                jQuery('.results-block').slideDown(function () {
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".results-block").offset().top - 10
                    }, 500);
                });

            } else {
                showMessage($response.notify.title, $response.notify.message, $response.notify.trace, "error");
            }

        }).fail(function (jqXHR) {
            var $trace = "url: " + $form.attr('action') + "<br>" + "response: " + jqXHR.statusText + " [" + jqXHR.status + "]";
            showMessage("XHR Error", "", $trace, "error");
        });

        return false;
    });


    /**
     * Products Counter Observer.
     * --------------------------
     * */
    jQuery('#results').bind("DOMSubtreeModified", function () {
        jQuery('.asins-counter.success').empty().text(jQuery('#results').find('table .label-success').length);
        jQuery('.asins-counter.failed').empty().text(jQuery('#results').find('table .label-danger').length);
    });


    /**
     * Save Product Types Relations.
     * ----------------------------
     */
    jQuery(document).on('click', 'button.accept-type', function () {

        var $button = jQuery(this);
        var $form = $button.closest('form');

        jQuery.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            dataType: 'json',
            beforeSend: function () {

            }
        }).done(function ($response) {
            updateProductTypes($response.types);
        }).fail(function (jqXHR) {
            var $trace = "url: " + $form.attr('action') + "<br>" + "response: " + jqXHR.statusText + " [" + jqXHR.status + "]";
            showMessage("XHR Error", "", $trace, "error");
        });

        return false;
    });


    /**
     * Types Select Trigger.
     * ---------------------
     */
    jQuery(document).on('change', 'select[name="attribute_set_id"], select[name="category_id"]', function () {

        var $select = jQuery(this);
        var $row = $select.closest('tr');

        checkProductReadiness($row);
        return false;
    });


    /**
     * Save Product Types Relations.
     * ----------------------------
     */
    jQuery(document).on('click', 'a.remove', function () {

        var $link = jQuery(this);
        var $row = $link.closest('tr');

        if (confirm('Are you sure to remove this product from import queue?')) {
            $row.slideUp(function () {
                $row.remove();
            });
        }
        return false;
    });


    /**
     * Remove All Fails.
     * -----------------
     */
    jQuery(document).on('click', '.remove-all', function () {

        if (confirm('Are you sure to remove all failed products from import queue?')) {

            var $results = jQuery('#results');
            jQuery($results.find('table tbody tr')).each(function () {

                var $row = jQuery(this);
                if ($row.find('pre').length) {
                    $row.slideUp(function () {
                        $row.remove();
                    });
                }
            });
        }
        return false;
    });


    /**
     * Set Category|AttributeSet for All Products.
     * -------------------------------------------
     */
    jQuery(document).on('change', 'select.global', function () {

        var $select = jQuery(this);
        jQuery('select[name="' + $select.attr('name') + '"]').val($select.val());

        var $results = jQuery('#results');
        jQuery($results.find('table tbody tr')).each(function () {

            /** Init Product Selectors */
            var $row = jQuery(this);
            checkProductReadiness($row);
        });

        return false;
    });


    /**
     * Fixed Header.
     * -------------
     */
    jQuery(window).scroll(function () {

        var $header = jQuery('.results-block .panel-heading'),
            $position = jQuery(window).scrollTop();

        if ($position >= ($header.scrollTop() + 400)) {
            $header.addClass('fixed');
        } else {
            $header.removeClass('fixed');
        }
    });

});



