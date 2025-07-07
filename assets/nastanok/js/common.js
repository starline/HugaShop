/**
 * Template nastanok
 * Common.js
 * Custom javascript code
 * 
 * @author Andi Huga
 * 
 */

$(function () {

    //  Автозаполнитель поиска
    $(".input_search").autocomplete({
        serviceUrl: '/ajax/product/search',
        minChars: 1,
        noCache: false,
        width: "480px", // Ширина списка
        zIndex: 9999, // z-index списка
        deferRequestBy: 400, // Задержка запроса (мсек), на случай, если мы не хотим слать миллион запросов, пока пользователь печатает. Я обычно ставлю 300.
        onSelect: function (suggestion) {
            if (typeof suggestion.data !== 'undefined') {
                location.href = "/product/" + suggestion.data.id;
            } else {
                return false;
            }
        },
        formatResult: function (suggestion, currentValue) {
            var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
            var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
            if (suggestion.data) {
                return '<div>' + (suggestion.data.image ? "<img align='absmiddle' src='" + suggestion.data.image + "'> " : '') + '<span class="product-name">' + suggestion.data.name.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + '</span></div>';
            } else {
                return '<div>' + suggestion.value + '</div>';
            }
        }
    });

    $('.search_button').on('click', function () {
        let keyword = $(".input_search").val();
        location.href = "/s/" + keyword;
    });

    $('form#search').on('submit', function (e) {
        e.preventDefault();
        let keyword = $(".input_search").val();
        location.href = "/s/" + keyword;
    });


    // Зум картинок
    $("a.zoom").fancybox({
        buttons: [
            'close'
        ],
        image: {
            preload: true
        },
        closeExisting: true,
        defaultType: "image"
    });


    asignFancyAjax();

    // Аяксовая корзина при нажатии на Купить
    $('form.variants').on('submit', function (e) {
        e.preventDefault();

        let button = $(this).find('button[type="submit"]');
        let amount = 1;
        let variant = null;

        if ($(this).find('input[name=amount]').val())
            amount = $(this).find('input[name=amount]').val();

        // Для страницы товара
        if ($(this).find('input[name=variant][type="radio"]').length > 0) {
            variant = $(this).find('input[name=variant]:checked').val();
            amount = $(this).find('input[name=amount]').val();
        }

        // Для товаров в каталоге
        else if ($(this).find('input[name=variant]').length > 0)
            variant = $(this).find('input[name=variant]').val();

        // Для выпадающего списка вариантов
        else if ($(this).find('select[name=variant]').length > 0)
            variant = $(this).find('select').val();

        getCartInformer(variant, amount, function () {

            if (button.attr('data-result-text'))
                button.html(button.attr('data-result-text'));

            // Popup
            $.fancybox.open({
                type: 'ajax',
                src: '/cart',
                touch: false,
                closeExisting: true,
                afterShow: asignFancyAjax
            });
        });
        return false;
    });


    function getCartInformer(product_id = null, amount = null, callback = null) {
        const cart_url = $("#cart_informer a").attr('href') + '?informer';
        $.ajax({
            url: cart_url,
            data: {
                product_id: product_id,
                amount: amount
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


    // Form in Fancy
    function asignFancyAjax() {

        // Корзина информер Popup
        $("#cart_informer a").fancybox({
            type: 'ajax',
            src: '/cart',
            touch: false,
            closeExisting: true,
            afterShow: asignFancyAjax,
        });

        // Ajax ссылки
        $('.fancybox-inner a.ajax').click(function (e) {
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
        $('.fancybox-inner form').submit(function (e) {
            e.preventDefault();
            $(this).ajaxSubmit({
                dataType: "html",
                beforeSubmit: function (arr, form, options) {
                    // показать лоадер
                },
                success: function (response) {
                    response = $.parseResponseByType(response);
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


    /*** Parse response ***/
    $.parseResponseByType = function (response) {
        let result = {};
        if (response.indexOf('{') == 0) { 	//it is JSON response
            result['data'] = $.parseJSON(response);
            result['type'] = 'json';
        } else {							//it is HTML response
            result['data'] = response;
            result['type'] = 'html';
        }
        return result;
    }
});


$(document).ready(function () {
    $(".owl-carousel").owlCarousel({
        loop: true,
        margin: 15,
        nav: true,
        dots: false,
        responsive: {
            0: {
                items: 2
            },
            760: {
                items: 3
            },
            1000: {
                items: 4
            }
        }
    });
});