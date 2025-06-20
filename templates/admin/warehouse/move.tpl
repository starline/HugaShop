{extends 'wrapper/main.tpl'}
{include 'warehouse/parts/menu_part.tpl'}

{if $movement->id}
	{$meta_title = "Поставка №`$movement->id`"}
{else}
	{$meta_title = "Новая поставка"}
{/if}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">{if $message_error == 'error_closing'}Нехватка товара на
				складе{else}{$message_error}
				{/if}</span>
		</div>
	{/if}

	<form method="post" id="order" enctype="multipart/form-data" class="two_columns_order">
		<input name="id" type="hidden" value="{$movement->id}" />
		{getCSRFInput}

		<div class="header_top">
			<h1>
				{if $movement->id}Перемещение
					<span class="copy_field" data-bs-toggle="tooltip" title="Скопировать"
						value="{$movement->id}">{$movement->id}
						<div class="copy_hover">
							<i class="material-icons">content_copy</i>
						</div>
					</span>
				{else}
					Новое перемещение товара
				{/if}

				<select class="form-select form-select-lg status" name="status" {if !$can_edit}disabled{/if}>
					<option value="0" {if $movement->status == 0}selected{/if}>Новое</option>
					{if $movement->status != 3 AND $movement->status != 4}
						<option value="1" {if $movement->status == 1}selected{/if}>Ожидаем</option>
						<option value="2" {if $movement->status == 2}selected{/if}>Поступило</option>
					{/if}
					{if $movement->status != 1 AND $movement->status != 2}
						<option value="3" {if $movement->status == 3}selected{/if}>Списано</option>
					{/if}
					<option value="4" {if $movement->status == 4}selected{/if}>Отмена</option>
				</select>
			</h1>

			{if !$movement->id|empty}
				<a class="print_icon fl_r" href="{url type=print}" target="_blank">
					<img loading="lazy" src="{'images/printer.png'|asset}" data-bs-toggle="tooltip" title="Печать поставки">
				</a>
			{/if}
		</div>


		<!-- Детали поставки -->
		<div id="order_details">
			<h2>Детали поставки</h2>

			<div class="order_date_time">
				{if !$movement->date|empty}
					<div>
						Создан {$movement->date|date} в {$movement->date|time} <span class="fl_r">Изменён
							{$movement->modified|date} в {$movement->modified|time}</span>
					</div>
				{/if}

				{if !$movement->manager|empty}
					<div class="order_manager">
						Последняя правка: <a href="/admin/user/{$movement->manager->id}">{$movement->manager->name}</a>
					</div>
				{/if}

				<ul>
					<li class="awaiting_date">
						<label class="col-form-label" for="awaiting_date">Дата
							{if $movement->status == 2 || $movement->status == 1}поставки{elseif $movement->status == 3}списания{elseif $movement->status == 4}отмены{else}перемещения{/if}</label>
						<input class="form-control" id="awaiting_date" type="text" name="awaiting_date"
							value="{$movement->awaiting_date|date}" {if !$can_edit}disabled{/if} />
					</li>

					<li class="wh_place">
						<label class="col-form-label" for="place_id">Склад</label>
						<select class="form-select" name="place_id" id="place_id" {if !$can_edit}disabled{/if}>
							{foreach $warehouse_places as $place}
								<option class="" value="{$place->id}" {if $movement->place_id == $place->id}selected{/if}>
									{$place->name}</option>
							{/foreach}
						</select>
					</li>
				</ul>
			</div>


			<!-- Примечания -->
			<div class="note_wrap layer">
				<h2>Примечание
					{if $can_edit}
						<i class="edit_note material-icons" data-bs-toggle="tooltip" title="Изменить">edit</i>
					{/if}
				</h2>

				<ul class="note_block">
					<li>
						<div class="edit_note" style="display:none;">
							<textarea class="form-control" name="note">{$movement->note}</textarea>
						</div>

						<div class="view_note" {if !$movement->note}style="display:none;" {/if}>
							<div class="note_text">{$movement->note|strip_tags|nl2br|raw}</div>
						</div>
					</li>
				</ul>
			</div>


			{if 'warehouse_edit'|user_access}
				<div class="note_wrap layer">
					<h2>Примечаниe логиста
						<i class="edit_note material-icons" data-bs-toggle="tooltip" title="Изменить">edit</i>
					</h2>

					<ul class="note_block">
						<li>
							<div class="edit_note" style="display:none;">
								<textarea class="form-control" name="note_logist">{$movement->note_logist}</textarea>
							</div>

							<div class="view_note" {if !$movement->note_logist}style="display:none;" {/if}>
								<div class="note_text">{$movement->note_logist|strip_tags|nl2br|raw}</div>
							</div>
						</li>
					</ul>
				</div>
			{/if}


			<!-- Параметры партии -->
			{if $total->weight > 0}
				<div class="layer">
					<h2>Параметры партии</h2>

					<div class="order_details_row total_wholesale_price">
						Общий вес: <b>{$total->weight} <span class="currency">{$settings->weight_units}</span></b>
					</div>
					{if 'finance'|user_access}
						<div class="order_details_row total_wholesale_price">
							Закупка: <b>{$total->cost_price|price_html|raw}</b>
						</div>
						<div class="order_details_row total_retail_price">
							Продажи: <b>{$total->retail_price|price_html|raw} </b>
						</div>
						<div class="order_details_row">
							Прибыль: <b>{($total->retail_price - $total->cost_price)|price_html:profit|raw}</b>
						</div>
					{/if}
				</div>
			{/if}


			{if 'finance'|user_access}
				<div class="layer">
					<h2>Финансы</h2>

					{if $movement->payments}
						<div class="order_details_row total_wholesale_price">Всего:
							<b>{$total->payments_price|price_html:profit|raw}</b>
						</div>
					{/if}

					<div class="btn_row">
						<a class="btn btn-light"
							href="{'PaymentNewAdmin'|urll}?cur_type=0&contractor_entity_name=wh_movement&contractor_entity_id={$movement->id}">Добавить
							платеж</a>
					</div>

					{if $movement->payments}
						<div class="list mt-4">
							{foreach $movement->payments as $p}
								<div class="list_row {if !$p->verified}verified_off{else}verified_on{/if}" item_id="{$p->id}">
									<div class="payment_amount {if $p->related_payment_id}transfer{/if}">
										<a
											href="{'PaymentAdmin'|urll:[id => $p->id]}">{$p->amount|price_html:profit:$p->currency_code|raw}</a>

										{if $p->currency_rate != 1}
											<div class="notice">{$p->currency_amount|price_html|raw}</div>
										{/if}

										<div class="order_date">
											<div class="date">{$p->date|date}</div>
											<div class="time">{$p->date|time}</div>
										</div>
									</div>
									<div class="col">
										{if $p->category->name}
											{$p->category->name}
										{else}
											Премещение между кошельками
										{/if} <div class="notice">{$p->comment}</div>
									</div>
								</div>
							{/foreach}
						</div>
					{/if}

				</div>
			{/if}

		</div>


		<!-- Список поставки -->
		<div id="purchases">
			<div class="list purchases">
			
				{foreach $movement->purchases as $purchase}
					<div class="list_row">
						<input type="hidden" name="purchases[{$purchase->position}][product_id]"
							value="{$purchase->product_id}" />
						<input type="hidden" name="purchases[{$purchase->position}][id]" value="{$purchase->id}" />

						<div class="move">
							<div class="move_zone"></div>
						</div>

						<div class="image">
							<img class="product_icon"
								src="{if $purchase->product->image->filename}{$purchase->product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}"
								data-bs-toggle="tooltip" title="{$purchase->product->variant_name}" />
						</div>

						<div class="col row">
							<div class="col">
								<a href="/admin/product/{$purchase->product->id}/price">{$purchase->product_name}</a>
								<span class="variant_name">{$purchase->variant_name}</span>
							</div>

							<div class="col-3 text-end">
								{if $purchase->sku}
									<div class="sku">
										<div class="round_box copy_field" value="{$purchase->sku}">
											<span>{$purchase->sku}</span>
											<div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
												<i class="material-icons">content_copy</i>
											</div>
										</div>
									</div>
								{/if}
							</div>

							<div class="col-2 text-end">
								<div class="view_edit_purchase price">
									<div class="row_alert">
										<span class="js_change">{$purchase->price|number:2}</span>
										<span class="price_sign">{$currency->sign}</span>
									</div>
									{if 'product_price'|user_access}
										<div class="view_purchase">
											{$purchase->cost_price|price_html|raw}
										</div>
									{/if}
									<div class="edit_purchase input-group input-group-sm" style="display:none;">
										<input class="form-control text-end" type="text"
											name="purchases[{$purchase->position}][cost_price]" value="{$purchase->cost_price}"
											size="5" />
										<span class="input-group-text">{$currency->sign}</span>
									</div>
								</div>
							</div>

							<div class="col-2 text-end">
								<div class="view_edit_purchase amount">
									{if $purchase->product->weight}
										<div class="row_alert">
											<span
												class="js_change">{($purchase->product->weight * $purchase->amount)|number:2}</span>
											<span class="price_sign">{$settings->weight_units}</span>
										</div>
									{/if}

									<div class="view_purchase">
										{$purchase->amount} {$settings->units}
									</div>

									<div class="edit_purchase" style="display:none;">
										<select class="form-select form-select-sm text-end"
											name="purchases[{$purchase->position}][amount]">
											{section name=amounts start=1 loop=$settings->max_order_amount step=1}
												<option value="{$smarty.section.amounts.index}"
													{if $purchase->amount == $smarty.section.amounts.index}selected{/if}>
													{$smarty.section.amounts.index} {$settings->units}</option>
											{/section}
										</select>
									</div>
								</div>
								<div class="view_edit_purchase text-end stock">
									{if $purchase->product}
										<div class="row_alert alert_down">
											{if $purchase->product->movements_amount}
												<div class="wmovements" data-bs-toggle="tooltip" data-bs-html="true"
													title="{foreach $purchase->product->movements as $mov}<div class='text-nowrap'>Поставка №{$mov->move_id} | {$mov->awaiting_date|date} | +{$mov->amount}</div>{/foreach}">
													+{$purchase->product->movements_amount}
												</div>
											{/if}
										</div>
										<div class="variant_stock">остаток: <span
												class="js_change">{$purchase->product->stock}</span>
										</div>
									{/if}
								</div>
							</div>

						</div>

						<div class="icons flex-column">
							{if $purchase->product->id}
								<a href="{'ProductMarkingAdmin'|urll:[product_id => $purchase->product->id]}" target="_blank"
									class="material-icons" data-bs-toggle="tooltip" title="Распечать этикету">print</a>
							{/if}
							{if $can_edit}
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							{/if}
						</div>
					</div>
				{/foreach}


				<!-- New purchase row -->
				<div id="new_purchase" class="list_row sort_disabled" style="display:none;">
					<input type="hidden" name="purchases[INDEX][product_id]" value="">
					<input type="hidden" name="purchases[INDEX][id]" value="">

					<div class="move">
						<div class="move_zone"></div>
					</div>

					<div class="image">
						<img class="product_icon" src="">
					</div>

					<div class="col row">
						<div class="col">
							<a class="add_name" href=""></a>
							<span class="variant_name"></span>
						</div>

						<div class="col-3 text-end">
							<div class="sku">
								<div class="round_box copy_field" value="">
									<span></span>
									<div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
										<i class="material-icons">content_copy</i>
									</div>
								</div>
							</div>
						</div>

						<div class="col-2 text-end ">
							<div class="view_edit_purchase price">
								<div class="row_alert">
									<span class="js_change"></span>
									<span class="price_sign">{$currency->sign}</span>
								</div>
								<div class="edit_purchase input-group input-group-sm">
									<input class="form-control text-end" type="text" name="purchases[INDEX][cost_price]"
										value="" size="5" />
									<span class="input-group-text">{$currency->sign}</span>
								</div>
							</div>
						</div>

						<div class="col-2 text-end">
							<div class="view_edit_purchase amount">
								<div class="row_alert">
									<span class="js_change"></span>
									<span class="price_sign">{$settings->weight_units}</span>
								</div>
								<select class="form-select form-select-sm text-end" name="purchases[INDEX][amount]">
									{section name=amounts start=1 loop=$settings->max_order_amount step=1}
										<option value="{$smarty.section.amounts.index}">{$smarty.section.amounts.index}
											{$settings->units}</option>
									{/section}
								</select>
							</div>
							<div class="view_edit_purchase text-end stock">
								<div class="row_alert alert_down"></div>
								<div class="variant_stock">остаток: <span class="js_change"></span></div>
							</div>
						</div>
					</div>

					<div class="icons flex-column">
						<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
					</div>
				</div>
			</div>

			<div id="add_purchase" {if $movement->purchases}style="display:none;" {/if}>
				<input type="text" id="add_purchase" class="input_autocomplete form-control"
					placeholder="Выберите товар чтобы добавить его">
			</div>

			{if $movement->purchases and $can_edit}
				<div class="edit_purchases dash_link">редактировать покупки</div>
			{/if}


			<!-- Изображения -->
			{if $movement->images || $can_edit}
				<div id="images" class="col-12 layer images">
					<h2>Фотоотчет</h2>
					<ul>
						{foreach $movement->images as $image}
							<li>
								{if $can_edit}
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
								{/if}

								<a href="{$image->filename|resize:1080:1080}" class="zoom" data-fancybox="images"
									data-caption="{$payment->comment}">
									<img loading="lazy" src="{$image->filename|resize:220:220}" />
								</a>
								<input type="hidden" name="images[]" value="{$image->id}" />
							</li>
						{/foreach}
					</ul>

					{if $can_edit}
						<div class="dropZone">
							<input type="file" name="dropped_images[]" multiple class="dropInput" />
							<div class="dropMessage">Перетащите файлы сюда</div>
						</div>

						<div class="add_image"></div>
					{/if}
				</div>
			{/if}

			{if $can_edit}
				<div class="col-12 btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			{/if}

		</div>
	</form>
{/block}


{block name=body_script append}

	{include file='parts/images_upload_init.tpl'}

	<link href="{'js/fancybox/jquery.fancybox.min.css'|asset}" rel="stylesheet" />

	<script type="module">
		import "{'js/jquery/datepicker/jquery.ui.datepicker-ru.js'|asset}";
		import "{'js/fancybox/jquery.fancybox.min.js'|asset}";
		import { initFancybox } from '{"js/common.js"|asset}';

		const currency = '{$currency->sign}';
		const max_order_amount = '{$settings->max_order_amount}';
		const units = '{$settings->units}';

		{literal}

			// On document load 
			$(function() {

				// Image Zoom init
				initFancybox();

				// Выбор даты
				$('input[name="awaiting_date"]').datepicker({
					regional: 'ru'
				});

				// Сортировка вариантов
				$("#purchases").sortable({
					items: '.list_row',
					handle: ".move_zone",
					tolerance: "pointer",
					opacity: 0.95,
					axis: 'y',
					update: function(event, ui) {
						indexListRows('#purchases');
					}
				});

				// Удаление товара
				$(".purchases").on('click', 'i.delete', function() {
					$(this).closest(".list_row").fadeOut(200, function() { $(this).remove(); });
					indexListRows('#purchases');
					return false;
				});

				// После завершения сортировки переиндексировать input-ы
				function indexListRows(container_selector) {
					$(container_selector).find('.list_row').each(function(idx) {
						$(this).find('input, select, textarea').each(function() {
							this.name = this.name.replace(/purchases\[(?:\d+|INDEX)\]/,
								'purchases[' + idx + ']');
						});
					});
					console.log('index row');
				}

				// Редактировать покупки
				$(".edit_purchases").click(function() {
					$(this).hide();
					$(".purchases div.view_purchase").hide();
					$(".purchases div.edit_purchase").show();
					$("div#add_purchase").show();
					return false;
				});

				// Добавление товара. Клонируем срочку товара.
				const new_purchase = $('.purchases #new_purchase').clone(true);
				$('.purchases #new_purchase').remove().removeAttr('id');

				$("input#add_purchase").autocomplete({
					serviceUrl: '/admin/ajax/search/product',
					minChars: 0,
					noCache: false,
					onSelect: function(suggestion) {
						let new_item = new_purchase.clone().appendTo('.purchases');
						let product = suggestion.data;

						new_item.removeAttr('id');
						new_item.find('a.add_name').html(product.name);
						new_item.find('a.add_name').attr('href', '/admin/product/' + product.id +
							'/price');

						new_item.find('input[name*=product_id]').val(product.id);
						new_item.find('.price .js_change').text(product.price);
						new_item.find('input[name*=cost_price]').val(product.cost_price);

						if (product.variant_name) {
							new_item.find('.view_purchase i').text(product.variant_name);
						} else {
							new_item.find('.view_purchase i').remove();
						}

						if (product.sku) {
							new_item.find('.sku .copy_field').attr('value', product.sku);
							new_item.find('.sku .copy_field span').text(product.sku);
						} else {
							new_item.find('.sku .copy_field').remove();
						}

						new_item.find('.stock .js_change').text(product.stock);
						new_item.find('.amount .js_change ').text(product.weight);

						const amount_select_el = new_item.find('select[name*=amount]');
						for (let i = 1; i <= max_order_amount; i++)
							amount_select_el.append("<option value='" + i + "'>" + i + " " + units +
								"</option>");

						if (product.image.url)
							new_item.find('img.product_icon').attr("src", product.image.url);
						else
							new_item.find('img.product_icon').remove();

						$("input#add_purchase").val('').focus().blur();

						indexListRows('#purchases');
						new_item.show();

					},
					formatResult: function(suggestion, currentValue) {
						let reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
						let pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
						let stock_txt = ' - <span class="color_grey">нет в наличии</span>';
						let movement = '';

						let product = suggestion.data;

						if (product.stock > 0)
							stock_txt = ' - <span class="color_green">остаток ' + product.stock + ' ' +
							units + '</span>';

						if (product.movements_amount > 0)
							movement = ' <span class="color_grey">(+' + product.movements_amount + ')</span>'

						return (product.image ? "<img align='absmiddle' src='" + product
								.image.url +
								"'> " : '') + suggestion.value.replace(new RegExp(pattern, 'gi'),
								'<strong>$1<\/strong>') + ' - ' + '<span class="color_red"><b>' + suggestion
							.data.price + currency + '</b><span>' + stock_txt + movement;
					}
				});

			});

		{/literal}
	</script>

{/block}