{if $tax_amount > 0}
    <div>
        <span class="col-form-label">Комиссия сервиса : </span>
        <span>{$payment_method->settings->tax} % ({$tax_amount|price_html|raw})</span>
    </div>
{/if}

{if $tax_inside_amount > 0}
    <div>
        <span class="col-form-label">Комиссия сервиса внутреняя: </span>
        <span>{$payment_method->settings->tax_inside}% (<b>{(-$tax_inside_amount)|price_html:profit|raw}</b>)</span>
        <span class="who_pay"> - Оплачивает продавец</span>
    </div>
{/if}