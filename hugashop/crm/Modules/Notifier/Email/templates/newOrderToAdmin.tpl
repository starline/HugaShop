{* Письмо о заказе на почту админа *}

{if $order->paid}
	{$subject = "Заказ №`$order->id` оплачен" scope=global}
{else}
	{$subject = "Новый заказ №`$order->id`" scope=global}
{/if}

<div id="header">
	<h1 style="display: inline;">
		<a href="{$config->root_url}{'OrderAdmin'|urll:[id => $order->id]}">Заказ №{$order->id}</a>
	</h1>
	<span>от {$order->date|date}</span>
	<p>{$settings->company_name} - {$settings->company_description}</p>
</div>


<div id="customer">
	<h2>Получатель</h2>
	<p>{$order->name}</p>
	<p>{$order->phone}</p>
	<p>{$order->email}</p>
	<p>{$order->address}</p>
	<p>
		<i>{$order->comment|strip_tags|nl2br|raw}</i>
	</p>
</div>


<div id="purchases">
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
					<a href="{$config->root_url}{'Product'|urll:[url => $purchase->product->url]}">
						<img border="0" src="{$purchase->image->filename|resize:60:60:c}">
					</a>
				</td>
				<td>
					<a
						href="{$config->root_url}{'Product'|urll:[url => $purchase->product->url]}">{$purchase->product_name}</a>
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

{if !$delivery_method->name|empty}
	<div id="delivery" style="margin: 25px 0;">
		<h2>Доставка</h2>
		<div>{$delivery_method->name}</div>
		{if $order->delivery_price > 0}
			<div>
				{$order->delivery_price|price_html:no_html}
				{if $order->separate_delivery} (оплачивается отдельно){/if}
			</div>
		{/if}
	</div>
{/if}


<div id="total" style="margin: 25px 0;">
	<h2>Оплата</h2>
	{if $order->discount > 0}
		<tr>
			<th style="text-align: left;">Скидка</th>
			<td>{$order->discount} %</td>
		</tr>
	{/if}

	{if $order->coupon_discount > 0}
		<tr style="text-align: left;">
			<th>Купон{if $order->coupon_code}&nbsp;({$order->coupon_code}){/if}</th>
			<td>{$order->coupon_discount|price_html:no_html}</td>
		</tr>
	{/if}

	<tr>
		<th style="text-align: left;">Итого:</th>
		<td>{$order->payment_price|price_html:no_html}</td>
	</tr>

	{if !$payment_method->name|empty}
		<tr>
			<th style="text-align: left;">Оплата:</th>
			<td>{$payment_method->name}</td>
		</tr>
	{/if}
	</table>
</div>