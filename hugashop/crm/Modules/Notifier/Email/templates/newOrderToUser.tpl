{* Письмо о заказе на почту заказчика *}

{$subject = "Заказ №`$order->id`" scope=global}

<div id="header">
	<h1 style="display: inline;">Заказ №{$order->id} {if $order->paid}, оплачен{/if}</h1>
	<span>от {$order->date|date}</span>
	<h2>{$settings->company_name} - {$settings->company_description}</h2>
</div>


<div id="customer">
	<h2>Получатель</h2>
	<p>{$order->name}</p>
	<p>{$order->phone}</p>
	<p>{$order->email}</p>
	<p>{$order->address}</p>
	<p><i>{$order->comment|strip_tags|nl2br|raw}</i></p>
</div>


<div id="purchases" style="margin: 25px 0;">
	<table>
		<tr>
			<th></th>
			<th>Товар</th>
			<th>Цена</th>
			<th>Кол-во</th>
			<th>Всего</th>
		</tr>

		{foreach $purchases as $purchase}
			<tr>
				<td>
					<a href="{$config->root_url}{'Product'|link:[url => $purchase->product->url]}">
						<img style="display: block; width: 100px;" border="0"
							src="{$purchase->product->image->filename|resize:120:120:c}">
					</a>
				</td>
				<td>
					<a
						href="{$config->root_url}{'Product'|link:[url => $purchase->product->url]}">{$purchase->product_name}</a>
					{$purchase->variant_name} {if $purchase->sku} (арт {$purchase->sku}){/if}
				</td>
				<td>
					{$purchase->price|price_html:no_html}
				</td>
				<td>
					{$purchase->amount} {$settings->units}
				</td>
				<td>
					{($purchase->price * $purchase->amount)|price_html:no_html}
				</td>
			</tr>
		{/foreach}

	</table>
</div>


<div id="delivery" style="margin: 25px 0;">
	<h2>Доставка</h2>
	<div>{$delivery_method->public_name}</div>

	{if $order->delivery_price>0}
		<div>
			{$order->delivery_price|price_html:no_html}
			{if $order->separate_delivery} (оплачивается отдельно){/if}
		</div>
	{/if}
</div>


<div id="total" style="margin: 25px 0;">
	<h2>Оплата</h2>
	<table>
		{if $order->discount>0}
			<tr>
				<th style="text-align: left;">Скидка</th>
				<td>{$order->discount} %</td>
			</tr>
		{/if}

		{if $order->coupon_discount > 0}
			<tr>
				<th style="text-align: left;">Купон{if $order->coupon_code} ({$order->coupon_code}){/if}</th>
				<td>{$order->coupon_discount|price_html:no_html}</td>
			</tr>
		{/if}

		<tr>
			<th style="text-align: left;">Итого</th>
			<td>{$order->payment_price|price_html:no_html}</td>
		</tr>

		{if $payment_method}
			<tr>
				<th style="text-align: left;">Оплата:</th>
				<td>{$payment_method->public_name}</td>
			</tr>
		{/if}
	</table>
</div>

<p>
	Вы всегда можете проверить состояние заказа по ссылке:<br>
	<a href="{$config->root_url}{'Order'|link:[id => $order->id, order_url => $order->url]}">
		{$config->root_url}{'Order'|link:[id => $order->id, order_url => $order->url]}
	</a>
</p>