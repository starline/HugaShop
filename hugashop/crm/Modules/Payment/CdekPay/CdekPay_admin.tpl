{if $fee_amount>0}
    <div>
        <span class="col-form-label">Комиссия сервиса CDEK {$payment_method->settings->fee}%: </span>
        <b>{$fee_amount|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит покупатель</span>
    </div>
{/if}

{if $tax_amount>0}
    <div>
        <span class="col-form-label">Налоги ИП {$payment_method->settings->tax}%: </span>
        <b>{$tax_amount|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит покупатель</span>
    </div>
{/if}

{if $sum_fee_tax}
    <div class="mt-2">
        <span class="col-form-label">Всего издержек {(($sum_fee_tax/$order->payment_price)*100)|number:2}%: </span>
        <b class="color_red">{$sum_fee_tax|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит покупатель</span>
    </div>
{/if}


{if $fee_inside_amount > 0}
    <div>
        <span class="col-form-label">Комиссия сервиса {$payment_method->settings->fee_inside}%: </span>
        <b class="color_red">-{$fee_inside_amount|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}

{if $tax_inside_amount > 0}
    <div>
        <span class="col-form-label">Налоги ИП {$payment_method->settings->tax_inside}%: </span>
        <b class="color_red">-{$tax_inside_amount|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}

{if $sum_inside}
    <div class="mt-2">
        <span class="col-form-label">Всего издержек {(($sum_inside/$order->payment_price)*100)|number:2}%: </span>
        <b class="color_red">-{$sum_inside|price_html:$payment_currency->code|raw}</b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}