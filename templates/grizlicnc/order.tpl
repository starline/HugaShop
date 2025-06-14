{extends 'wrapper/order.tpl'}

{$meta_title = "Заказ №`$order->id`" scope=global}

{block name=content}
	{if $order->status == 0 and $message_success == 'added'}
		<div class="message message_success">
			<span class="text">
				Информация о доставке и оплате успешно сохранена.<br>
				В ближайшее время мы свяжемся с вами для уточнения деталей заказа.
			</span>
		</div>
	{/if}

	<h1>Заказ №{$order->id}
		<span class="badge text-bg-secondary">
			{if $order->status == 0}
				принят в обработку
			{/if}
			{if $order->status == 1}
				готовится к отправке
			{/if}
			{if $order->status == 4}
				отправлен
			{/if}
			{if $order->status == 2}
				выполнен
			{/if}
			{if $order->status == 3}
				отклонен
			{/if}
		</span>

		{if $order->paid == 1}
			<span class="badge text-bg-success">оплачен</span>
		{/if}
	</h1>

	<div class="row">
		<div class="col-12 col-lg-8" id="main_list">

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
							{if $order->subtotal_price < $delivery->free_from && $delivery->price > 0}
								<span class="delivery_price">{$delivery->price|price_html|raw}</span>
							{elseif $order->subtotal_price >= $delivery->free_from}
								<span class="delivery_price">(бесплатно)</span>
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
			<h2>Заказ</h2>
			<div class="checkout_purchases">
				{foreach $purchases as $purchase}
					<div class="purchase_row">
						<div class="image">
							<img loading="lazy" src="{$purchase->image->filename|resize:60:60}"
								alt="{$purchase->product->name}">
						</div>
						<div class="name">
							<div>{$purchase->product->name}
								{if $purchase->variant->name}
									- {$purchase->variant->name}
								{/if}
							</div>

							<div class="amount">
								{$purchase->variant->price|price_html|raw} &times; {$purchase->amount}
								{$settings->units}
							</div>
						</div>
					</div>
				{/foreach}
			</div>

			<div class="form_block">
				<div class="wrapper">

					{* Если стоимость доставки входит в сумму заказа *}
					{if !$order->separate_delivery && $order->delivery_price > 0}
						<div class="row mb-4">
							<div class="col-6">{$delivery->public_name}</div>
							<div class="col-6 text-end">
								<b>{$order->delivery_price|price_html|raw}</b>
							</div>
						</div>
					{/if}

					{if $order->discount > 0}
						<div class="row mb-4">
							<div class="col-6">Скидка</div>
							<div class="col-6 text-end">
								<b>{$order->discount}&nbsp;%</b>
							</div>
						</div>
					{/if}

					{if $order->coupon_discount > 0}
						<div class="row mb-4">
							<div class="col-6">Купон</div>
							<div class="col-6 text-end">
								<b>&minus;{$order->coupon_discount|price_html|raw}</b>
							</div>
						</div>
					{/if}

					<div class="row mt-4">
						<div class="col-6 h2">К оплате:</div>
						<div class="col-6 text-end h2">{$order->payment_price|price_html|raw}</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/block}