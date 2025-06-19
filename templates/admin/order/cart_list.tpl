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
						<a class="btn btn-light" id="cart_chart_year">год</a>
					</div>
					<div>
						<div id="cartsHistory" style="height: 250px;"></div>
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

	<script type="text/javascript" src="{'js/chart/luxon.js'|asset}"></script>
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

	<script type="module">
		var csrf = "{setCSRF}";

		import { makeChart, hideOverlappingDataLabels, getChartData } from '{"js/common.js"|asset}';

		let cartsData = makeChart(
			document.getElementById('cartsHistory'), {
				chart: {
					type: 'bar',
					height: 350,
					events: {
						mounted: hideOverlappingDataLabels,
						updated: hideOverlappingDataLabels
					}
				},
				xaxis: { type: 'datetime' },
				plotOptions: { bar: { dataLabels: { position: 'top' } } },
				tooltip: { x: { format: 'dd LLL yyyy' } },
				title: { text: 'Корзины по дням' }
			}
		);

		cartsData.ready.then(function() {
			loadCartStats('month');
		});

		function loadCartStats(range = 'month') {
			let params = { csrf: csrf, filter: 'byDay' };

			cartsData.series = [];
			if (cartsData.chart) cartsData.chart.updateSeries([]);

			const baseOpt = { url: '/admin/ajax/stats/cart' };
			getChartData(cartsData, Object.assign({}, params), Object.assign({}, baseOpt, {
				label: 'Корзин',
				color: '#76c100',
				type: 'carts',
				range: range
			}));
			getChartData(cartsData, Object.assign({}, params), Object.assign({}, baseOpt, {
				label: 'Оформлено в заказ',
				color: '#f8a13f',
				type: 'ordered',
				range: range
			}));
			getChartData(cartsData, Object.assign({}, params), Object.assign({}, baseOpt, {
				label: 'Оплачено',
				color: '#000000',
				type: 'paid',
				range: range
			}));
		}

		$('#cart_chart_reset').click(function() {
			if (cartsData.chart) cartsData.chart.resetSeries();
		});

		$('#cart_chart_year').click(function() {
			loadCartStats('year');
		});
	</script>
{/block}