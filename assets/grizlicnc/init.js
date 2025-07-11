/**
 * Template grizlicnc
 * Custom javascript code
 * 
 * @author Andri Huga
 * @version 1.7
 * 
 */

import './js/fancybox/jquery.fancybox.min.css';
import './js/jquery/jquery-ui.css';
import './js/owlcarousel/owl.carousel.css';
import './css/common.css';

import './js/jquery/jquery.js';
import './js/jquery/jquery-ui.js';
import './js/fancybox/jquery.fancybox.min.js';
import './js/autocomplete/jquery.autocomplete-min.js';
import './js/jquery/jquery.form.js';
import './js/owlcarousel/owl.carousel.min.js';
import './js/htmx.min.js';
import './js/bootstrap.bundle.min.js';
import { getCartInformer, asignFancyAjax, loaderLayer, owlCarouselInit } from './js/common.js';

$(function () {

    // Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));


    // Fancy
    asignFancyAjax();

    owlCarouselInit($('#related_products'));

    //  Автозаполнитель поиска
    $("#search input").autocomplete({
        serviceUrl: '/ajax/product/search',
        minChars: 1,
        noCache: false,
        width: "auto", // List width
        zIndex: 9999, // z-index списка
        deferRequestBy: 400, // Query delay (msc)
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

    $('form#search').on('submit', function (e) {
        e.preventDefault();
        let keyword = $("#search input").val();
        keyword = keyword.replaceAll("+", "%2B");
        keyword = keyword.replaceAll(" ", "+");
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


    // Select Product
    $('.product_item a').on('click', function () {
        const product_item = $(this).closest('.product_item');
        const attr_array = ['product_id', 'sku', 'product_name', 'variant_name', 'price'];
        let item = { amount: 1 }

        if (product_item.find('input[name=product]').length > 0) {
            attr_array.forEach(attr => {
                item[attr] = product_item.find('form.variants').attr(attr) || null;
            });
        }

        // Get list data
        item.list_id = product_item.closest('.product_list').attr('list_id') || null;
        item.list_name = product_item.closest('.product_list').attr('list_name') || null;

        $(document).trigger('selectItemEvent', item);
    });


    // Select amount
    $('.product_amount .minus').on('click', function () {
        let qty = $('.product_amount input').val();
        qty = parseInt(qty);
        qty = isNaN(qty) ? 1 : qty - 1;
        qty = qty < 1 ? 1 : qty;
        $('.product_amount input').val(qty);
        return false;
    });

    $('.product_amount .plus').on('click', function () {
        let qty = $('.product_amount input').val();
        qty = parseInt(qty);
        qty = isNaN(qty) ? 1 : qty + 1;

        let max_stock = $('.variants input[name=product]:checked').attr('max_stock') || null;
        qty = (max_stock != null && qty > max_stock) ? max_stock : qty;

        $('.product_amount input').val(qty);
        return false;
    });


    // Select product
    $('.variants input[name=product]').on('change', function () {

        loaderLayer('.variants');

        const attr_array = ['product_id', 'price', 'old_price'];
        let product = {};
        attr_array.forEach(attr => {
            product[attr] = $(this).attr(attr) || null;
        });

        window.location.href = '/p/' + product['product_id'];
    });


    // Ajax Cart when click Buy
    $('form.variants').on('submit', function (e) {
        e.preventDefault(); // Cancel the submit

        const product_item = $(this).closest('.product_item');
        const attr_array = ['product_id', 'sku', 'product_name', 'variant_name', 'price'];
        let item = { amount: 1 }

        if ($(this).find('input[name=amount]').val())
            item.amount = $(this).find('input[name=amount]').val();

        // Для страницы товара
        if ($(this).find('input[name=product][type="radio"]').length > 0) {
            attr_array.forEach(attr => {
                item[attr] = $(this).find('input[name=product]:checked').attr(attr) || null;
            });
        }

        // Для товаров в каталоге
        else if (product_item.find('input[name=product]').length > 0) {
            attr_array.forEach(attr => {
                item[attr] = $(this).attr(attr) || null;
            });

            // Get list data
            item.list_id = product_item.closest('.product_list').attr('list_id') || null;
            item.list_name = product_item.closest('.product_list').attr('list_name') || null;
        }

        $(document).trigger('addToCardEvent', item);
    });


    // Event
    $(document).on('addToCardEvent', function (e, item) {
        getCartInformer(item.product_id, item.amount, function () {

            // Popup
            const cart_url = $("#cart_informer a").attr('href');

            $.fancybox.open({
                type: 'ajax',
                src: cart_url,
                touch: false,
                closeExisting: true,
                afterShow: asignFancyAjax
            });
        });
    });


    $(document).on('documentHiddenEvent', function (e) {
        // navigator.sendBeacon("/log", 'analyticsData');
    })
});