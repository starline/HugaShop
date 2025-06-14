{if $fee_inside_amount > 0}
    <div>
        <span class="col-form-label">Комиссия сервиса {$payment_method->settings->fee_inside}%: </span>
        <b>-{$fee_inside_amount|number} {$payment_currency->sign}</b>
    </div>
{/if}

{if $fee_fix_inside_amount > 0}
    <div>
        <span class="col-form-label">Платеж сервису за операцию: </span>
        <b>-{$fee_fix_inside_amount|number} {$payment_currency->sign}</b>

    </div>
{/if}

{if $sum_inside}
    <div class="mt-2">
        <span class="col-form-label">Всего издержек {(($sum_inside/$order->payment_price)*100)|number:2}%: </span>
        <b class="color_red">-{$sum_inside|number} {$payment_currency->sign}</b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}