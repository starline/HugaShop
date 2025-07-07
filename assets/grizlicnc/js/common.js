/**
 * Custom javascript code
 * 
 * @author Andri Huga
 * @version 1.6
 * 
 */

export function getCartInformer(product_id = null, amount = null, callback = null) {
    const cart_url = $("#cart_informer a").attr('href') + '?informer';
    $.ajax({
        type: "POST",
        url: cart_url,
        data: {
            product_id: product_id,
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
    const cart = $("#cart_informer a");
    const cart_link = cart.attr('href');
    cart.fancybox({
        type: 'ajax',
        src: cart_link,
        touch: false,
        closeExisting: true,
        afterShow: asignFancyAjax,
    });


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