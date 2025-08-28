{extends 'wrapper/order.tpl'}

{$meta_title = "Корзина"}

{block name=content}
	<div>
		<div class="container">
			<h1>
				{if $purchases}
					В корзине {$cart->purchases_count} {$cart->purchases_count|plural:'товар':'товаров':'товара'}
				{else}
					Корзина пуста
				{/if}
			</h1>

			{if $purchases}
				<form id="cart_form" method="post" name="cart" action="{'Cart'|linkLang}">
					{getCSRFInput}

					<div class="cart_purchases">

						{foreach $purchases as $purchase}
							<div class="py-4 border-top">

								<div class="delete mt-5">
									<a href="{'CartRemove'|linkLang:[product_id => $purchase->product->id]}" class="ajax">
										<img loading="lazy" src="{'/images/delete.png'|asset}" data-bs-toggle="tooltip"
											title="Удалить из корзины" alt="Удалить из корзины">
									</a>
								</div>

								<div class="row">
									<div class="col-auto">
										<a href="{'Product'|linkLang:[url => $purchase->product->url]}">
											<img class="object-fit-contain" width="120" height="120" loading="lazy"
												src="{$purchase->product->image->filename|resize:120:120:c}"
												alt="{$purchase->product->name}">
										</a>
									</div>
									<div class="col">
										<div>
											<a
												href="{'Product'|linkLang:[url => $purchase->product->url]}">{$purchase->product->name}</a>
											{if $purchase->product->variant_name} - {$purchase->product->variant_name}{/if}
										</div>

										<div class="row mt-1 g-4 text-end">
											<div class="col-12 col-lg-8">
												<select class="amount ms-auto form-select text-end"
													name="amounts[{$purchase->product->id}]">
													{$loop = ($purchase->product->custom || $purchase->product->stock == null) ? $settings->max_order_amount : $purchase->product->stock + 1}
													{section name=amounts start=1 loop=$loop step=1}
														<option value="{$smarty.section.amounts.index}"
															{if $purchase->amount == $smarty.section.amounts.index}selected{/if}>
															{$smarty.section.amounts.index} {$settings->units}</option>
													{/section}
												</select>
											</div>

											<div class="col-12 col-lg-4 fs-4">
												{($purchase->product->price * $purchase->amount)|price_html|raw}
											</div>
										</div>
									</div>

								</div>
							</div>
						{/foreach}
					</div>

					<div class="text-end border-top mt-4">
						<div class="py-3">
							<div class="fs-2 my-4">Итого: {$cart->purchases_price|price_html|raw}</div>

							<div class="row g-3">
								<div class="col-12">
									<a class="btn btn-primary" href="{'Checkout'|linkLang}">Оформить заказ</a>
								</div>
							</div>
						</div>
					</div>
				</form>

			{else}
				<div class="py-5">
					Выберите товары в каталоге. Когда корзина будет сформирована, можно будет оформить заказ. Приятных покупок!
				</div>
			{/if}
		</div>
	</div>

	<script type="module">
		$(function() {
			$('select[name*=amounts]').change(function() {
				$('form[name=cart]').submit();
			});

			$('.fancy_close').click(function() {
				$.fancybox.close();
			});
		});
	</script>
{/block}