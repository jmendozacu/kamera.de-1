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
 * Serialize Object.
 * -----------------
 */
(function ($) {
    $.fn.serializeObject = function () {

        var self = this,
            json = {},
            push_counters = {},
            patterns = {
                "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                "key": /[a-zA-Z0-9_]+|(?=\[\])/g,
                "push": /^$/,
                "fixed": /^\d+$/,
                "named": /^[a-zA-Z0-9_]+$/
            };

        this.build = function (base, key, value) {
            base[key] = value;
            return base;
        };

        this.push_counter = function (key) {
            if (push_counters[key] === undefined) {
                push_counters[key] = 0;
            }
            return push_counters[key]++;
        };

        $.each($(this).serializeArray(), function () {

            /** skip invalid keys */
            if (!patterns.validate.test(this.name)) {
                return;
            }

            var k,
                keys = this.name.match(patterns.key),
                merge = this.value,
                reverse_key = this.name;

            while ((k = keys.pop()) !== undefined) {

                /** adjust reverse_key */
                reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

                if (k.match(patterns.push)) {
                    merge = self.build([], self.push_counter(reverse_key), merge);
                } else if (k.match(patterns.fixed)) {
                    merge = self.build([], k, merge);
                } else if (k.match(patterns.named)) {
                    merge = self.build({}, k, merge);
                }
            }

            json = $.extend(true, json, merge);
        });

        return json;
    };
})(jQuery);


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
 * Monitor Jobs.
 * -------------
 */
function monitorJobs() {

    /** Get Form */
    var $monitor = jQuery('.jobs-monitor');
    var $form = $monitor.find('form');

    jQuery.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: $form.serialize(),
        dataType: 'json',
        beforeSend: function () {
        }
    }).done(function ($response) {

        if ($response.status) {

            /** Set Jobs List */
            $monitor.find('ul').replaceWith($response.html);

            /** Jobs Counter */
            $monitor.find('.badge').text(Object.keys($response.data).length);

            /** Custom Pagination Scrollbar */
            $form.find('ul').mCustomScrollbar({
                axis: "y",
                theme: "rounded-dark",
                scrollInertia: 100
            });

        } else {
            showMessage($response.notify.title, $response.notify.message, $response.notify.trace, "error");
        }

    }).fail(function (jqXHR) {
        var $trace = "url: " + $form.attr('action') + "<br>" + "response: " + jqXHR.statusText + " [" + jqXHR.status + "]";
        showMessage("XHR Error", "", $trace, "error");
    });
}


/**
 * Load Page by Number.
 * --------------------
 *
 * @param $page
 */
function loadPage($page) {

    /** Init Current State */
    var $form = jQuery('form.search-form');

    jQuery.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: $form.serialize() + '&page=' + $page + '&mode=next',
        dataType: 'json',
        beforeSend: function () {
        }
    }).done(function ($response) {

        if ($response.status) {
            jQuery('#results table tbody').append($response.html.list);
            jQuery('#results #pagination').replaceWith($response.html.pagination);
            updateProductTypes($response.types);
            showMessage('Notification', 'Next page was loaded. Scroll page down and check new products', false, "info");

            jQuery('html, body').animate({
                scrollTop: jQuery("tr.first-row").last().offset().top - 10
            }, 500);

            /** Custom Pagination Scrollbar */
            jQuery(".paginator").mCustomScrollbar({
                axis: "x",
                theme: "rounded-dark",
                scrollInertia: 100,
                advanced: {
                    autoExpandHorizontalScroll: true
                }
            });

        } else {
            showMessage($response.notify.title, $response.notify.message, $response.notify.trace, "error");
        }

    }).fail(function (jqXHR) {
        var $trace = "url: " + $form.attr('action') + "<br>" + "response: " + jqXHR.statusText + " [" + jqXHR.status + "]";
        showMessage("XHR Error", "", $trace, "error");
    });
}


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
        $row.find('a.add2jobs').show();

        var $count = jQuery('td.product-status.ready').size();
        jQuery('.add-ready-products .badge').text($count > 0 ? $count : '');

    } else {
        $row.find('td.product-status').removeClass('ready').addClass('pending');
        $row.find('a.add2jobs').hide();
    }
}


/**
 * Save Products To Jobs.
 * ---------------------
 */
function saveMagentoJobs($rows) {

    var $data = {};

    /** Check Input Param */
    $rows = jQuery.isArray($rows) ? $rows : [$rows];

    /** Collect Data */
    jQuery.each($rows, function ($index, $row) {

        /** Init Product Selectors */
        var $form = $row.find('.type-form');
        var $asin = $form.find('[name="asin"]').val();

        $data[$asin] = $form.serializeObject();
    });

    /** Build Request */
    var $form = jQuery('.job-form');
    $form.find('[name="json"]').val(JSON.stringify($data));

    jQuery.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: $form.serialize(),
        dataType: 'json',
        beforeSend: function () {

        }
    }).done(function ($response) {

        var $type = $response.data > 0 ? 'info' : 'warning';
        showMessage('Import: add product to jobs', '<strong>' + $response.data + '</strong> products was added to import queue', false, $type);

        monitorJobs();

        /** Remove Processed Items */
        jQuery.each($rows, function ($index, $row) {
            setTimeout(function () {
                $row.remove();
            }, 200 * $index);
        });

    }).fail(function (jqXHR) {
        var $trace = "url: " + $action + "<br>" + "response: " + jqXHR.statusText + " [" + jqXHR.status + "]";
        showMessage("XHR Error", "", $trace, "error");
    });

}


jQuery(document).ready(function () {

    /**
     * Jobs Monitor.
     * -------------
     */
    monitorJobs();

    /**
     * Refresh Jobs.
     * ------------
     */
    jQuery(document).on('click', '.refresh-jobs', function () {
        monitorJobs();
        return false;
    });


    /**
     * Nodes Tree.
     * -----------------------
     */
    var $tree = jQuery('#tree');
    var $treeForm = $tree.closest('form');

    $tree.jstree({
        'core': {
            'data': {
                'url': $treeForm.attr('action'),
                'data': function ($node) {
                    return {
                        'id': $node.id
                    };
                }
            },
            'themes': {
                'stripes': true
            }
        },
        'sort': function (a, b) {
            return this.get_type(a) === this.get_type(b) ? (this.get_text(a) > this.get_text(b) ? 1 : -1) : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
        },
        'plugins': ['state', 'dnd', 'sort', 'types']

    }).on('changed.jstree', function ($event, $data) {

        /** Node Selection */
        if ($data && $data.selected && $data.selected.length) {
            jQuery('[data-target="#treeModal"] span').text($data.node.text);
            jQuery('.search-form').find('[name="node"]').val($data.node.id);
        }
    });


    /**
     * Category Select Plugin.
     * -----------------------
     */
    jQuery('[name="category"]').selectpicker({
        style: 'btn-default',
        size: 10,
        liveSearch: true,
        width: '100%'
    });


    /**
     * Sort Select Plugin.
     * ------------------
     */
    jQuery('[name="sort"]').selectpicker({
        style: 'btn-default',
        size: 10,
        liveSearch: true,
        width: '58%'
    });


    /**
     * Price Ranger.
     * -------------
     */
    jQuery("#price-selector").ionRangeSlider({
        type: "double",
        grid: true,
        grid_num: 50,
        min: 1,
        max: 75000,
        from: 50,
        to: 20000,
        prefix: "€ "
    });


    /**
     * Checkbox Switchers.
     * -------------------
     */
    jQuery('[type="checkbox"]').bootstrapToggle();


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

                $form.find('[name="keywords"]').val($response.keywords);
                jQuery('#results').append($response.html.search);
                updateProductTypes($response.types);

                jQuery('.results-block').slideDown(function () {

                    jQuery('html, body').animate({
                        scrollTop: jQuery(".results-block").offset().top - 10
                    }, 500);

                    /** Custom Pagination Scrollbar */
                    jQuery(".paginator").mCustomScrollbar({
                        axis: "x",
                        theme: "rounded-dark",
                        scrollInertia: 100,
                        advanced: {
                            autoExpandHorizontalScroll: true
                        }
                    });
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
     * Search Products: Direct Page.
     * -----------------------------
     */
    jQuery(document).on('click', '.page', function () {

        var $button = jQuery(this);
        if (!$button.hasClass('.active')) {
            var $page = parseInt($button.text().trim());
            loadPage($page);
        }

        return false;
    });


    /**
     * Remove Job.
     * -----------------------------
     */
    jQuery(document).on('click', '.remove-job', function () {

        if (confirm('Are you sure to remove this job from queue?')) {

            var $button = jQuery(this);
            var $asin = $button.closest('li').find('.job-asin').text().trim();
            var $form = $button.closest('li').find('form');

            jQuery.ajax({
                url: $form.attr('action'),
                type: $form.attr('method'),
                data: $form.serialize() + '&asin=' + $asin,
                dataType: 'json',
                beforeSend: function () {
                    $button.attr('disabled', true);
                }
            }).done(function () {
                monitorJobs();
                $button.attr('disabled', false);
            }).fail(function (jqXHR) {
                var $trace = "url: " + $form.attr('action') + "<br>" + "response: " + jqXHR.statusText + " [" + jqXHR.status + "]";
                showMessage("XHR Error", "", $trace, "error");
            });
        }

        return false;
    });


    /**
     * Search Products: Next.
     * ----------------------
     */
    jQuery(document).on('click', '#load-next', function () {

        var $currentPage = parseInt(jQuery('#current-page').text().trim());
        loadPage(++$currentPage);

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
            showMessage('Success!', 'Products types relation was saved and apply to queue', false, "info");
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


    /**
     * Add Product to Job.
     * -------------------
     */
    jQuery(document).on('click', '.add2jobs', function () {

        var $link = jQuery(this);
        var $row = $link.closest('tr');

        saveMagentoJobs($row);
        return false;
    });


    /**
     * Add All Products to Job.
     * -------------------
     */
    jQuery(document).on('click', '.add-ready-products', function () {

        var $rows = [];

        /** Collect Ready Products */
        jQuery('td.product-status.ready').each(function () {
            var $td = jQuery(this);
            $rows.push($td.closest('tr'));
        });

        saveMagentoJobs($rows);
        return false;
    });

});



