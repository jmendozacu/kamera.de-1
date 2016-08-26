
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


jQuery(document).ready(function () {

    /** Globals */
    var $asinQueue = jQuery('#asins');


    /** Add ASIN to Queue */
    jQuery('form.direct-asin').on('submit', function () {

        var $form = jQuery(this);
        var $asinNode = $form.find('input[type="text"]');
        var $asin = $asinNode.val().trim();

        var $optionExists = $asinQueue.find('option[value="' + $asin + '"]');

        if ($asin.length > 3 && !$optionExists.length) {
            var $option = jQuery('<option></option>');
            $option.val($asin).text($asin).prependTo($asinQueue);
        }

        $asinNode.val('').focus();
        $asinQueue.val($asin);

        /** Scroll Select to 1st Option */
        setTimeout(function () {
            $asinQueue.scrollTop(0);
        }, 0);

        return false;
    });


    /** Import Products */
    jQuery('form.import-form').on('submit', function () {

        var $form = jQuery(this);
        $asinQueue.find('option').prop('selected', true);

        jQuery.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            dataType: 'json',
            beforeSend: function () {

            }
        }).done(function ($response) {

            if ($response.status) {
                showMessage('Success', 'Products was imported.', false, 'success');
                $asinQueue.find('option').remove();
            } else {
                showMessage($response.notify.title, $response.notify.message, $response.notify.trace, "error");
            }

        }).always(function () {

        }).fail(function (jqXHR) {
            var $trace = "url: " + $form.attr('action') + "<br>" + "response: " + jqXHR.statusText + " [" + jqXHR.status + "]";
            showMessage("XHR Error", "", $trace, "error");
        });

        return false;
    });

    /** ASINs item count observer */
    $asinQueue.bind("DOMSubtreeModified", function () {
        jQuery('.asins-counter').empty().text($asinQueue.find('option').length);
    });


    /** Remove ASIN from Queue */
    $asinQueue.on("dblclick", 'option', function () {

        jQuery(this).remove();

        /** Scroll Select to 1st Option */
        setTimeout(function () {
            $asinQueue.scrollTop(0);
        }, 0);
    });

});



