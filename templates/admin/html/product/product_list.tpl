{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{* Meta Title *}
{if $category}
	{$meta_title=$category->name}
{else}
	{$meta_title='Товары'}
{/if}

{block name=content}

	<div class="two_columns_list">

		<!-- Заголовок -->
		<div class="header_top">

			{if $products_count}
				{if $category->name || $brand->name}
					<h1>{$category->name} {$brand->name}<span class="sum_total">{$products_count}
							{$products_count|plural:'товар':'товаров':'товара'}</span>
					</h1>
				{elseif $keyword}
					<h1>{$products_count|plural:'Найден':'Найдено':'Найдено'}
						<span class="sum_total">{$products_count}
							{$products_count|plural:'товар':'товаров':'товара'}</span>
					</h1>
				{else}
					<h1>Все товары<span class="sum_total">{$products_count}
							{$products_count|plural:'товар':'товаров':'товара'}</span></h1>
				{/if}
			{else}
				<h1>Нет товаров</h1>
			{/if}

			{if 'product_content'|user_access}
				<a class="add" href="{'ProductNewAdmin'|link}">Добавить товар</a>
			{/if}

			<div class="btns_wrap">
				{if 'product_import'|user_access AND $products_count > 0}
					<a class="export_btn" href="{'ProductExport'|link}?{$smarty.server.QUERY_STRING}">
						<img src="{'images/export_excel.png'|asset}" name="export" data-bs-toggle="tooltip"
							title="Экспортировать товары" />
					</a>

					<a class="export_btn" href="{'ProductsImportPAdmin'|link}">
						<img src="{'images/import_excel.png'|asset}" name="import" data-bs-toggle="tooltip"
							title="Импортировать товары" />
					</a>
				{/if}
			</div>

			<!-- Search -->
			<form method="get" id="search">
				<div class="input-group">
					<input class="search form-control" type="text" name="keyword" value="{$keyword}"
						placeholder="Название, артикул" />
					<input class="input-group-text search_button" type="submit" value="" />
				</div>
			</form>
		</div>


		<!-- Меню -->
		<div class="navbar-expand-lg" id="right_menu">

			<div class="popup_menu_btn navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#filter_menu_block">
				<span class="material-icons">menu</span>
				<span class="popup_btn_text">Фильтр</span>
			</div>

			<div class="offcanvas offcanvas-start" id="filter_menu_block" tabindex="-1" aria-labelledby="offcanvasLabel">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="offcanvasLabel"></h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>

				<div class="offcanvas-body">

					<!-- Фильтры -->
					<select class="form-select js-url-select" name="filter">
						<option value="" {if !$filter}selected{/if}>Все товары</option>
						<option value="sale" {if $filter == 'sale'}selected{/if}>Акция</option>
						<option value="featured" {if $filter == 'featured'}selected{/if}>Рекомендуемые</option>
						<option value="discounted" {if $filter == 'discounted'}selected{/if}>Со скидкой</option>
						<option value="visible" {if $filter == 'visible'}selected{/if}>Активные</option>
						<option value="hidden" {if $filter == 'hidden'}selected{/if}>Неактивные</option>
						<option value="outofstock" {if $filter == 'outofstock'}selected{/if}>Нет в наличии</option>
						<option value="instock" {if $filter == 'instock'}selected{/if}>В наличии</option>
					</select>

					{if $brands}
						<select class="form-select js-url-select" name="brand_id" data-clear="keyword">
							<option value="" {if !$brand->id}selected{/if}>Все бренды</option>
							{foreach $brands as $b}
								<option value="{$b->id}" {if $brand->id == $b->id}selected{/if}>{$b->name}</option>
							{/foreach}
						</select>
					{/if}

					<!-- Категории товаров -->
					{include file='parts/categories_tree_part.tpl'}

				</div>

			</div>
		</div>


		<!--  Основная форма -->
		<div id="main_list">
			{if $category->id}
				<a class="out_link" target="_self" href="{'Products'|linkLang:[url => $category->url]}">Открыть категорию на
					сайте</a>
			{/if}

			{if $products}
				{if 'stats'|user_access AND $category->id}
					<!-- Статистика продажи товара-->
					<div class="product_stats">
						<div id="product_stats" style="height: 250px;"></div>
					</div>
				{/if}

				{include file='parts/pagination.tpl'}

				<!-- Список товаров -->
				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list sortable_on">
						{foreach $products as $product}
							<div class="list_row {if !$product->visible}visible_off{/if} {if $product->disable}disable{/if} {if !$product->featured}featured_off{/if} {if !$product->sale}sale_off{/if}"
								item_id="{$product->id}">

								{if 'product_price'|user_access AND !$product->order_date AND !$product->never_ordered AND !$product->profit AND !$product->need}
									<div class="move">
										<div class="move_zone"></div>
										<input type="hidden" name="positions[{$product->id}]" value="{$product->position}">
										<input type="hidden" name="variant" value="{$product->id}" />
									</div>
								{/if}

								{if 'product_price'|user_access}
									<div class="checkbox">
										<input class="form-check-input" type="checkbox" name="check[]" value="{$product->id}" />
									</div>
								{/if}


								<div class="col row">
									<div class="col_image">
										<img
											src="{if $product->image->filename}{$product->image->filename|resize:60:60:c}{else}{'images/cargo.png'|asset}{/if}" />
									</div>

									<div class="col col_name">

										{if 'product_content'|user_access}
											<a
												href="{'ProductAdmin'|link:[id=>$product->id]}?return={$smarty.server.REQUEST_URI}">{$product->name}</a>
										{else}
											{$product->name}
										{/if}

										{if $product->variant_name}
											<i class="small"> - {$product->variant_name}</i>
										{/if}


										<div class="icons flex-row mt-2">
											{if 'product_price'|user_access}
												<a class="show_chart" data-bs-toggle="tooltip" title="Показать график продаж"></a>
											{/if}

											<a class="featured {if 'product_price'|user_access}edit{/if}" data-bs-toggle="tooltip"
												title="Рекомендуемый"></a>
											<a class="sale {if 'product_price'|user_access}edit{/if}" data-bs-toggle="tooltip"
												title="Акция"></a>
											<i class="enable {if 'product_price'|user_access}edit{/if} material-icons visibility"
												data-bs-toggle="tooltip" title="Активен" title="Активен"></i>
											{if 'product_price'|user_access}
												<i class="duplicate material-icons library_add" data-bs-toggle="tooltip"
													title="Дублировать"></i>
											{/if}
											<a class="material-icons launch" data-bs-toggle="tooltip"
												title="Предпросмотр в новом окне" href="{'Product'|linkLang:[url => $product->url]}"
												target="_blank"></a>
										</div>
									</div>

									<div class="col-12 col-md-4 variants">
										<div class="row">
											<div class="col-4 text-end">
												{if $product->sku}
													<div class="badge text-bg-round copy_field" value="{$product->sku}">{$product->sku}
														<div class="copy_hover" data-bs-toggle="tooltip"
															data-bs-original-title="Скопировать">
															<i class="material-icons">content_copy</i>
														</div>
													</div>
												{/if}
											</div>

											<span class="col-4 price">
												{if 'product_price'|user_access}
													<a data-bs-toggle="tooltip" data-bs-html="true" {if $product->cost_price > 0}
															title="Оптовая цена &mdash; {$product->cost_price|number} {$currency->sign}</br>Доход &mdash; {$product->profit_price|number} {$currency->sign}</br> Старая цена  &mdash; {$product->old_price|number} {$currency->sign}"
														{/if}
														href="{'ProductPriceAdmin'|link:[id => $product->id]}?return={$smarty.server.REQUEST_URI}">{$product->price|price_html|raw}</a>
												{else}
													{$product->price|price_html|raw}
												{/if}
											</span>

											<span class="col-4">
												<div class="stock">
													{if $product->stock|is_null}
														∞
													{else}
														{$product->stock} {$settings->units}
													{/if}

													{if $product->movements_amount}
														<span class="wmovements" data-bs-toggle="tooltip" data-bs-html="true"
															title="{foreach $product->movements as $mov}<div class='text-nowrap'>Поставка №{$mov->move_id} | {$mov->awaiting_date|date} | +{$mov->amount}</div>{/foreach}">+{$product->movements_amount}</span>
													{/if}
												</div>
											</span>
										</div>
									</div>
								</div>

							</div>
						{/foreach}
					</div>


					{if [product_delete, product_price]|user_access}
						<div id="action">
							<span id="check_all" class="dash_link">Выбрать все</span>

							<span id="select">
								<select class="form-select" name="action">
									<option value="">Выбрать действие</option>
									<option value="enable">Сделать видимыми</option>
									<option value="disable">Сделать невидимыми</option>
									<option value="set_featured">Сделать рекомендуемым</option>
									<option value="unset_featured">Отменить рекомендуемый</option>
									<option value="set_sale">Сделать Акцию</option>
									<option value="unset_sale">Отменить Акцию</option>
									<option value="duplicate">Создать дубликат</option>
									{if $pagination->pages_count > 1}
										<option value="move_to_page">Переместить на страницу</option>
									{/if}
									{if 'product_delete'|user_access}
										<option value="delete">Удалить</option>
									{/if}
								</select>
							</span>

							<span id="move_to_page">
								<select class="form-select" name="target_page">
									{section target_page $pagination->pages_count}
										<option value="{$smarty.section.target_page.index+1}">{$smarty.section.target_page.index+1}
										</option>
									{/section}
								</select>
							</span>
							{include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
						</div>
					{/if}

				</form>

				{include file='parts/pagination.tpl'}
			{/if}
		</div>
	</div>
{/block}


{block name=body_script append}

	<script type="text/javascript" src="{'js/luxon.js'|asset}"></script>
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

	<script type="module">
		import { ajaxEntityUpdateIcon } from '{"js/common.js"|asset}';
		import { makeChart } from '{"js/chart.js"|asset}';

		const php_category_id = '{$category->id}';
		const php_currency_name = '{$currency->name}';
		const php_currency_sign = '{$currency->sign}';


		{literal}
			$(function() {

				let current_url = new URL(window.location.href);

				// Select filter and brand
				$('select.js-url-select').change(function() {
					const param = $(this).attr('name');
					const selected = $(this).val();
					if (selected !== '')
						current_url.searchParams.set(param, selected);
					else
						current_url.searchParams.delete(param);

					const clears = $(this).data('clear');
					if (clears)
						clears.split(',').forEach(p => current_url.searchParams.delete(p));

					current_url.searchParams.delete('page');
					window.location.href = current_url.toString();
				});

				// Перенос товара на другую страницу
				$("#action select[name=action]").change(function() {
					if ($(this).val() == 'move_to_page')
						$("span#move_to_page").css('display', 'inline-block');
					else
						$("span#move_to_page").hide();
				});

				$(".pagination a.droppable").droppable({
					tolerance: "pointer",
					drop: function(event, ui) {
						$(ui.helper).find('input[type="checkbox"][name*="check"]').prop('checked', true);
						$(ui.draggable).closest("form.list_form").find(
							'select[name="action"] option[value=move_to_page]').prop("selected",
							"selected");
						$(ui.draggable).closest("form.list_form").find(
							'select[name=target_page] option[value=' + $(this)
							.html() + ']').prop("selected", "selected");
						$(ui.draggable).closest("form.list_form").submit();
						return false;
					}
				});

				// Дублировать товар
				$("i.duplicate").click(function() {
					$('.list input[type="checkbox"][name*="check"]').prop('checked', false);
					$(this).closest("div.list_row").find('input[type="checkbox"][name*="check"]').prop('checked',
						true);
					$(this).closest("form.list_form").find('select[name="action"] option[value=duplicate]').prop(
						'selected',
						true);
					$(this).closest("form.list_form").submit();
				});


				// Скрыт/Видим
				$("i.enable.edit").click(function() {
					ajaxEntityUpdateIcon($(this), 'product', 'visible');
					return false;
				});

				// Сделать хитом
				$("a.featured.edit").click(function() {
					ajaxEntityUpdateIcon($(this), 'product', 'featured');
					return false;
				});

				// Сделать акционным
				$("a.sale.edit").click(function() {
					ajaxEntityUpdateIcon($(this), 'product', 'sale');
					return false;
				});


				// Статистика продаж
				$('.list .list_row .show_chart').click(function() {
					let row = $(this).closest('.list_row');
					let id = row.attr('item_id');
					let icon = $(this);

					if (!$("div").is('#chart_' + id)) {
						icon.addClass('loading_icon');
						row.after("<div id='chart_" + id + "'></div>");

						let chartData = makeChart(
							document.getElementById('chart_' + id), {
								chart: { type: 'bar', height: 250 },
								title: { text: 'Статистика продаж' }
							},
							[{
									filter: {
										product_id: id,
										filter: 'byMonth',
										type: 'totalPrice'
									},
									options: {
										label: 'Сумма заказов, ' + php_currency_sign,
										color: '#76c100',
										url: '/admin/ajax/stats/order'
									}
								},
								{
									filter: {
										product_id: id,
										filter: 'byMonth',
										type: 'profitPrice'
									},
									options: {
										label: 'Сумма прибыли, ' + php_currency_sign,
										color: '#f8a13f',
										url: '/admin/ajax/stats/order'
									}
								},
								{
									filter: {
										product_id: id,
										filter: 'byMonth',
										type: 'amount'
									},
									options: {
										label: 'Продано, шт',
										color: '#000000',
										url: '/admin/ajax/stats/order'
									}
								}
							]
						);

						chartData.ready.then(function() {
							$("#chart_" + id).css("height", "200px");
							icon.removeClass('loading_icon');
						});
					} else {
						$('#chart_' + id).remove();
					}
				});

				makeChart(
					document.getElementById('product_stats'), {
						chart: { type: 'bar', height: 250 },
						title: { text: 'Статистика продаж' }
					},
					[{
							filter: {
								category_id: php_category_id,
								filter: 'byMonth',
								type: 'totalPrice'
							},
							options: {
								label: 'Сумма заказов, ' + php_currency_sign,
								color: '#76c100',
								url: '/admin/ajax/stats/order'
							}
						},
						{
							filter: {
								category_id: php_category_id,
								filter: 'byMonth',
								type: 'profitPrice'
							},
							options: {
								label: 'Сумма прибыли, ' + php_currency_sign,
								color: '#f8a13f',
								url: '/admin/ajax/stats/order'
							}
						},
						{
							filter: {
								category_id: php_category_id,
								filter: 'byMonth',
								type: 'amount'
							},
							options: {
								label: 'Продано, шт',
								color: '#000000',
								url: '/admin/ajax/stats/order'
							}
						}
					]
				);
			});
		{/literal}
	</script>
{/block}