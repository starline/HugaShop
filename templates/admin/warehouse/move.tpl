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

					{if !$payments|empty}
						<div class="order_details_row total_wholesale_price">Всего:
							<b>{$total->payments_price|price_html:profit|raw}</b>
						</div>
					{/if}

					<div class="btn_row">
						<a class="btn btn-light"
							href="{'PaymentNewAdmin'|urll}?cur_type=0&contractor_entity_name=wh_movement&contractor_entity_id={$movement->id}">Добавить
							платеж</a>
					</div>

					{if !$payments|empty}
						<div class="list mt-4">
							{foreach $payments as $p}
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
									<div class="name">
										{if !$p->category_name|empty}
											{$p->category_name}
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

						<div class="move">
							<div class="move_zone"></div>
						</div>

						<div class="image">
							<input type="hidden" name="purchases[id][]" value="{$purchase->id}" />
							<img class="product_icon"
								src="{if $purchase->product->image->filename}{$purchase->product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}"
								data-bs-toggle="tooltip" title="{$purchase->product->variant_name}" />
						</div>

						<div class="col">
							<a href="/admin/product/{$purchase->product->id}/price">{$purchase->product->name}</a>
						</div>

						<div class="purchase_variant">
							<div class="view_purchase">
								<i data-bs-toggle="tooltip"
									title="{$purchase->variant_name}">{$purchase->variant_name|truncate:20:'…':true:false}</i>

								{if $purchase->sku}
									<div class="round_box copy_field" value="{$purchase->sku}">{$purchase->sku}
										<div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
											<i class="material-icons">content_copy</i>
										</div>
									</div>
								{/if}
							</div>
						</div>

						{if 'product_price'|user_access}
							<div class="price">
								<div class="view_edit_purchase">
									<div class="row_alert">
										<span class="js_change">{$purchase->price|number:2}</span>
										<span class="price_sign">{$currency->sign}</span>
									</div>

									<div>
										<div class="view_purchase">
											{$purchase->cost_price|price_html|raw}
										</div>
									</div>

									<div class="edit_purchase input-group input-group-sm" style="display:none;">
										<input class="form-control text-end" type="text" name="purchases[cost_price][]"
											value="{$purchase->cost_price}" size="5" />
										<span class="input-group-text">{$currency->sign}</span>
									</div>
								</div>
							</div>
						{/if}

						<div class="amount">
							<div class="view_edit_purchase">
								<div class="row_alert">
									<span class="js_change">{($purchase->product->weight * $purchase->amount)|number}</span>
									<span class="price_sign">{$settings->weight_units}</span>
								</div>

								<div class="view_purchase">
									{$purchase->amount} {$settings->units}
								</div>

								<div class="edit_purchase" style="display:none;">
									<select class="form-select form-select-sm" name="purchases[amount][]">
										{section name=amounts start=1 loop=$settings->max_order_amount step=1}
											<option value="{$smarty.section.amounts.index}"
												{if $purchase->amount==$smarty.section.amounts.index}selected{/if}>
												{$smarty.section.amounts.index} {$settings->units}</option>
										{/section}
									</select>
								</div>
							</div>
						</div>

						<div class="stock">
							<div class="view_edit_purchase">
								<div class="row_alert">
									{if $purchase->product->income_move}
										<div class="wmovements" data-bs-toggle="tooltip" data-bs-html="true"
											title="{foreach $purchase->product->warehouse_move as $mov}Поставка №{$mov->move_id} | {$mov->awaiting_date|date} | +{$mov->amount}</br>{/foreach}">
											+{$purchase->product->income_move}</div>
									{/if}
								</div>

								{if $purchase->product}
									<div class="variant_stock">остаток: <span class="js_change">{$purchase->product->stock}</span>
									</div>
								{else}
									{if $purchase->product->id|empty}
										<img src="{'images/error.png'|asset}" data-bs-toggle="tooltip" title="Товар был удалён">
									{elseif $purchase->variant->id|empty}
										<img src="{'images/error.png'|asset}" data-bs-toggle="tooltip"
											title="Вариант товара был удалён">
									{/if}
								{/if}
							</div>
						</div>

						{if 'product_marking'|user_access}
							<div class="icons">
								<a href="{'ProductMarkingAdmin'|urll:[variant_id => $purchase->product->id]}" target="_blank"
									class="material-icons" data-bs-toggle="tooltip" title="Распечать этикету">print</a>

								{if $can_edit}
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
								{/if}
							</div>
						{/if}
					</div>
				{/foreach}


				<div id="new_purchase" class="list_row sort_disabled" style="display:none;">
					<div class="move">
						<div class="move_zone"></div>
					</div>

					<div class="image">
						<input type="hidden" name="purchases[id][]" value="">
						<img class="product_icon" src="">
					</div>

					<div class="col">
						<a class="add_name" href=""></a>
					</div>

					<div class="purchase_variant">
						<div class="view_purchase">
							<i data-bs-toggle="tooltip" title=""></i>
							<div class="round_box copy_field" value="{$purchase->sku}">
								<div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
									<i class="material-icons">content_copy</i>
								</div>
							</div>
						</div>
					</div>

					<div class="price">
						<div class="view_edit_purchase">
							{if 'product_price'|user_access}
								<div class="row_alert">
									<span class="js_change"></span>
									<span class="price_sign">{$currency->sign}</span>
								</div>
							{/if}
							<div class="edit_purchase input-group input-group-sm">
								<input class="form-control text-end" type="text" name="purchases[cost_price][]" value=""
									size="5" {if !'product_price'|user_access} disabled {/if} />
								<span class="input-group-text">{$currency->sign}</span>
							</div>
						</div>
					</div>

					<div class="amount">
						<div class="view_edit_purchase">
							<div class="row_alert">
								<span class="js_change"></span>
								<span class="price_sign">{$settings->weight_units}</span>
							</div>
							<select class="form-select form-select-sm" name="purchases[amount][]"></select>
						</div>
					</div>

					<div class="stock">
						<div class="view_edit_purchase">
							<div class="row_alert"></div>
							<div class="variant_stock">остаток: <span class="js_change"></span></div>
						</div>
					</div>

					<div class="icons">
						<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
					</div>
				</div>

			</div>

			<div id="add_purchase" {if !$purchases|empty}style="display:none;" {/if}>
				<input type="text" id="add_purchase" class="input_autocomplete form-control"
					placeholder="Выберите товар чтобы добавить его">
			</div>

			{if $purchases and $can_edit}
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

						<span class="upload_image">
							<span class="dash_link">Добавить изображение</span>
						</span>
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
					items: '.list_row:not(.sort_disabled)',
					handle: ".move_zone",
					cancel: ".sort_disabled",
					tolerance: "pointer",
					opacity: 0.95,
					axis: 'y',
					update: function(event, ui) {
						$("#purchases input[name*='check']").prop('checked', false);
					}
				});


				// Удаление товара
				$(".purchases").on('click', 'i.delete', function() {
					$(this).closest(".list_row").fadeOut(200, function() { $(this).remove(); });
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
						const new_item = new_purchase.clone().appendTo('.purchases');
						new_item.removeAttr('id');
						new_item.find('a.add_name').html(suggestion.data.name);
						new_item.find('a.add_name').attr('href', '/admin/product/' + suggestion.data.id +
							'/price');

						new_item.find('.price .js_change').text(suggestion.data.price);
						new_item.find('input[name*=purchases\\[cost_price\\]]').val(suggestion.data
							.cost_price);

						if (suggestion.data.variant_name) {
							new_item.find('.view_purchase i').text(suggestion.data.variant_name);
						} else {
							new_item.find('.view_purchase i').remove();
						}

						if (suggestion.data.sku) {
							new_item.find('.view_purchase .copy_field').attr('value', suggestion.data.sku);
							new_item.find('.view_purchase .copy_field').text(suggestion.data.sku);
						} else {
							new_item.find('.view_purchase .copy_field').remove();
						}

						new_item.find('.stock .js_change').text(suggestion.data.stock);
						new_item.find('.amount .js_change ').text(suggestion.data.weight);

						const amount_select_el = new_item.find('select[name*=purchases][name*=amount]');
						for (let i = 1; i <= max_order_amount; i++)
							amount_select_el.append("<option value='" + i + "'>" + i + " " + units +
								"</option>");

						if (suggestion.data.image.url)
							new_item.find('img.product_icon').attr("src", suggestion.data.image.url);
						else
							new_item.find('img.product_icon').remove();

						$("input#add_purchase").val('').focus().blur();
						new_item.show();
					},
					formatResult: function(suggestion, currentValue) {
						let reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
						let pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
						let stock_txt = ' - <span class="color_grey">нет в наличии</span>';
						let movement = '';

						let stock_count = suggestion.data.stock;
						let movement_count = suggestion.data.movements_amount;

						if (stock_count > 0)
							stock_txt = ' - <span class="color_green">остаток ' + suggestion.data.stock + ' ' +
							units + '</span>';

						if (movement_count > 0)
							movement = ' <span class="color_grey">(+' + movement_count + ')</span>'

						return (suggestion.data.image ? "<img align='absmiddle' src='" + suggestion.data
								.image.url +
								"'> " : '') + suggestion.value.replace(new RegExp(pattern, 'gi'),
								'<strong>$1<\/strong>') + ' - ' + '<span class="color_red"><b>' + suggestion
							.data.price + currency + '</b><span>' + stock_txt + movement;
					}
				});


				// Редактировать покупки
				$(".edit_purchases").click(function() {
					$(".purchases div.view_purchase").hide();
					$(".purchases div.edit_purchase").show();
					$(this).hide();
					$("div#add_purchase").show();
					return false;
				});

			});

		{/literal}
	</script>

{/block}