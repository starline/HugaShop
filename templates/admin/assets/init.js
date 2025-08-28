/**
 * Welcome to your app's main JavaScript file!
 * 
 * @author Andri Huga
 * @version 2.4
 */

import './js/fancybox/jquery.fancybox.min.css';
import './css/style.css';

import './js/jquery/jquery.min.js';
import './js/fancybox/jquery.fancybox.min.js';
import './js/bootstrap.bundle.min.js';
import './js/jquery/jquery-ui.js';
import './js/jquery/jquery.form.js';
import './js/autocomplete/jquery.autocomplete.min.js';
import './js/ctrlnavigate.js';
import { assignTooltip, assignButton, asignFancyOpen } from './js/common.js';

$(function () {

    // Сортировка списка
    $(".sortable_on").sortable({
        items: ".list_row:not(.sortable_off)",
        handle: ".move_zone",
        cancel: ".sortable_off",
        tolerance: "pointer",
        opacity: 0.90,
        axis: "y",
        update: function (event, ui) {
            $(this).find("input[name*='check']").prop('checked', false);

            // listt_form
            if ($(this).closest("form.list_form").length) {
                $(this).closest("form.list_form").ajaxSubmit();
            }
        }
    });

    asignFancyOpen();
    assignButton('button.btn');
    assignTooltip();


    // Выделить все
    $("#check_all").on('click', function () {
        $('.list input[type="checkbox"][name*="check"]').prop('checked', $(
            '.list input[type="checkbox"][name*="check"]:not(:checked)').length > 0);
    });


    // Удалить 
    $("form.list_form").on('click', 'i.delete', function () {
        $('.list input[type="checkbox"][name*="check"]')
            .prop('checked', false);
        $(this).closest(".list_row").find('input[type="checkbox"][name*="check"]')
            .prop('checked', true);
        $(this).closest("form.list_form").find('select[name="action"] option[value=delete]')
            .prop('selected', true);

        $(this).closest("form.list_form").trigger('submit');
    });


    // Подтверждение удаления
    $("form.list_form").on('submit', function () {
        if ($('.list input[type="checkbox"][name*="check"]:checked').length > 0)
            if ($('select[name="action"]').val() == 'delete' && !confirm('Подтвердите удаление'))
                return false;
    });


    // Clipboard field
    $("span, div").on('click', '.copy_field', function () {
        navigator.clipboard.writeText($(this).attr('value'));
    });


    // Редактировать примечание (universal)
    $("i.edit_note").on('click', function () {
        let layer = $(this).closest('div.note_wrap');
        let text_height = layer.find("div.view_note").height() + 5;
        layer.find("div.edit_note textarea").height(text_height);
        layer.find("div.view_note").hide();
        layer.find("div.edit_note").show();
        return false;
    });
});