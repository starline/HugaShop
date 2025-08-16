{extends 'wrapper/main.tpl'}

{$meta_title = 'Заказы'|trans}

{block name=content}
	<div class="profile row">
		<div class="col-lg-3" id="catalog_menu">
			<ul>
				<li class="category_main">
					<a class="{if $route|in_array:[UserOrderList]}selected{/if}"
						href="{'UserOrderList'|linkLang}">{'Заказы'|trans}</a>
				</li>
				<li class="category_main">
					<a class="{if $route|in_array:[User]}selected{/if}" href="{'User'|linkLang}">{'Личные данные'|trans}</a>
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
								<a href="{'Order'|linkLang:[id => $order->id, order_url => $order->url]}">{'Заказ'|trans}
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
												src="{if $purchase->product->image->filename}{$purchase->product->image->filename|resize:60:60:c}{else}{'images/cargo.png'|asset}{/if}" />
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

				{include file='parts/pagination.tpl'}
			</div>
		</div>
	</div>
{/block}