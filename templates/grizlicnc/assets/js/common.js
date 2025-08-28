/**
 * Custom javascript code
 * 
 * @author Andri Huga
 * @version 2.0
 * 
 */

// Get Cart informer
export function getCartInformer(product_id = null, amount = null, callback = null) {

    const cart = "#cart_informer";
    const cart_a = "#cart_informer a";
    const cart_url = $(cart_a).attr('href');

    if (!document.querySelector(cart)) return;

    $.ajax({
        type: "POST",
        url: cart_url + '?informer',
        data: {
            product_id: product_id,
            amount: amount,
            language: getUiLanguage()
        },
        dataType: 'html',
        success: function (data) {
            $(cart).html(data);

            // Assign Cart Popup
            $(cart_a).fancybox({
                type: 'ajax',
                src: cart_url,
                touch: false,
                closeExisting: true,
                afterShow: asignFancyAjax,
            });

            assignTooltip(cart);

            if (callback) {
                callback();
            }
        }
    });
}


// Tooltips
export function assignTooltip(selector) {
    const container = selector ? document.querySelector(selector) : document;
    if (!container) return;

    const tooltipTriggerList = container.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
}


// Get UI language. Browser
export function getUiLanguage() {
    return navigator.language || navigator.userLanguage || null;
}


// Form in Fancy
export function asignFancyAjax() {

    // Ajax links
    $('.fancybox-inner a.ajax').on('click', function (e) {
        e.preventDefault();
        $.fancybox.open({
            type: 'ajax',
            src: $(this).attr('href'),
            touch: false,
            closeExisting: true,
            afterShow: asignFancyAjax
        });
    });

    // Ajax форм
    $('.fancybox-inner form').on('submit', function (e) {
        e.preventDefault();

        $(this).ajaxSubmit({
            dataType: "html",
            beforeSubmit: function (formData, form, options) {
                $.fancybox.getInstance()?.showLoading();
            },
            success: function (response, statusText, xhr, $form) {

                // Приоритет: редирект через заголовок
                let redirectUrl = xhr.getResponseHeader('X-Redirect');
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                    return;
                }

                if ($form.is('#cart_form')) {
                    getCartInformer();
                }

                response = parseResponseByType(response);
                if (response.type == 'html') {
                    $.fancybox.open({
                        type: 'html',
                        src: response.data,
                        touch: false,
                        closeExisting: true,
                        afterShow: asignFancyAjax
                    });
                } else {
                    data = response.data || {};
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            },
            error: function (xmlRequest, textStatus, errorThrown) {
                console.error(errorThrown || 'Request failed');
            }
        });
    });
}


// Parse response
function parseResponseByType(response) {
    let result = {};
    if (response.indexOf('{') == 0) { 	// it is JSON response
        result['data'] = JSON.parse(response);
        result['type'] = 'json';
    } else {							// it is HTML response
        result['data'] = response;
        result['type'] = 'html';
    }
    return result;
}


export function loaderLayer(element) {
    $(element).prepend('<div class="loader_layer"></div>');
    $(element).css('position', 'relative');
}


document.onvisibilitychange = () => {
    if (document.visibilityState === "hidden") {
        $(document).trigger('documentHiddenEvent');
    } else {
        $(document).trigger('documentVisibleEvent');
    }
};


/**
 * Init Owl Carusel
 */
export function owlCarouselInit(target) {
    const $carousel = $(target).find('.owl-carousel');
    if ($carousel.length) {
        $carousel.owlCarousel({
            loop: true,
            margin: 0,
            nav: true,
            dots: false,
            responsive: {
                0: { items: 2 },
                760: { items: 3 },
                1000: { items: 4 }
            }
        });
    }
}


// Action btn
export function assignButton(selector) {
    $(selector).on('click', function (e) {

        // Если уже в процессе — не даём нажимать повторно
        if ($(this).hasClass('disabled')) {
            e.preventDefault();
            (this).prop('disabled', true);
            return;
        }

        $(this).addClass('disabled');
    });
}

export function allButtonOn(selector) {
    $(selector).removeClass('disabled').prop('disabled', false);
}