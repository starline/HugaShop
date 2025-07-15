{if $tax_amount}
    <div>
        <span class='col-form-label'>Налоги ФОП {$payment_method->settings->tax}%: </span>
        <b>{$tax_amount|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит покупатель</span>
    </div>
{/if}

{if $tax_inside_amount}
    <div>
        <span class='col-form-label'>Налоги ИП {$payment_method->settings->tax_inside}%: </span>
        <b class='color_red'>-{$tax_inside_amount|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}

<h4 class="mt-4">Докуметы</h4>

<div class="row gx-5">
    <div class="col-lg-6">
        <label class="form-label" for='payment_name'>Наименование плательщика: </label>
        <input class="form-control" id='payment_name' autocomplete='off' type='text' name="order_settings[payment_name]"
            value="{$order->settings->payment_name}" />
    </div>
</div>

<div class="row gx-5 mt-3">
    <div class="col-6 col-lg-3">
        <input class="form-control text-center" id="payment_checkdate" autocomplete="off" type="text"
            name="order_settings[payment_checkdate]" value="{$order->settings->payment_checkdate ?? ''}"
            placeholder="дата рахунку" />
    </div>
    <div class="col-6 col-lg-3 d-grid">
        <a class="btn btn-light"
            href="{'PaymentExchange'|link:[id => $payment_method->id]}?order_url={$order->url}&form_type=invoice"
            target="_blank" data-bs-toggle="tooltip" title="Сформировать Счет">Рахунок</a>
    </div>
</div>

<div class="row gx-5 mt-3">
    <div class="col-6 col-lg-3">
        <input class="form-control text-center" id="packing_checkdate" autocomplete="off" type="text"
            name="order_settings[packing_checkdate]" value="{$order->settings->packing_checkdate ?? ''}"
            placeholder="дата видатковоi" />
    </div>
    <div class="col-6 col-lg-3 d-grid">
        <a class="btn btn-light"
            href="{'PaymentExchange'|link:[id => $payment_method->id]}?order_url={$order->url}&form_type=packing_list"
            data-bs-toggle="tooltip" target="_blank" title="Сформировать Расходную накладную">Видаткова</a>
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
            $('input[name="order_settings[packing_checkdate]"]').datepicker({
                regional: 'ru'
            });
        });
    {/literal}
</script>