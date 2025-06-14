{extends 'wrapper/main.tpl'}

{$meta_title = 'Заказы'}

{block name=content}
	<div class="profile row">
		<div class="col-lg-3" id="catalog_menu">
			<ul>
				<li class="category_main">
					<a class="{if $route|in_array:[UserOrderList]}selected{/if}" href="{'UserOrderList'|urll}">Заказы</a>
				</li>
				<li class="category_main">
					<a class="{if $route|in_array:[User]}selected{/if}" href="{'User'|urll}">Личные данные</a>
				</li>
			</ul>
		</div>

		<div class="col-lg-9">
			<h1>{'Ваши заказы'|trans}</h1>
			<div class="container">
				{foreach $orders as $order}
					<div class="row my-4 pb-4 border-bottom">
						<div class="col-lg-8">
							<div>
								<a href="{'Order'|urll:[id => $order->id, order_url => $order->url]}">Заказ
									№{$order->id}</a> от {$order->date|date}

								{if $order->paid == 1}
									<div class="badge text-bg-success">оплачен</div>
								{/if}

								<div class="badge text-bg-secondary">
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
								</div>
							</div>

							{if !$order->purchases|empty}
								<div class="purchases mt-4">
									{foreach $order->purchases as $purchase}
										<div class="image">
											<div class="amount">{$purchase->amount}</div>
											<img data-bs-toggle="tooltip"
												title="{$purchase->product_name} {if $purchase->variant_name} - {$purchase->variant_name}{/if}"
												src="{if $purchase->image_filename}{$purchase->image_filename|resize:60}{else}{'images/cargo.png'|asset}{/if}" />
										</div>
									{/foreach}
								</div>
							{/if}
						</div>

						<div class="col-lg-4 text-end">
							<div class="fs-4">
								{$order->payment_price|price_html|raw}
							</div>
						</div>
					</div>
				{/foreach}

			</div>
		</div>
	</div>
{/block}