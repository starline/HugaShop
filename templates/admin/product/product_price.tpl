{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}
{include 'product/parts/submenu_part.tpl'}

{if $product->id}
	{$meta_title = $product->name}
{/if}

{block name=content}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$product->id}" />
		{getCSRFInput}

		<div class="row g-6">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check">
							<input class="form-check-input" name="visible" value="1" type="checkbox" id="active_checkbox"
								{if $product->visible}checked{/if} />
							<label class="form-check-label" for="active_checkbox">Показывать в каталоге</label>
						</div>

						<div class="form-check">
							<input class="form-check-input" name="disable" value="1" type="checkbox" id="disable_checkbox"
								{if $product->disable}checked{/if} />
							<label class="form-check-label" for="disable_checkbox">Не поставляется</label>
						</div>

						<div class="form-check">
							<input class="form-check-input" name="featured" value="1" type="checkbox" id="featured_checkbox"
								{if $product->featured}checked{/if} />
							<label class="form-check-label" for="featured_checkbox" data-bs-toggle="tooltip"
								title="Товар выводиться на главное странице">Рекомендуемый</label>
						</div>

						<div class="form-check">
							<input class="form-check-input" name="sale" value="1" type="checkbox" id="sale_checkbox"
								{if $product->sale}checked{/if} />
							<label class="form-check-label" for="sale_checkbox">Распродажа</label>
						</div>
					</div>
					<div class="link_line">
						{if $product->category_id}
							<a class='out_link' href="/admin/products?category_id={$product->category_id}">Перейти к товарам
								категории в админке</a>
						{/if}
						{if $product->id}
							<a class="out_link" target="_self" href="{$settings->site_url}/product/{$product->id}">Открыть товар
								на сайте</a>
						{/if}
					</div>
				</div>
				<div class="name_row">
					<input class="form-control form-control-lg" name="name" type="text" value="{$product->name}"
						autocomplete="off" disabled />
				</div>
			</div>


			<!-- Варианты товара -->
			<div id="variants_block">
				<ul>
					<li class="variant_name_info">
						<div class="variant_name">
							<input class="form-control" name="variant_name" type="text" value="{$product->variant_name}" />
						</div>
					</li>

					<li class="variant_sku_info">
						<div class="variant_sku">
							<div class="input_disabled"></div>
							<input class="form-control text-center" name="sku" type="text" value="{$product->sku}"
								disabled />
						</div>
						<div class="marking_print">
							<a class="print_icon" href="{'ProductMarkingAdmin'|urll:[product_id => $product->id]}"
								target="_blank">
								<img src="{'images/printer.png'|asset}" data-bs-toggle="tooltip" title="Печать маркировки">
							</a>
						</div>
					</li>

					<li class="variant_price_info">
						<div class="variant_old">
							<label for="old_price">Старая цена</label>
							<input class="form-control form-control-sm" id="old_price" name="old_price" type="text"
								value="{$product->old_price}" />
						</div>

						{if $variant->old_price > 0 AND ($variant->old_price - $variant->price) > 0}
							<div class="discount">
								<span data-bs-toggle="tooltip" title="Скидка">-{$product->old_price - $product->price}
									{$currency->sign}
									{(($product->old_price - $product->price) / $product->price * 100)|ceil}%</span>
							</div>
						{/if}

						<div class="variant_price">
							<label for="price">Розница</label>
							<input class="form-control" id="price" name="price" type="text" value="{$product->price}" />
						</div>

						{if $product->cost_price > 0}
							<div class="profit">
								<span data-bs-toggle="tooltip" title="Наценка">+{$product->price - $product->cost_price}
									{$currency->sign}
									{(($product->price - $product->cost_price) / $product->cost_price * 100)|ceil}%</span>
							</div>
						{/if}

						<div class="variant_discount">
							<label for="cost_price">Оптовая цена</label>
							<input class="form-control form-control-sm" id="cost_price" name="cost_price" type="text"
								value="{$product->cost_price}" />
						</div>
					</li>

					<li class="variant_amount_info">
						<div class="variant_amount">
							<div class="input_disabled"></div>
							<input class="form-control form-control-sm" name="stock" type="text"
								value="{if $product->stock|is_null}∞{elseif !$product->stock}0{else}{$product->stock}{/if}"
								disabled />
							<span>{$settings->units}</span>
						</div>
						<div class="variant_weight">
							<div class="input_disabled"></div>
							<input class="form-control" name="weight" type="text" value="{$product->weight}" disabled />
							<span>{$settings->weight_units}/{$settings->units}</span>
						</div>
					</li>

					<li class="variant_provider_info">

						<div class="variant_awaiting_date">
							<label for="awaiting_date">Дата поставки</label>
							<input class="form-control form-control-sm" id="awaiting_date" type="text" name="awaiting_date"
								value="{$product->awaiting_date|date}" />
						</div>

						<div class="variant_custom form-check">
							<label class="form-check-label" for="awaiting">Выводить ожидаем</label>
							<input class="form-check-input" id="awaiting" type="checkbox" name="awaiting" value="1"
								{if $product->awaiting}checked{/if} data-bs-toggle="tooltip" title="Выводить ожидаем" />
						</div>

						<div class="variant_custom form-check">
							<label class="form-check-label" for="custom">Выводить под заказ</label>
							<input class="form-check-input" id="custom" type="checkbox" name="custom" value="1"
								{if $product->custom}checked{/if} data-bs-toggle="tooltip" title="Выводить под заказ" />
						</div>

					</li>
				</ul>

				<div class="col-12 btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>


			<!-- Статистика продажи товара-->
			{if ('stats'|user_access and $product->id)}
				<div class="col-12 layer product_stats">
					<div class="chart_actions btn_row">
						<a class="btn btn-light" id="product_stats_reset">Reset zoom</a>
					</div>
					<div id="product_stats"></div>
				</div>

				<div class="col-12 layer product_price_history">
					<div class="chart_actions btn_row">
						<a class="btn btn-light" id="productPriceHistory_reset">Reset zoom</a>
					</div>
					<div id="productPriceHistory" style="height: 250px;"></div>
				</div>
			{/if}


			<!-- Варианты товара --->
			<div class="col-lg-6 layer">
				<h2>
					Варианты товара
					<span class="sum_total">{$product_variants|count}
						{$product_variants|count|plural:'товар':'товаров':'товара'}</span>
				</h2>
				<div class="list sortable product_variants">
					{foreach $product_variants as $product_variant}
						<div
							class="list_row {if !$product_variant->product->visible}visible_off{/if} {if $product_variant->product->disable}disable{/if}">
							<input type="hidden" name="product_variants[]" value="{$product_variant->product_id}">

							<div class="col row gy-5">
								<div class="col-12 col-md-9">
									<div class="row">
										<div class="move">
											<div class="move_zone"></div>
										</div>
										<div class="image">
											<img class="product_icon"
												src="{if $product_variant->product->image->filename}{$product_variant->product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}">
										</div>
										<div class="col">
											<a class="product_name"
												href="{'ProductAdmin'|urll:[id => $product_variant->product_id]}?return={$smarty.server.REQUEST_URI|urlencode}">{$product_variant->product->name}</a>
											<div class="variant_name">{$product_variant->product->variant_name}</div>
										</div>
									</div>
								</div>

								<div class="col-12 col-md-3">
									<div class="col sku text-end">
										<div class="round_box copy_field" value="{$product_variant->product->sku}">
											<span>{$product_variant->product->sku}</span>
											<div class="copy_hover" data-bs-toggle="tooltip"
												data-bs-original-title="Скопировать">
												<i class="material-icons">content_copy</i>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="icons">
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						</div>
					{/foreach}

					<div id="new_product_variant" class="list_row" style="display:none;">
						<input type="hidden" name="product_variants[]" value="">

						<div class="col row gy-5">
							<div class="col-12 col-md-9">
								<div class="row">
									<div class="move">
										<div class="move_zone"></div>
									</div>
									<div class="image">
										<img class="product_icon" src="">
									</div>
									<div class="col">
										<a class="product_name" href=""></a>
										<div class="variant_name"></div>
									</div>
								</div>
							</div>

							<div class="col-12 col-md-3">
								<div class="col sku text-end">
									<div class="round_box copy_field" value="">
										<span></span>
										<div class="copy_hover" data-bs-toggle="tooltip"
											data-bs-original-title="Скопировать">
											<i class="material-icons">content_copy</i>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="icons">
							<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
						</div>
					</div>
				</div>

				<input type="text" id="product_variants" class="input_autocomplete form-control"
					placeholder='Выберите товар чтобы добавить его'>

				<div class="btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>


			<!-- Связанные товары --->
			<div class="col-lg-6 layer">
				<h2>
					Связанные товары
					<span class="sum_total">{$product->related|count}
						{$product->related|count|plural:'товар':'товаров':'товара'}</span>
				</h2>
				<div class="list sortable related_products">
					{foreach $product->related as $rel_product}
						<div
							class="list_row {if !$rel_product->visible}visible_off{/if} {if $rel_product->disable}disable{/if}">
							<input type="hidden" name="related_products[]" value="{$rel_product->id}">
							<div class="move">
								<div class="move_zone"></div>
							</div>

							<div class="image">
								<img class="product_icon"
									src="{if $rel_product->image->filename}{$rel_product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}">
							</div>

							<div class="col">
								<a class="related_product_name"
									href="{'ProductAdmin'|urll:[id => $rel_product->id]}?return={$smarty.server.REQUEST_URI|escape}">{$rel_product->name}</a>
							</div>

							<div class="icons">
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						</div>
					{/foreach}

					<div id="new_related_product" class="list_row" style="display:none;">
						<div class="move">
							<input type="hidden" name="related_products[]" value="">
							<div class="move_zone"></div>
						</div>
						<div class="image">
							<img class="product_icon" src="">
						</div>
						<div class="col">
							<a class="related_product_name" href=""></a>
						</div>
						<div class="icons">
							<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
						</div>
					</div>
				</div>

				<input type="text" id="related_products" class="input_autocomplete form-control"
					placeholder='Выберите товар чтобы добавить его'>

				<div class="btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>
		</div>
	</form>


	<!-- Заказы с товаром -->
	<div class="row mt-5">
		<div class="col-12 layer">
			<div class="header_top mt-3">
				<h1>
					{if $orders_count > 0}{$orders_count}{else}Нет{/if} заказ{$orders_count|plural:'':'ов':'а'}
					{if 'finance'|user_access AND $orders_paid_price->sum_total_price}
						<span class="sum_total">оплаченых на сумму: {$orders_paid_price->sum_total_price|price_html|raw}
							<span class="sum_profit_price">{$orders_paid_price->sum_profit_price|price_html:profit|raw}</span>
						</span>
					{/if}
				</h1>
				<form class="export_btn" method="post" action="/admin/product_orders/export?product_id={$product->id}"
					target="_blank">
					<input type="image" src="{'images/export_excel.png'|asset}" name="export" data-bs-toggle="tooltip"
						title="Экспортировать заказы с товаром" />
				</form>
			</div>
		</div>

		<div class="col-12">

			{include file='parts/pagination.tpl'}

			<div class="list">
				{foreach $orders as $order}
					{include file='order/parts/order_item_part.tpl'}
				{/foreach}
			</div>

			{include file='parts/pagination.tpl'}

		</div>
	</div>
{/block}


{block name=body_script append}

	{include file='parts/charts_init.tpl'}

	<script type="text/javascript" src="{'js/chart/luxon.js'|asset}"></script>
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

	<script type="module">
		import '{"js/jquery/datepicker/jquery.ui.datepicker-ru.js"|asset}';
		import { ajax_icon, makeChart } from '{"js/common.js"|asset}';

		var csrf = "{setCSRF}";
		let php_product_id = '{$product->id}';
		let php_currency_name = '{$currency->name}';
		let php_currency_sign = '{$currency->sign}';

		{literal}
			$(function() {

				// Выбор даты
				$("input[name*=awaiting_date]").datepicker({
					regional: 'ru'
				});


				// Сортировка товаров
				$('.sortable').sortable({
					items: '.list_row',
					handle: '.move_zone',
					tolerance: "pointer",
					opacity: 0.95,
					axis: 'y'
				});


				// После завершения сортировки переиндексировать input-ы
				function indexListRows(container_selector, name_prefix) {
					const regex = new RegExp(name_prefix + '\\[(?:\\d+|INDEX)\\]');
					$(container_selector).find('.list_row').each(function(idx) {
						$(this).find('input, select, textarea').each(function() {
							this.name = this.name.replace(regex, name_prefix + '[' + idx + ']');
						});
					});
				}


				// Варианты товара
				let new_product_variant = $('#new_product_variant').clone(true).removeAttr('id');
				$('#new_product_variant').remove();
				$("input#product_variants").autocomplete({
					serviceUrl: '/admin/ajax/search/product',
					minChars: 0,
					noCache: false,
					onSelect: function(suggestion) {
						$(this).val('').focus().blur();
						let new_item = new_product_variant.clone().appendTo('.product_variants');
						let product = suggestion.data;

						new_item.find('a.product_name').html(product.name);
						new_item.find('a.product_name')
							.attr('href', '/admin/product/' + product.id);

						new_item.find('input[name*=product_variants]').val(product.id);

						if (product.variant_name) {
							new_item.find('.variant_name').text(product.variant_name);
						} else {
							new_item.find('.variant_name').remove();
						}

						if (product.sku) {
							new_item.find('.sku .copy_field').attr('value', product.sku);
							new_item.find('.sku .copy_field span').text(product.sku);
						} else {
							new_item.find('.sku .copy_field').remove();
						}

						if (product.image)
							new_item.find('img.product_icon').attr("src", product.image.url);
						else
							new_item.find('img.product_icon').remove();

						if (product.disable == 1)
							new_item.addClass("disable");

						if (product.visible == 0)
							new_item.addClass("visible_off");

						new_item.show();
					},
					formatResult: function(suggestions, currentValue) {
						let reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
						let pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
						return (suggestions.data.image ? "<img align=absmiddle src='" + suggestions.data
							.image.url + "'> " : '') + suggestions.value.replace(new RegExp(pattern, 'gi'),
							'<strong>$1<\/strong>');
					}
				});


				// Добавление связанного товара 
				let new_related_product = $('#new_related_product').clone(true).removeAttr('id');
				$('#new_related_product').remove();
				$("input#related_products").autocomplete({
					serviceUrl: '/admin/ajax/search/product',
					minChars: 0,
					noCache: false,
					onSelect: function(suggestion) {
						$(this).val('').focus().blur();
						let new_item = new_related_product.clone().appendTo('.related_products');
						let product = suggestion.data;

						new_item.find('a.related_product_name').html(product.name);
						new_item.find('a.related_product_name')
							.attr('href', '/admin/product/' + product.id);
						new_item.find('input[name*="related_products"]').val(product.id);

						if (product.image)
							new_item.find('img.product_icon').attr("src", product.image.url);
						else
							new_item.find('img.product_icon').remove();

						if (product.disable == 1)
							new_item.addClass("disable");

						if (product.visible == 0)
							new_item.addClass("visible_off");

						new_item.show();
					},
					formatResult: function(suggestions, currentValue) {
						let reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
						let pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
						return (suggestions.data.image ? "<img align=absmiddle src='" + suggestions.data
							.image.url + "'> " : '') + suggestions.value.replace(new RegExp(pattern, 'gi'),
							'<strong>$1<\/strong>');
					}
				});

				// Удаление товара
				$(".related_products, .product_variants").on('click', 'i.delete', function() {
					$(this).closest("div.list_row").fadeOut(200, function() {
						$(this).remove();
					});
					return false;
				});


				// Бесконечность на складе
				$("input[name*=variants][name*=stock]").focus(function() {
					if ($(this).val() == '∞')
						$(this).val('');
					return false;
				});

				$("input[name*=variants][name*=stock]").blur(function() {
					if ($(this).val() == '')
						$(this).val('∞');
				});


				// Редактирование колонки input
				$("#variants_block").on('dblclick', 'div.input_disabled', function() {
					let select_column = $(this).parent().attr('class');

					// Открываем input всей колонки
					$("." + select_column).find('input').prop('disabled', false);
					$(this).parent().find('input').focus();
					$("." + select_column).find('div.input_disabled').remove();
					return false;
				});


				// Выводим график
				let statsChart = makeChart(
					document.getElementById('product_stats'), {
						chart: { type: 'bar', height: 350 },
						title: { text: 'Статистика продаж' }
					},
					[{
							filter: {
								product_id: php_product_id,
								filter: 'byMonth',
								csrf: csrf
							},
							options: {
								label: 'Сумма заказов, ' + php_currency_sign,
								color: '#76c100',
								type: 'totalPrice',
								url: '/admin/ajax/stats/order'
							}
						},
						{
							filter: {
								product_id: php_product_id,
								filter: 'byMonth',
								csrf: csrf
							},
							options: {
								label: 'Сумма прибыли, ' + php_currency_sign,
								color: '#f8a13f',
								type: 'profitPrice',
								url: '/admin/ajax/stats/order'
							}
						},
						{
							filter: {
								product_id: php_product_id,
								filter: 'byMonth',
								csrf: csrf
							},
							options: {
								label: 'Продано, шт',
								color: '#000000',
								type: 'amount',
								url: '/admin/ajax/stats/order'
							}
						},
						{
							filter: {
								product_id: php_product_id,
								filter: 'byMonth',
								csrf: csrf
							},
							options: {
								label: 'Поставка, шт',
								color: '#673ab7',
								type: 'add',
								url: '/admin/ajax/stats/order'
							}
						},
						{
							filter: {
								product_id: php_product_id,
								filter: 'byMonth',
								csrf: csrf
							},
							options: {
								label: 'Списано, шт',
								color: '#f00',
								type: 'delete',
								url: '/admin/ajax/stats/order'
							}
						}
					]
				);

				let priceChart = makeChart(
					document.getElementById('productPriceHistory'), {
						chart: { type: 'line', height: 350, zoom: { enabled: true } },
						tooltip: { x: { format: 'dd LLL yyyy' } },
						title: { text: 'История цен' }
					},
					[{
							filter: {
								filter: 'byDay',
								product_id: php_product_id,
								csrf: csrf
							},
							options: {
								label: 'Цена, ' + php_currency_sign,
								color: '#76c100',
								type: 'price',
								url: '/admin/ajax/stats/product-price'
							}
						},
						{
							filter: {
								filter: 'byDay',
								product_id: php_product_id,
								csrf: csrf
							},
							options: {
								label: 'Оптовая цена, ' + php_currency_sign,
								color: '#f8a13f',
								type: 'costPrice',
								url: '/admin/ajax/stats/product-price'
							}
						}
					]
				);

				$('#productPriceHistory_reset').click(function() {
					if (priceChart.chart) priceChart.chart.resetSeries();
				});

				$('#product_stats_reset').click(function() {
					if (statsChart.chart) statsChart.chart.resetSeries();
				});
			});
		{/literal}
	</script>

{/block}