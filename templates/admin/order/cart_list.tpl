{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{$meta_title='Корзины'}


{block name=content}
	<div class="two_columns_list">

		<div class="header_top">
			<h1>{if $carts_count}{$carts_count}{else}Нет{/if} корзин{$carts_count|plural:'a':'':'ы'}</h1>

			<a class="add" href="/admin/order">Добавить заказ</a>
		</div>

		<div id="right_menu">
		</div>


		<!-- Список заказов -->
		<div id="main_list">
			{if $carts}


				<div class="grafic">
					<div class="chart_actions btn_row">
						<a class="btn btn-light" id="cart_chart_reset">Reset zoom</a>
					</div>
					<div>
						<canvas id="cartsHistory" height="250" role="img"></canvas>
					</div>
				</div>

				{include file='parts/pagination.tpl'}

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list">
						{foreach $carts as $cart}
							<div class="list_row {if $cart->order->paid}highlight{/if}" item_id="{$cart->id}">

								{if 'order_edit'|user_access}
									<div class="checkbox">
										<input class="form-check-input" type="checkbox" name="check[]" value="{$cart->id}" />
									</div>
								{/if}

								<div class="col row">
									<div class="col-12 col-md-6">
										<span class="order_id me-3">Корзина #<span>{$cart->id}</span></span>

										{if $cart->order_id}
											<span>
												→ <a class="order_id m-3" href="{'OrderAdmin'|urll:[id => $cart->order_id]}">Заказ
													#<span>{$cart->order_id}</span></a>
											</span>
										{/if}

										<div class="my-2">
											{if !$cart->user->id|empty}
												<a href="{'UserAdmin'|urll:[id => $cart->user->id]}"><b>{$cart->user->name}</b></a>
											{/if}
										</div>

										{if !$cart->purchases|empty}
											<div class="purchases">
												{foreach $cart->purchases as $purchase}
													<div class="image {if $purchase->disabled}disabled{/if}">
														<div class="amount">{$purchase->amount}</div>
														<img loading="lazy" data-bs-toggle="tooltip" title="{$purchase->product->name}"
															src="{if $purchase->product->image->filename}{$purchase->product->image->filename|resize:60:60}{else}{'images/cargo.png'|asset}{/if}" />
													</div>
												{/foreach}
											</div>
										{/if}
									</div>

									<div class="col-12 col-md-6 mt-3 mt-md-0">
										<div class="order_price">
											{$cart->total_price|price_html|raw}

											{if $cart->order->status == 3}
												<span class="order_decline rounded">отменен</span>
											{/if}

											{if $cart->order->paid}
												<span class="order_paid rounded">оплачен</span>
											{/if}


											{if 'order_finance'|user_access}
												<span class="profit_price">{$cart->profit_price|price_html:profit|raw}</span>
											{/if}
										</div>

										{include file='order/parts/user_agent_part.tpl'}
									</div>
								</div>

								{if 'order_delete'|user_access}
									<div class="icons">
										<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
									</div>
								{/if}
							</div>
						{/foreach}
					</div>


					{if 'order_edit'|user_access}
						<div id="action">
							<span id="check_all" class="dash_link">Выбрать все</span>
							<span id="select">
								<select class="form-select" name="action">
									<option value="">Выбрать действие</option>
									{if 'order_delete'|user_access}
										<option value="delete">
											Удалить
										</option>
									{/if}
								</select>
							</span>
							<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
						</div>
					{/if}

				</form>

				{include file='parts/pagination.tpl'}

			{/if}
		</div>

	</div>
{/block}


{block name=body_script append}
	<script type="text/javascript" src="{'js/chart/chart.umd.js'|asset}"></script>
	<script type="text/javascript" src="{'js/chart/luxon.js'|asset}"></script>
	<script type="text/javascript" src="{'js/chart/chartjs-adapter-luxon.js'|asset}"></script>
	<script type="text/javascript" src="{'js/chart/chartjs-plugin-datalabels.js'|asset}"></script>
	<script type="text/javascript" src="{'js/chart/hammerjs.js'|asset}"></script>
	<script type="text/javascript" src="{'js/chart/chartjs-plugin-zoom.min.js'|asset}"></script>

	<script type="module">
		var csrf = "{setCSRF}";

		let cartsChart = new Chart(document.getElementById('cartsHistory'), {
			type: 'line',
			options: {
				locale: 'ru',
				maintainAspectRatio: false,
				plugins: {
					datalabels: {
						color: 'black',
						formatter: function(value) { return value.y; },
						align: 'top',
						anchor: 'end',
						display: 'auto',
						font: { weight: 'bold' }
					},
					zoom: {
						pan: { enabled: true, mode: 'x', modifierKey: 'ctrl' },
						zoom: { drag: { enabled: true }, mode: 'x' }
					},
					tooltip: { yAlign: 'bottom' }
				},
				scales: {
					x: { type: 'time', time: { unit: 'day', tooltipFormat: 'dd LLL yyyy' } },
					y: { display: true, title: { display: true, text: 'шт' } }
				}
			},
			plugins: [ChartDataLabels]
		});

		function loadCartStats() {
			$.post('/admin/ajax/stats/cart', { csrf: csrf }, function(data) {
				if (data && data.length > 0) {
					let carts = [],
						ordered = [],
						paid = [];
					data.forEach((p) => {
						let dt = luxon.DateTime.fromISO(p.date);
						carts.push({ x: dt, y: parseInt(p.carts) });
						ordered.push({ x: dt, y: parseInt(p.ordered) });
						paid.push({ x: dt, y: parseInt(p.paid) });
					});

					cartsChart.data.datasets.push({
						label: 'Корзин',
						data: carts,
						borderColor: '#76c100',
						backgroundColor: '#76c100',
						fill: false,
						tension: 0
					});
					cartsChart.data.datasets.push({
						label: 'Оформлено в заказ',
						data: ordered,
						borderColor: '#f8a13f',
						backgroundColor: '#f8a13f',
						fill: false,
						tension: 0
					});
					cartsChart.data.datasets.push({
						label: 'Оплачено',
						data: paid,
						borderColor: '#000000',
						backgroundColor: '#000000',
						fill: false,
						tension: 0
					});

					cartsChart.update();
				}
			});
		}

		loadCartStats();

		$('#cart_chart_reset').click(function() {
			cartsChart.resetZoom();
		});
	</script>
{/block}