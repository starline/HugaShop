<p>{$payment_method->settings->business_form} {$payment_method->settings->recipient}</p>
<p>{$payment_method->settings->recipient_adress}</p>

{if $payment_method->settings->recipient_phone}
    <p>Телефон: {$payment_method->settings->recipient_phone}</p>
{/if}

<p></p>

{if $form_type != 'packing_list'}
    <table cellspacing="0" cellpadding="4" border="1" style="font-size: 8;">
        <tr>
            <td width="60%" colspan="4" rowspan="2"><span>{$payment_method->settings->bank}</span>
                <br />
                <br />
                <br />
                <span style="font-size: 7;">Банк получателя</span>
            </td>
            <td width="10%">БИК</td>
            <td width="30%" rowspan="2">
                {$payment_method->settings->bik}<br /><br /><span>{$payment_method->settings->ks}</span>
            </td>
        </tr>
        <tr>
            <td width="10%">Сч. №</td>
        </tr>
        <tr>
            <td width="10%">ИНН</td>
            <td width="20%">{$payment_method->settings->ipn}</td>
            <td width="10%">КПП</td>
            <td width="20%"></td>
            <td width="10%" rowspan="2">Сч. №</td>
            <td width="30%" rowspan="2">{$payment_method->settings->account}</td>
        </tr>
        <tr>
            <td width="60%" colspan="4">
                <span>
                    {$payment_method->settings->business_form} {$payment_method->settings->recipient}
                </span>
                <br />
                <br />
                <span style="font-size: 7;">Получатель</span>
            </td>
        </tr>
    </table>
    <br />
    <br />
{/if}



{if $form_type == 'invoice'}
    <div style="font-size:16; text-align:center;">Счёт на оплату <b>№ {$order->id}</b> от {$order->date_spellput}</div>
{elseif $form_type == 'packing_list'}
    <div style="font-size:16; text-align:center;">Товарная накладная <b>№ {$order->id}</b> от {$order->date_spellput}</div>
{elseif $form_type == "commercial_offer"}
    <div style="font-size: 16; text-align:center;">Коммерческое предложение <b>№ {$order->id}</b> от {$order->date_spellput}
    </div>
{/if}

<br />



<table cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td width="12%">Поставщик:</td>
        <td style="text-align:left; width:50%">{$payment_method->settings->business_form}
            {$payment_method->settings->recipient}</td>
        {if $form_type != 'packing_list'}
            <td width="2%" rowspan="2"></td>
            <td width="22%" rowspan="2">
                <img
                    src="{$config->root_url}{'PaymentExchange'|link:[id => $payment_method->id]}?order_url={$order->url}&form_type=qrcode" />
            </td>
            <td width="2%" rowspan="2"></td>
            <td width="12%" rowspan="2"><br /><br /><span><b>QR-код для оплаты</b></span><br /><span
                    style="font-size: 7;">Отсканируйте код с
                    помощью банковского приложения</span></td>
        {/if}
    </tr>
    <tr>
        <td width="12%">Покупатель:</td>
        <td style="text-align:left; width:50%;">{$order->name}</td>
    </tr>
</table>

<p></p>

<table cellspacing="0" cellpadding="6" border="1" style="font-size: 8;">
    <tr>
        <th width="4%">№</th>
        <th width="8%">Арт.</th>
        <th width="59%">Товар</th>
        <th width="9%" style="text-align:center;">Кол-во</th>
        <th width="10%" style="text-align:center;">Цена</th>
        <th width="10%" style="text-align:center;">Сумма</th>
    </tr>
    {foreach $purchases as $key=>$purch}
        <tr>
            <td>{$key+1}</td>
            <td>{$purch->sku}</td>
            <td>{$purch->product_name}{if !empty($purch->variant_name)} - {$purch->variant_name}{/if}</td>
            <td style="text-align:right;">{$purch->amount}</td>
            <td style="text-align:right;">{$purch->price|price_convert:$payment_method->currency_id}</td>
            <td style="text-align:right;">
                {($purch->price * $purch->amount)|price_convert:$payment_method->currency_id}
            </td>
        </tr>
    {/foreach}
</table>

{if !empty($order->coupon_discount) and $order->coupon_discount > 0}
    <br />
    <div>Нельзя применять купоны при оплате на счет</div>
{/if}

<br />
<br />

<table cellspacing="0" cellpadding="3" border="0">
    <tr>
        <td width="85%" style="text-align:right;">
            <b>Итого к оплате:</b>
        </td>
        <td style="text-align:right;" width="15%">
            <b>{$order->payment_price|price_convert:$payment_method->currency_id}</b>
        </td>
    </tr>
    <tr>
        <td style="text-align:right; width:85%;">В том числе НДС:</td>
        <td style="text-align:right; width:15%;">Без НДС</td>
    </tr>
</table>

<br />

<div>Всего к оплате: <b>{$order->payment_price_spellout_int}</b>
    {$payment_method->currency->sign}.
    {if !$order->payment_price_spellout_dec|empty}
        <b>{$order->payment_price_spellout_dec}</b> {$payment_method->settings->pense}
    {/if}
</div>

<div style="border-top: 1px solid black;"></div>

<br />
<br />

{if $form_type == 'invoice'}
    <table cellspacing="0" cellpadding="0" border="0" style="font-size: 8;">
        <tr>
            <td width="15%">Поставщик</td>
            <td width="35%" style="text-align: center;">{$payment_method->settings->business_form}</td>
            <td width="3%"></td>
            <td width="22%"></td>
            <td width="3%"></td>
            <td width="22%" style="text-align: center;">{$payment_method->settings->recipient_short}</td>
        </tr>
        <tr>
            <td width="15%"></td>
            <td width="35%">
                <div style="border-top: 1px solid black; text-align: center;">должность</div>
            </td>
            <td width="3%"></td>
            <td width="22%">
                <div style="border-top: 1px solid black; text-align: center;">подпись</div>
            </td>
            <td width="3%"></td>
            <td width="22%">
                <div style="border-top: 1px solid black; text-align: center;">расшифровка подписи</div>
            </td>
        </tr>
    </table>
{elseif $form_type == 'packing_list'}
    <table cellspacing="0" cellpadding="6" border="0" style="font-size: 8">
        <tr>
            <td width="40%">
                <div style="text-align: left;">
                    <div style="border-top: 1px solid black;">Отгрузил(а)</div>
                </div>
            </td>
            <td width="20%"></td>
            <td width="40%">
                <div style="text-align: left;">
                    <div style="border-top: 1px solid black;">Получил(а)</div>
                </div>
            </td>
        </tr>
    </table>
{elseif $form_type == 'commercial_offer'}
    <table cellspacing="0" cellpadding="6" border="0" style="font-size: 8;">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <div style="text-align: left;">
                    <div style="border-top: 1px solid black;">Составил(а)</div>
                </div>
            </td>
        </tr>
    </table>
{/if}

<br />
<br />