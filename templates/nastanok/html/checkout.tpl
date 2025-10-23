{extends 'wrapper/order.tpl'}

{$meta_title = "Оформление заказа" scope=global}

{block name=content}
	<div class="info-box">
		Заполните данные получателя и реквизиты доставки. <br>
		Если у вас возникли дополнительные вопросы по поводу вашего заказа, не
		стесняйтесь, перезвоните нам по контактным телефонам - это ускорит обработку вашего заказа.
	</div>

	<h2 class="cart_info">
		{if $purchases}
			{$cart->purchases_count} {$cart->purchases_count|plural:'товар':'товаров':'товара'}
		{/if}
	</h2>

	<form method="post" name="cart" id=cart action="/checkout">
		<input name="ya_client_id" type="hidden" value="" />
		{getCSRFInput}

		<div class="row">
			<div class="col-12 col-lg-8" id="main_list">

				{if $purchases}
					<div id="purchases">
						{foreach $purchases as $purchase}
							<div class="purchase_row">
								<div class="image">
									<a href="{'Product'|linkLang:[url => $purchase->product->url]}">
										<img loading="lazy" src="{$purchase->product->image->filename|resize:60:60:c}"
											alt="{$purchase->product->name}">
									</a>
								</div>
								<div class="name">
									<a href="{'Product'|linkLang:[url => $purchase->product->url]}">{$purchase->product->name}</a>
									{if $purchase->variant->name}
										- {$purchase->variant->name}
									{/if}
								</div>
								<div class="amount">{$purchase->variant->price|price_html|raw} &times;
									{$purchase->amount}
									{$settings->units}</div>
								<div class="price purchase_total_price">
									{($purchase->variant->price * $purchase->amount)|price_html|raw}
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

					</div>

					<div class="form_block">
						<h2>1. Информация о получателе</h2>
						<div class="wrapper">
							<div class="row g-4">

								<div class="col-lg-6">
									<label class="form-label" for="phone">Телефон <span>(Обязательно)</span></label>
									<input class="form-control {if phone|in_array:$form_invalid}is-invalid{/if}" id="phone"
										name="phone" type="text" value="{$order->phone}" autocomplete="tel"
										placeholder="Телефон" />
									<div class="invalid-feedback">Введите Телефон</div>
								</div>

								<div class="col-lg-6">
									<label class="form-label" for="name">Имя <span>(обязательно)</span> Фамилия <span
											data-bs-toggle="tooltip"
											title="Фамилия не обзятельна. Но это ускорить обработку вашего заказа.">(не
											обязательно)</span></label>
									<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" id="name"
										name="name" type="text" value="{$order->name}" autocomplete="name"
										placeholder="Имя и Фамилия" />
									<div class="invalid-feedback">Введите Имя и Фамилия</div>
								</div>


								<div class="col-lg-6">
									<label class="form-label" for="email">Email <span>(Сюда отправим инфрмацию о
											заказe)</span></label>
									<input class="form-control" id="email" name="email" type="email" value="{$order->email}"
										autocomplete="email" placeholder="Email" />
								</div>

								<div class="col-lg-6">
									<label class="form-label" for="address">Город доставки</label>
									<input class="form-control" id="address" name="address" type="text"
										value="{$order->address}" autocomplete="address-level1" placeholder="Город доставки" />
								</div>

								<div class="col-12">
									<label class="form-label" for="comment">Номер отделения или комментарий к заказу</label>
									<textarea class="form-control" id="comment" name="comment" id="order_comment"
										placeholder="Комментарий">{$order->comment|strip_tags|nl2br|raw}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="form_block">
						{if $delivery_methods}
							<h2>2. Выберите способ доставки:</h2>
							<div class="wrapper">
								<ul id="deliveries">
									{foreach $delivery_methods as $delivery}
										<li>
											<div class="checkbox">
												<input type="radio" name="delivery_id" value="{$delivery->id}"
													{if $order->delivery_id == $delivery->id}checked{/if}
													id="deliveries_{$delivery->id}" />
												<label for="deliveries_{$delivery->id}">{$delivery->public_name}

													{if $order->total_price < $delivery->free_from && $delivery->price>0}
														<span class="delivery_price">{$delivery->price|price_html|raw}</span>
													{elseif $order->total_price >= $delivery->free_from}
														<span class="delivery_price">бесплатно</span>
													{/if}
												</label>
											</div>

											<div class="description">
												{$delivery->description|raw}
											</div>
										</li>
									{/foreach}
								</ul>
							</div>
						{/if}
					</div>

					<div class="form_block">
						{if $payment_methods}
							<h2>3. Выберите способ оплаты</h2>
							<div class="wrapper">
								<ul id="payments">
									{foreach $payment_methods as $payment_method}
										<li>
											<div class="checkbox">
												<input type="radio" name="payment_method_id" value='{$payment_method->id}'
													{if $payment_method->id == $order->payment_method_id}checked{/if}
													id="payment_{$payment_method->id}">
												<label for="payment_{$payment_method->id}">{$payment_method->public_name}</label>
											</div>
											<div class="description">
												{$payment_method->description|raw}
											</div>
										</li>
									{/foreach}
								</ul>
							</div>
						{/if}
					</div>


				{else}
					<p>
						Выберите товары в каталоге. Когда корзина будет сформирована, можно будет оформить заказ. Приятных
						покупок!
					</p>
				{/if}

			</div>

			<div class="col-12 col-lg-4" id="right_menu">

				{if $coupon_request}
					<div class="form_block">
						<div class="wrapper">
							<div class="col-12">
								<label class="form-label" for="coupon_code">Введите промокод <span>узнай свою
										скидку</span></label>
								<input class="form-control {if coupon|in_array:$form_invalid}is-invalid{/if}" id="coupon_code"
									name="coupon_code" type="text" value="{$pre_order->coupon_code}" autocomplete="off"
									placeholder="Код" />
								<div class="invalid-feedback">Такого промокода у нас нет</div>
							</div>
							<div class="mt-2">
								{include file="parts/button.tpl" label="Применить промокод" class="btn-light" type="submit" extra_attrs='name=promocod value=true form=cart'}
							</div>
						</div>
					</div>
				{/if}

				<div class="form_block">
					<div class="wrapper">
						<div class="row checkout_total">
							<div class="left_part">Итого:</div>
							<div class="right_part">{$cart->purchases_price|price_html|raw}</div>
						</div>
						<div class="row mt-4">
							<button class="btn btn-primary" form="cart" type="submit" name="checkout"
								value="true">Подтвердить заказ</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

	<script type="text/javascript" src="{'js/jquery/jquery.mask.js'|asset}"></script>

	<script>
		{literal}

			// Добавляем +380
			$('#phone').focus(function() {
				if (!$(this).val()) {
					element = document.getElementById("phone");
					element.value = '+7';
					setTimeout(function() {
						let end = element.value.length;
						element.setSelectionRange(end, end);
					}, 100);
				}
			});

			$('#phone').blur(function() {
				if ($(this).val() == '+7') {
					$(this).val('');
				}
			});

			// Устанавливаем формат номера
			$('#phone').mask('+7 (000) 000-00-00', {
				placeholder: "Номер телефона",
			});

			// Не даем удалить +7
			$('#phone').keydown(function(e) {
				let cursorPosition = $(this).selectionStart;
				if (e.keyCode == 8 && $(this).val() == '+7' || cursorPosition < 2) {
					e.preventDefault();
				}
			});
		{/literal}
	</script>
{/block}