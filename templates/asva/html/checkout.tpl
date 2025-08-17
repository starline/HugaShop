{extends 'wrapper/order.tpl'}

{$meta_title = "Оформление заказа"}

{block name=content}
	<form class="checkout_wrapper" method="post" name="cart" id="cart" action="/checkout">
		{getCSRFInput}

		<div class="row">
			<div class="col-12 col-lg-8" id="main_list">

				{if !$purchases|empty}
					<div class="form_block">
						<h2>Получатель</h2>
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
									<label class="form-label" for="name">Имя, фамилия <span>(Обязательно)</span></label>
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
										placeholder="Комментарий">{$order->comment}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="form_block">
						{if $delivery_methods}
							<h2>Доставка</h2>
							<div class="acc_wrapper accordion" id="deliveries">
								{foreach $delivery_methods as $delivery}
									<h3>
										<div class="checkbox">
											<input type="radio" name="delivery_id" value="{$delivery->id}"
												{if $order->delivery_id == $delivery->id || $delivery@index == 0}checked{/if}
												id="deliveries_{$delivery->id}">
											<label for="deliveries_{$delivery->id}">{$delivery->public_name}

												{if $order->total_price < $delivery->free_from && $delivery->price > 0}
													<span class="delivery_price">от {$delivery->price|price_html|raw}</span>
												{elseif $order->total_price >= $delivery->free_from || $delivery->price == 0}
													<span class="delivery_price">бесплатно</span>
												{/if}
											</label>
										</div>
									</h3>
									<div class="acc_body">
										<div class="description">
											{$delivery->description|raw}
										</div>
									</div>
								{/foreach}
							</div>
						{/if}
					</div>

					<div class="form_block">
						{if $payment_methods}
							<h2>Оплата</h2>
							<div class="acc_wrapper accordion" id="payments">
								{foreach $payment_methods as $payment_method}
									<h3 class="acc_header">
										<div class="checkbox">
											<input type="radio" name="payment_method_id" value='{$payment_method->id}'
												{if $payment_method->id == $order->payment_method_id || $payment_method@index == 0}checked{/if}
												id="payment_{$payment_method->id}">
											<label for="payment_{$payment_method->id}">{$payment_method->public_name}</label>
										</div>
									</h3>
									<div class="acc_body">
										<div class="description">
											{$payment_method->description|raw}
										</div>
									</div>
								{/foreach}
							</div>
						{/if}
					</div>
				{else}
					<p>
						Выберите товары в каталоге. Когда корзина будет сформирована, можно будет оформить заказ.
						Приятных покупок!
					</p>
				{/if}
			</div>


			<div class="col-12 col-lg-4" id="right_menu">
				<div class="info-box">
					Заполните данные получателя и реквизиты доставки. Если у вас возникли вопросы по поводу вашего заказа,
					не стесняйтесь перезвонить нам по контактным телефонам.
				</div>

				<div class="checkout_purchases">
					{foreach $purchases as $purchase}
						<div class="purchase_row">
							<div class="image">
								<img loading="lazy" src="{$purchase->product->image->filename|resize:60:60:c}"
									alt="{$purchase->product->name}">
							</div>
							<div class="name">
								<div>{$purchase->product->name}
									{if $purchase->product->variant_name}
										- {$purchase->product->variant_name}
									{/if}
								</div>

								<div class="amount">
									{$purchase->product->price|price_html|raw} &times; {$purchase->amount}
									{$settings->units}
								</div>
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


				{if $coupon_request}
					<div class="form_block">
						<div class="wrapper">
							{if $coupon_error}
								<div class="message_error">
									{if $coupon_error == 'invalid'}Такого промокода у нас нет{/if}
								</div>
							{/if}
							<div class="row">
								<label for="coupon_code">Введите промокод <span>узнай свою скидку</span></label>
								<input id="coupon_code" name="coupon_code" type="text" value="{$order->coupon_code}"
									autocomplete="off" />
							</div>
							<div class="row">
								<button class="button btn_grey" form=cart type="submit" name="promocod" value="true">
									Применить промокод
								</button>
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
						<button class="btn btn-primary mt-5" form="cart" type="submit" name="checkout" value="true">
							Подтвердить заказ
						</button>
					</div>
				</div>

				<div class="policy_info">
					Ваша информация будет сохранена в учетной записи Магазина. Продолжая, вы соглашаетесь с <a
						href="{'Page'|linkLang:[url => 'policy']}" title="Политика конфиденциальности">Условиями
						обслуживания Магазина</a>.
				</div>
			</div>
		</div>
	</form>
{/block}


{block name=body_script append}
	<script type="module">
		import '{"js/jquery/jquery.mask.js"|asset}';

		{literal}
			$(function() {
				$(".accordion").accordion({
					'heightStyle': 'content',
					'icons': false,
					activate: function(event, ui) {
						$("input", ui.newHeader).prop('checked', true);
					}
				});

				// Добавляем +380
				$('#phone').focus(function() {
					if (!$(this).val()) {
						let element = document.getElementById("phone");
						element.value = '+38';
						setTimeout(function() {
							let end = element.value.length;
							element.setSelectionRange(end, end);
						}, 100);
					}
				});

				$('#phone').blur(function() {
					if ($(this).val() == '+38') {
						$(this).val('');
					}
				});

				// Устанавливаем формат номера
				$('#phone').mask('+38 (000) 000-00-00', {
					placeholder: "Номер телефона",
				});

				// Не даем удалить +380
				$('#phone').keydown(function(e) {
					let cursorPosition = $(this).selectionStart;
					if (e.keyCode == 8 && $(this).val() == '+38' || cursorPosition < 3) {
						e.preventDefault();
					}
				});
			});
		{/literal}
	</script>
{/block}