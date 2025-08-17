{if $tax_amount}
    <div>
        <span class='col-form-label'>Налоги ИП {$payment_method->settings->tax}%: </span>
        <b>{$tax_amount|number} <span class="price_sign">{$payment_currency->sign}</span></b>
        <span class="who_pay"> - Платит покупатель</span>
    </div>
{/if}

{if $tax_inside_amount}
    <div>
        <span class='col-form-label'>Налоги ИП {$payment_method->settings->tax_inside}%: </span>
        <b class='color_red'>-{$tax_inside_amount|number}<span
                class="price_sign">{$payment_currency->sign}</span></b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}

<h4 class="mt-4">Квитанции</h4>

<div class="mt-4">
    <div class="row">
        <div class="col-lg-6">
            <label class="form-label" for='payment_name'>Наименование плательщика: </label>
            <input class="form-control" id='payment_name' autocomplete='off' type='text'
                name="order_settings[payment_name]" value="{$order->settings->payment_name}" />
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-3">
            <label class="form-label" for='payment_checkdate'>Дата выставления счета: </label>
            <input class="form-control" id='payment_checkdate' autocomplete='off' type='text'
                name="order_settings[payment_checkdate]" value="{$order->settings->payment_checkdate ?? ''}" />
        </div>
    </div>

    <div class="btn_row left">
        <a class="btn btn-light"
            href="{'PaymentExchange'|link:[id => $payment_method->id]}?order_token={$order->token}&form_type=invoice"
            target="_blank" data-bs-toggle="tooltip" title="Сформировать Счет">Счет</a>
        <a class="btn btn-light"
            href="{'PaymentExchange'|link:[id => $payment_method->id]}?order_token={$order->token}&form_type=packing_list"
            data-bs-toggle="tooltip" target="_blank" title="Сформировать Расходную накладную">Накладная</a>
        <a class="btn btn-light"
            href="{'PaymentExchange'|link:[id => $payment_method->id]}?order_token={$order->token}&form_type=commercial_offer"
            data-bs-toggle="tooltip" target="_blank" title="Коммерческое предложение">КП</a>
    </div>
</div>

<script type="module">
    import '{"js/jquery/datepicker/jquery.ui.datepicker-ru.js"|asset}';

    {literal}
        $(function() {

            // Выбор даты
            $('input[name="order_settings[payment_checkdate]"]').datepicker({
                regional: 'ru'
            });
        });
    {/literal}
</script>