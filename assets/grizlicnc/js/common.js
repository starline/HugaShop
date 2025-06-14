/**
 * Custom javascript code
 * 
 */

export function getCartInformer(variant_id = null, amount = null, callback = null) {
    $.ajax({
        type: "POST",
        url: "/ajax/cart",
        data: {
            variant_id: variant_id,
            amount: amount,
            language: getUiLanguage()
        },
        dataType: 'html',
        success: function (data) {
            $('#cart_informer').html(data);
            if (callback) {
                callback();
            }
        }
    });
}


export function getUiLanguage() {
    return navigator.language || navigator.userLanguage || null;
}


// Form in Fancy
export function asignFancyAjax() {

    // Get Cart Popup
    $("#cart_informer a").fancybox({
        type: 'ajax',
        src: '/cart',
        touch: false,
        closeExisting: true,
        afterShow: asignFancyAjax,
    });

    // Ajax links
    $('.fancybox-inner a.ajax').on('click', function (e) {
        e.preventDefault();
        $.post($(this).attr('href'), function (response) {
            getCartInformer();
            $.fancybox.open({
                type: 'html',
                src: response,
                touch: false,
                closeExisting: true,
                afterShow: asignFancyAjax
            });
        });
    })

    // Ajax форм
    $('.fancybox-inner form').on('submit', function (e) {
        e.preventDefault();
        $(this).ajaxSubmit({
            dataType: "html",
            beforeSubmit: function (arr, form, options) {
                // show loader
            },
            success: function (response) {
                response = parseResponseByType(response);
                if (response.type == 'html') {
                    getCartInformer();
                    $.fancybox.open({
                        type: 'html',
                        src: response.data,
                        touch: false,
                        closeExisting: true,
                        afterShow: asignFancyAjax
                    });
                } else {
                    data = response.data;
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }
            },
            error: function (xmlRequest, textStatus, errorThrown) {
                alert(errorThrown);
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