{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{if $order->id}
	{$meta_title = "Заказ №`$order->id`"}
{else}
	{$meta_title = 'Новый заказ'}
{/if}


{block name=content}
	<form method="post" id="order" enctype="multipart/form-data" class="two_columns_order">
		{getCSRFInput}

		<div class="header_top">
			<input name="id" type="hidden" value="{$order->id}" />
			<input name="manager_id" type="hidden" value="{$order->manager_id}" />

			<h1>Заказ

				<span class="copy_field" value="{$order->id}">{$order->id}
					<div class="copy_hover" data-bs-toggle="tooltip" title="Скопировать">
						<i class="material-icons">content_copy</i>
					</div>
				</span>

				<select class="form-select form-select-lg status" class="status" name="status" {if !$can_edit}disabled{/if}>
					<option value="0" {if $order->status == 0}selected{/if}>Новый</option>
					<option value="1" {if $order->status == 1}selected{/if}>Принят</option>
					<option value="4" {if $order->status == 4}selected{/if}>Отгружен</option>
					<option value="2" {if $order->status == 2}selected{/if} {if !'order_edit'|user_access}disabled{/if}>
						Выполнен</option>
					<option value="3" {if $order->status == 3}selected{/if}>Отмена</option>
				</select>

				{if !$prev_order->id|empty}
					<a class="out_link" href="{'OrderAdmin'|urll:[id => $prev_order->id]}" data-bs-toggle="tooltip"
						title="№{$prev_order->id}">Перейти к
						следуюшему заказу</a>
				{/if}
			</h1>

			{if !$order->id|empty}
				<a class="print_icon" href="{'Order'|urll:[id => $order->id, order_url => $order->url, type => print]}"
					target="_blank">
					<img src="{'images/printer.png'|asset}" data-bs-toggle="tooltip" title="Печать заказа">
				</a>
			{/if}

		</div>


		<!-- Детали заказа -->
		<div id="order_details">
			<h2>Детали заказа
				{if $can_edit}
					<i class="edit_order_details material-icons" data-bs-toggle="tooltip" title="Редактировать">edit</i>
				{/if}
			</h2>

			<div class="order_date_time">

				{if !$order_manager->id|empty}
					<div class="order_manager">
						Менеджер: <a href="/admin/user/{$order_manager->id}" target="_blank">{$order_manager->name}</a>
						{if $order_manager->interest_price and ($user->id == $order_manager->id OR 'order_finance'|user_access)}
							<span class="manager_profit">
								<span data-bs-toggle="tooltip" title="Комиссия менеджера с продажи">
									{$order_manager->interest_price|price_html:profit|raw}
								</span>
								{if $order_manager->interest_discount > 0}
									<span class="profit_dicount" data-bs-toggle="tooltip" title="% за заказ">
										{$order_manager->interest_discount|number:1}%
									</span>
								{/if}
							</span>
						{/if}
					</div>
				{/if}

				{if !$order->date|empty}
					<div>
						Создан {$order->date|date} в {$order->date|time} <span class="fl_r">Изменён {$order->modified|date} в
							{$order->modified|time}</span>
					</div>
				{/if}
			</div>

			<div id="user">
				<ul>
					<li>
						<label class="col-form-label" for="id">Имя</label>
						<div class="edit_order_detail" style="display:none;">
							<input class="form-control" name="name" id="id" type="text" autocomplete="off"
								value="{$order->name}" />
						</div>
						<div class="view_order_detail">
							{$order->name}
						</div>
					</li>
					<li>
						<label class="col-form-label" for="email">Email</label>
						<div class="edit_order_detail" style="display:none;">
							<input class="form-control" name="email" id="email" type="email" autocomplete="off"
								value="{$order->email}" />
						</div>
						<div class="view_order_detail">
							<a href="mailto:{$order->email}?subject=Заказ%20№{$order->id}">{$order->email}</a>
						</div>
					</li>
					<li>
						<label class="col-form-label" for="phone">Телефон</label>
						<div class="edit_order_detail" style="display:none;">
							<input class="form-control" name="phone" id="phone" type="text" autocomplete="off"
								value="{$order->phone}" />
						</div>
						<div class="view_order_detail">
							{if $order->phone}
								<a class="ip_call" data-phone="{$order->phone}" target="_blank"
									href="tel:{$order->phone}">{$order->phone}</a>
							{else}
								{$order->phone}
							{/if}
						</div>
					</li>
					<li>
						<label class="col-form-label" for="address">Город</label>
						<div class="edit_order_detail" style='display:none;'>
							<input class="form-control" name="address" id="address" type="text" autocomplete="off"
								value="{$order->address}" />
						</div>
						<div class="view_order_detail">
							{$order->address}
						</div>
					</li>
					<li>
						<label class="col-form-label" for=comment>Комментарий пользователя</label>
						<div class="edit_order_detail edit_comment" style='display:none;'>
							<textarea class="form-control" name="comment" id=comment>{$order->comment}</textarea>
						</div>
						<div class="view_order_detail edit_comment">
							{$order->comment|strip_tags|nl2br|raw}
						</div>
					</li>
				</ul>
			</div>

			<div class="note_wrap mt-4">
				<h2>Примечание
					{if $can_edit}
						<i class="edit_note material-icons" data-bs-toggle="tooltip" title="Редактировать">edit</i>
					{/if}
				</h2>
				<ul class="note_block">
					<li>
						<div class="edit_note" style="display:none;">
							<div class="col-form-label">Ваше примечание (не видно клиенту)</div>
							<textarea class="form-control" name="note">{$order->note}</textarea>
						</div>
						<div class="view_note" {if !$order->note}style="display:none;" {/if}>
							<div class="col-form-label">Ваше примечание (не видно клиенту)</div>
							<div class="note_text">{$order->note|strip_tags|nl2br|raw}</div>
						</div>
					</li>
				</ul>
			</div>


			{if $labels}
				<div class="layer">
					<h2>Метка</h2>
					<ul class="menu_list">
						{foreach $labels as $l}
							{if ($l->enabled OR 'order_edit'|user_access)}
								<li class="label {if !$l->enabled}disabled{/if}">
									<label for="label_{$l->id}" style="background-color:#{$l->color};"
										class="form-check {if !$l->enabled}disabled{/if}">
										<input class="form-check-input" id="label_{$l->id}" type="checkbox" name="order_labels[]"
											value="{$l->id}" {if in_array($l->id, $order->label_ids)}checked{/if}
											{if !$can_edit}disabled{/if} />
										<span class="form-check-label">{$l->name}</span>
									</label>
								</li>
							{/if}
						{/foreach}
					</ul>
				</div>
			{/if}


			<div class="layer mt-2">
				<h2>Покупатель
					{if $can_edit}
						<i class="edit_user material-icons" data-bs-toggle="tooltip" title="Редактировать">edit</i>
						{if $order->user}
							<i class="delete_user material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
						{/if}
					{/if}
				</h2>

				<div class="view_user">
					{if !$order->user}
						Не зарегистрирован
					{else}
						<a href="/admin/user/{$order->user->id}">{$order->user->name}</a>
						{if $order->user->group->name}
							<div>{$order->user->group->name}</div>
						{/if}
					{/if}
				</div>

				<div class="edit_user" style="display:none;">
					<input type="hidden" name="user_id" value="{$order->user->id}" />
					<input type="text" id="user_id" class="form-control input_autocomplete "
						placeholder="Выберите пользователя" />
				</div>

				<!-- Cart info -->
				{include file='order/parts/user_agent_part.tpl'}

				{extension place='admin_order_side'}
			</div>
		</div>



		<!-- Товары заказа -->
		<div id="purchases">
			<div class="list purchases overflow-x-auto">

				{foreach $order->purchases as $purchase}
					<div class="list_row">
						<input type="hidden" name="purchases[{$purchase->position}][product_id]"
							value="{$purchase->product_id}" />
						<input type="hidden" name="purchases[{$purchase->position}][id]" value="{$purchase->id}" />

						<div class="col row gy-5">
							<div class="col-12 col-md-7">
								<div class="row">
									<div class="move">
										<div class="move_zone"></div>
									</div>
									<div class="col_image image">
										<img
											src="{if $purchase->product->image->filename}{$purchase->product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}" />
									</div>
									<div class="col">
										<a class="product_name"
											href="/admin/product/{$purchase->product_id}/price">{$purchase->product_name}</a>
										<div class="variant_name">{$purchase->variant_name}</div>
									</div>
								</div>
							</div>

							<div class="col-12 col-md-5">
								<div class="row gx-2">
									<div class="col-4 text-end sku">
										{if $purchase->sku}
											<div class="badge text-bg-round copy_field" value="{$purchase->sku}">
												<span>{$purchase->sku}</span>
												<div class="copy_hover" data-bs-toggle="tooltip"
													data-bs-original-title="Скопировать">
													<i class="material-icons">content_copy</i>
												</div>
											</div>
										{/if}
									</div>

									<div class="col-4 text-end">
										<div class="view_edit_purchase price">
											<div class="row_alert">
												{if 'order_finance'|user_access}
													<span class="js_change">{$purchase->cost_price|number:2}</span>
													<span class="price_sign">{$currency->sign}</span>
												{/if}
											</div>
											{if 'product_price'|user_access}
												<div class="view_purchase">
													{$purchase->price|price_html|raw}
												</div>
											{/if}
											<div class="edit_purchase input-group input-group-sm" style="display:none;">
												<input class="form-control text-end" type="text"
													name="purchases[{$purchase->position}][price]" value="{$purchase->price}"
													size="5" maxlength="10" {if !'order_finance'|user_access}disabled{/if} />
												<span class="input-group-text">{$currency->sign}</span>
											</div>
										</div>
									</div>


									<div class="col-4 text-end">
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

												{if $purchase->product}
													{math equation="min(max(x,y),z)" x=$purchase->product->stock + $purchase->amount * ($order->closed) y=$purchase->amount z=$settings->max_order_amount assign="loop"}
												{else}
													{math equation="x" x=$purchase->amount assign="loop"}
												{/if}

												<select class="form-select form-select-sm text-end"
													name="purchases[{$purchase->position}][amount]">
													{section name=amounts start=1 loop=$loop+1 step=1}
														<option value="{$smarty.section.amounts.index}"
															{if $purchase->amount == $smarty.section.amounts.index}selected{/if}>
															{$smarty.section.amounts.index} {$settings->units}</option>
													{/section}
												</select>
											</div>
										</div>

										<div class="text-end stock view_edit_purchase">
											{if $purchase->product}
												<div class="row_alert alert_down">
													{if $purchase->product->movements_amount}
														<div class="wmovements" data-bs-toggle="tooltip" data-bs-html="true"
															title="{foreach $purchase->product->movements as $mov}<div class='text-nowrap'>Поставка №{$mov->move_id} | {$mov->awaiting_date|date} | +{$mov->amount}</div>{/foreach}">
															+{$purchase->product->movements_amount}
														</div>
													{/if}
													{if !$order->closed and $purchase->product->stock < $purchase->amount and !$purchase->product->stock|is_null}
														<img src="{'images/error.png'|asset}" data-bs-toggle="tooltip"
															title='На складе остал{$purchase->product->stock|plural:'ся':'ось'} {$purchase->product->stock} товар{$purchase->product->stock|plural:'':'ов':'а'}'>
													{/if}
												</div>

												<div class="variant_stock">остаток: <span
														class="js_change">{if $purchase->product->stock|is_null}∞{else}{$purchase->product->stock}{/if}</span>
												</div>
											{/if}
										</div>

									</div>
								</div>
							</div>
						</div>

						<div class="icons">
							{if !$order->closed}
								{if !$purchase->product}
									<img src="{'images/error.png'|asset}" alt="Товар был удалён" title="Товар был удалён">
								{elseif !$purchase->product->id}
									<img src="{'images/error.png'|asset}" alt="Товара был удалён" title="Вариант товара был удалён">
								{/if}
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

					<div class="col row gy-5">
						<div class="col-12 col-md-6">
							<div class="row">
								<div class="move">
									<div class="move_zone"></div>
								</div>
								<div class="col_image image">
									<img src="" />
								</div>
								<div class="col">
									<a class="product_name" href=""></a>
									<div class="variant_name"></div>
								</div>
							</div>
						</div>

						<div class="col-12 col-md-6">
							<div class="row gx-2">
								<div class="col-4 text-end sku">
									<div class="badge text-bg-round copy_field" value="">
										<span></span>
										<div class="copy_hover" data-bs-toggle="tooltip"
											data-bs-original-title="Скопировать">
											<i class="material-icons">content_copy</i>
										</div>
									</div>
								</div>

								<div class="col-4 text-end">
									<div class="view_edit_purchase price">
										<div class="row_alert">
											{if 'order_finance'|user_access}
												<span class="js_change"></span>
												<span class="price_sign">{$currency->sign}</span>
											{/if}
										</div>
										<div class="edit_purchase input-group input-group-sm">
											<input class="form-control text-end" type="text" name="purchases[INDEX][price]"
												value="" size="5" {if !'order_finance'|user_access} disabled {/if} />
											<span class="input-group-text">{$currency->sign}</span>
										</div>
									</div>
								</div>

								<div class="col-4 text-end">
									<div class="view_edit_purchase amount">
										<div class="row_alert">
											<span class="js_change"></span>
											<span class="price_sign">{$settings->weight_units}</span>
										</div>
										<select class="form-select form-select-sm text-end"
											name="purchases[INDEX][amount]"></select>
									</div>
									<div class="view_edit_purchase text-end stock">
										<div class="row_alert alert_down"></div>
										<div class="variant_stock">остаток: <span class="js_change"></span></div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="icons flex-column">
						<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
					</div>
				</div>

			</div>

			<div id="add_purchase" {if $order->purchases}style='display:none;' {/if}>
				<input type="text" id="add_purchase" class="form-control input_autocomplete"
					placeholder="Выберите товар чтобы добавить его" />
			</div>

			{if $order->purchases and $can_edit}
				<div class="edit_purchases dash_link">редактировать покупки</div>
			{/if}


			{if $order->purchases}
				<div class="subtotal mt-4">
					<div class="over_line">{$total->purchases_count} {$settings->units} {$total->purchases_weight}
						{$settings->weight_units}
					</div>
					<div class="main_line">
						Всего: <b>{$total->purchases_price|price_html|raw}</b>
					</div>
				</div>
			{/if}


			<!-- Скидка и купоны -->
			<div class="my-4">
				<div class="row gx-5 mt-2">
					<label class="col-form-label col-3 col-xl-2" for="discount">Скидка</label>
					<div class="col-5 col-xl-3">
						<div class="input-group">
							<input id="discount" class="form-control text-end" {if !$can_edit}disabled{/if} type="text"
								name="discount" value="{$order->discount}" autocomplete='off' />
							<span class="input-group-text currency">%</span>
						</div>
					</div>

					{if $order->discount > 0}
						<span class="col-form-label col">
							<b class="disount_amount">
								{(-($total->purchases_price * ($order->discount/100)))|price_html:profit|raw}
							</b>
						</span>
					{/if}
				</div>

				<div class="row gx-5 mt-2">
					<label class="col-form-label col-3 col-xl-2" for="coupon_discount">Купон
						{if !$order->coupon_code|empty}({$order->coupon_code}){/if}</label>
					<div class="col-5 col-xl-3">
						<div class="input-group">
							<input id="coupon_discount" class="form-control text-end" {if !$can_edit}disabled{/if}
								type="text" name="coupon_discount" value="{$order->coupon_discount}" autocomplete='off' />
							<span class="input-group-text currency">{$currency->sign}</span>
						</div>
					</div>
				</div>

				{if $order->discount > 0 || $order->coupon_discount > 0}
					<div class="subtotal">
						<div class="over_line">
							Скидка:
							{(-($total->purchases_price * ($order->discount / 100) + $order->coupon_discount))|price_html:profit|raw}
						</div>
						<div class="main_line">
							Итого: <b>
								{($total->purchases_price - $total->purchases_price * ($order->discount / 100) - $order->coupon_discount)|price_html|raw}</b>
						</div>
					</div>
				{/if}
			</div>


			<!-- Доставка -->
			<div class="delivery layer">
				<h2>Доставка</h2>
				<div class="row gx-5">
					<div class="col-xl-6">
						<select class="form-select" name="delivery_id" {if !$can_edit}disabled{/if}>
							<option value="">Не выбрана</option>
							{if !$deliveries|empty}
								{foreach $deliveries as $d}
									{if $d->enabled || $d->enabled_public || $d->id == $order->delivery_method->id}
										<option class="{if !$d->enabled_public}disabled{/if}" value="{$d->id}"
											{if !$order->delivery_method->id|empty and $d->id == $order->delivery_method->id}selected{/if}>
											{$d->name}</option>
									{/if}
								{/foreach}
							{/if}
						</select>
					</div>

					<div class="col-xl-2 col-6 my-5 my-xl-0">
						<div class="input-group">
							<input class="form-control" {if !$can_edit}disabled{/if} type="text" name="delivery_price"
								value="{$order->delivery_price}" autocomplete='off' />
							<span class="input-group-text">{$currency->sign}</span>
						</div>
					</div>

					<div class="col-xl-4 col-6 my-5 my-xl-0">
						<div class="separate_delivery form-check form-switch float-end">
							<input class="form-check-input" type="checkbox" role="switch" name="separate_delivery" value="1"
								id="separate_delivery" {if $order->separate_delivery}checked{/if}
								{if !$can_edit}disabled{/if}>
							<label class="form-check-label" for="separate_delivery">Оплачивается отдельно</label>
						</div>
					</div>
				</div>

				<!-- Модуль доставки -->
				{if !$order->delivery_method->module|empty}
					<div class="delivery_method_module">
						{get_delivery_module_html order_id=$order->id module=$order->delivery_method->module view_type='admin'}
					</div>
				{/if}
			</div>


			<!-- Оплата -->
			<div class="payment layer {if $order->paid}paid{/if}">
				<h2>Оплата</h2>

				<div class="row gx-5">
					<div class="col-lg-6">
						<select class="form-select" name="payment_method_id" {if !$can_edit}disabled{/if}>
							<option value="">Не выбрана</option>
							{if !$payment_methods|empty}
								{foreach $payment_methods as $pm}
									{if $pm->enabled || $pm->enabled_public || $pm->id == $order->payment_method->id}
										<option class="{if !$pm->enabled_public}disabled{/if}" value="{$pm->id}"
											{if !$order->payment_method->id|empty and $pm->id == $order->payment_method->id}selected{/if}>
											{$pm->name}
										</option>
									{/if}
								{/foreach}
							{/if}
						</select>
					</div>

					<div class="col-lg-6 my-5 my-lg-0">
						<div class="form-check form-switch float-end">
							<input class="form-check-input" type="checkbox" role="switch" name="paid" value="1" id="paid"
								{if $order->paid}checked{/if} {if !$can_edit}disabled{/if}>
							<label class="form-check-label" for="paid">Заказ оплачен</label>
						</div>
					</div>
				</div>

				<!-- Модуль оплаты -->
				{if !$order->payment_method->module|empty}
					<div class="payment_method_module">
						{get_payment_module_html order_id=$order->id module=$order->payment_method->module view_type='admin'}
					</div>
				{/if}
			</div>


			<div class="subtotal fs-2 my-5">

				{if 'order_finance'|user_access}
					{if $order->profit_price|isset}
						<div class="over_line">
							<span class="profit_price" data-bs-toggle="tooltip" title="Чистая прибыль">
								{$order->profit_price|price_html:profit|raw}
							</span>
						</div>
					{/if}

					{if $order->total_price > 0}
						<div class="over_line">
							<span class="percent" data-bs-toggle="tooltip" title="Маржа = % прибыли в выручке">Маржа:
								{($order->profit_price / $order->total_price * 100)|number:2}%</span>
							<span class="percent" data-bs-toggle="tooltip" title="ROI = % возврата инвестиций">ROI:
								{($order->profit_price / ($order->total_price - $order->profit_price)*100)|number:2}%</span>
						</div>
					{/if}
				{/if}
				{if !$order->payment_price|empty}
					<div class="main_line">
						<small>К оплате:</small>
						<b>
							{$order->payment_price|price_convert:$order->payment_method->currency->id|raw}

							{if !$order->payment_method->currency->sign|empty}
								<span class="price_sign">{$order->payment_method->currency->sign}</span>
							{else}
								<span class="price_sign">{$currency->sign}</span>
							{/if}
						</b>
					</div>
				{/if}
			</div>

			{if $can_edit}
				<div class="btn_row_add">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="1" id="notify_user" name="notify_user" />
						<label class="form-check-label" for="notify_user">Уведомить покупателя о состоянии заказа по
							email</label>
					</div>

					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			{/if}



			<!-- Финансы -->
			{if  'order_finance'|user_access AND $order->id}
				<div class="layer mt-5">
					<h2>Финансы
						{if $order->payments}
							<span class="sum_total"> всего: {$total->payments_price|price_html:profit|raw}</span>
						{/if}
					</h2>

					<div id="payments" class="list">
						{foreach $order->payments as $p}
							<div class="list_row {if !$p->verified}verified_off{else}verified_on{/if}" item_id="{$p->id}">
								<div class="payment_amount {if $p->related_payment_id}transfer{/if}">
									<a
										href="{'PaymentAdmin'|urll:[id => $p->id]}">{$p->amount|price_html:profit:$p->currency_code|raw}</a>
									{if $p->currency_rate != 1}
										<div class="notice">{$p->currency_amount|price_html|raw}</div>
									{/if}
								</div>

								<div class="order_date">
									<div class="date">{$p->date|date}</div>
									<div class="time">{$p->date|time}</div>
								</div>

								<div class="col row">
									<div class="col-12 col-md-6">
										{if $p->category->name}
											{$p->category->name}
										{else}
											Премещение между кошельками
										{/if} <div class="notice">{$p->comment}</div>
									</div>

									<div class="col-12 col-md-6">
										{$p->purse->name}
										<div class="notice">
											<a
												href="/admin/{$p->contractor->entity_name}/{$p->contractor->entity_id}">{$p->contractor->entity_name}</a>
										</div>
									</div>
								</div>

								<div class="icons">
									<a class="verified" data-bs-toggle="tooltip" title="Cверка с бухгалтерией"></a>
								</div>
							</div>
						{/foreach}
					</div>

					<div class="col-12 btn_row">
						<a href="{'PaymentNewAdmin'|urll}?cur_type=1&contractor_entity_name=order&contractor_entity_id={$order->id}"
							class="btn btn-light" type="submit">Добавить платеж</a>
					</div>
				</div>
			{/if}
		</div>
	</form>
{/block}


{block name=body_script append}

	<!-- Script -->
	<script type="module">
		const order_status = '{$order->status}';
		const currency = '{$currency->sign}';
		const max_order_amount = '{$settings->max_order_amount}';
		const units = '{$settings->units}';

		{literal}
			$(function() {

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
						new_item.find('a.product_name').html(product.name);
						new_item.find('a.product_name').attr('href', '/admin/product/' + product.id +
							'/price');

						new_item.find('input[name*=product_id]').val(product.id);
						new_item.find('.price .js_change').text(product.cost_price);
						new_item.find('input[name*=price]').val(product.price);

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

						new_item.find('.stock .js_change').text(product.stock);
						new_item.find('.amount .js_change ').text(product.weight);

						let amount_select_el = new_item.find('select[name*=amount]');
						let amount = product.stock < 0 ? 0 : product.stock;

						for (let i = 1; i <= amount; i++)
							amount_select_el.append("<option value='" + i + "'>" + i + " " + units +
								"</option>");

						// Дополнительное кол-во для новых заказов (0)
						if (order_status == 0) {
							for (let ai = (Number(amount) + 1); ai <= max_order_amount; ai++)
								amount_select_el.append("<option class='disabled' value='" + ai + "'>" + ai +
									" " + units +
									"</option>");
						}

						if (product.image.url)
							new_item.find('.image img').attr("src", product.image.url);
						else
							new_item.find('.image img').remove();

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


				// Редактировать получателя
				$("i.edit_order_details").on('click', function() {
					if ($("div.view_comment").height() > 10)
						$("div.edit_comment textarea").height($("div.view_comment").height() + 5);

					$("#user .view_order_detail").hide();
					$("#user .edit_order_detail").show();
					return false;
				});


				// Редактировать пользователя
				$("i.edit_user").on('click', function() {
					$("div.view_user").hide();
					$("div.edit_user").show();
					return false;
				});


				$("input#user_id").autocomplete({
					serviceUrl: '/admin/ajax/search/user',
					minChars: 0,
					noCache: false,
					onSelect: function(suggestion) {
						$('input[name="user_id"]').val(suggestion.data.id);
					}
				});


				// Удалить пользователя
				$("#order_details").on('click', 'i.delete_user', function() {
					$('input[name="user_id"]').val(0);
					$('div.view_user').hide();
					$('div.edit_user').hide();
					return false;
				});
			});
		{/literal}
	</script>
{/block}