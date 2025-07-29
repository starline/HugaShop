{extends 'wrapper/order.tpl'}

{$meta_title = "Заказ №{$order->id}"}

{block name=content}

	{if $order->status == 0}
		<div class="message message_success">
			<span class="text">
				Информация о доставке и оплате успешно сохранена.<br>
				В ближайшее время мы свяжемся с вами для уточнения деталей заказа.
			</span>
		</div>
	{/if}

	<h1>
		Заказ №{$order->id}
		{if $order->status == 0}принят в обработку{/if}
		{if $order->status == 1}готовится к отправке{/if}
		{if $order->status == 2}выполнен{/if}
		{if $order->status == 3}отклонен{/if}
		{if $order->paid == 1}, оплачен{/if}
	</h1>

	<div class="row">
		<div class="col-12 col-lg-8" id="main_list">

			<!-- Товары -->
			<div id="purchases">

				{foreach $purchases as $purchase}
					<div class="purchase_row">
						<div class="image">
							<a href="{'Product'|link:[url => $purchase->product->url]}">
								<img loading="lazy" src="{$purchase->product->image->filename|resize:120:120:c}" alt="{$product->name}">
							</a>
						</div>

						<div class="name">
							<a href="{'Product'|link:[url => $purchase->product->url]}">{$purchase->product->name}</a>
							{$purchase->variant->name}
						</div>

						<div class="amount">
							{$purchase->price|price_html|raw} &times;
							{$purchase->amount}&nbsp;{$settings->units}
						</div>

						<div class="price purchase_total_price">
							{($purchase->price * $purchase->amount)|price_html|raw}
						</div>
					</div>
				{/foreach}

				{if $order->discount > 0}
					<div class="purchase_row">
						<div class="amount">скидка</div>
						<div class="price">
							{$order->discount}&nbsp;%
						</div>
					</div>
				{/if}

				{if $order->coupon_discount > 0}
					<div class="purchase_row">
						<div class="amount">купон</div>
						<div class="price">
							&minus;{$order->coupon_discount|price_html|raw}
						</div>
					</div>
				{/if}

				{* Если стоимость доставки входит в сумму заказа *}
				{if !$order->separate_delivery && $order->delivery_price > 0}
					<div class="purchase_row">
						<div class="amount">{$delivery->name}</div>
						<div class="price">
							{$order->delivery_price|price_html|raw}
						</div>
					</div>
				{/if}
			</div>

			<!-- Получатель -->
			<div class="form_block">
				<h2>Информация о получателе</h2>

				<div class="wrapper">
					<div class="row g-4">
						<div class="col-6">
							<div class="label">Телефон</div>
							<div class="value">{$order->phone}</div>
						</div>

						{if $order->name}
							<div class="col-6">
								<div class="label">Имя</div>
								<div class="value">{$order->name}</div>
							</div>
						{/if}

						{if $order->email}
							<div class="col-6">
								<div class="label">Email</div>
								<div class="value">{$order->email}</div>
							</div>
						{/if}

						{if $order->address}
							<div class="col-6">
								<div class="label">Город доставки</div>
								<div class="value">{$order->address}</div>
							</div>
						{/if}

						{if $order->comment}
							<div class="col-12">
								<div class="label">Номер отделения или комментарий к заказу</div>
								<div class="value">{$order->comment|strip_tags|nl2br|raw}</div>
							</div>
						{/if}
					</div>
				</div>
			</div>

			<!-- Доставка -->
			{if $delivery}
				<div class="form_block">
					<h2>Cпособ доставки</h2>
					<div class="wrapper">
						<div class="form_item_name">{$delivery->public_name}
							{if $order->total_price < $delivery->free_from && $delivery->price > 0}
								<span class="delivery_price">{$delivery->price|price_html|raw}</span>
							{elseif $order->total_price >= $delivery->free_from}
								<span class="delivery_price">бесплатно</span>
							{/if}
						</div>
						<div class="form_item_description">
							{$delivery->description|raw}
						</div>
					</div>
				</div>
			{/if}

			<!-- Оплата -->
			{if $payment_method}
				<div class="form_block">
					<h2>Cпособ оплаты</h2>
					<div class="wrapper">
						<div class="form_item_name">{$payment_method->public_name}</div>
						<div class="form_item_description">
							{$payment_method->description|raw}
						</div>
						<div class="payment_form">
							{get_payment_module_html order_id=$order->id module=$payment_method->module view_type='order'}
						</div>
					</div>
				</div>
			{/if}
		</div>

		<div class="col-12 col-lg-4" id="right_menu">
			<div class="form_block">
				<div class="wrapper">
					<div class="row checkout_total">
						<div class="left_part">К оплате:</div>
						<div class="right_part">{$order->payment_price|price_html|raw}</div>
					</div>
				</div>
			</div>
		</div>

	</div>
{/block}