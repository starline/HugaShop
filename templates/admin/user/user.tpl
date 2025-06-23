{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}
{include 'user/parts/submenu_part.tpl'}

{if $current_user->id}
	{$meta_title = $current_user->name}
{/if}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">
				{if $message_error=='email_exists'}Пользователь с таким email уже зарегистрирован
				{elseif $message_error=='phone_exists'}Пользователь с таким телефоном уже зарегистрирован
				{elseif $message_error=='te_name_exists'}Пользователь с таким именем Телеграм уже зарегистрирован
				{else}{$message_error}
				{/if}</span>
		</div>
	{/if}


	<!-- Основная форма -->
	<form method="post">
		<input name="id" type="hidden" value="{$current_user->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check">
							<input class="form-check-input" name="enabled" value='1' type="checkbox" id="active_checkbox"
								{if !'user_edit'|user_access}disabled{/if} {if $current_user->enabled}checked{/if} />
							<label for="active_checkbox">Активен</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" name="manager" value='1' type="checkbox" id="manager_checkbox"
								{if !'user_manager'|user_access}disabled{/if} {if $current_user->manager}checked{/if} />
							<label class="form-check-label" for="manager_checkbox">Сотрудник</label>
						</div>
					</div>
				</div>

				<div class="name_row">
					<div class="col">
						<input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}"
							name="name" type="text" value="{$current_user->name}" autocomplete="off" />
						<div class="invalid-feedback">Введите имя</div>
					</div>
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Данные пользователя</h2>
				<ul class="property_block">
					{if $groups}
						<li>
							<label class="col-form-label" for="group_id">Группа</label>
							<select class="form-select" id="group_id" name="group_id"
								{if !'user_group'|user_access}disabled{/if}>
								<option value="">Не входит в группу</option>
								{foreach $groups as $g}
									<option value='{$g->id}' {if $current_user->group->id == $g->id}selected{/if}>{$g->name}
									</option>
								{/foreach}
							</select>
						</li>
					{/if}

					<li>
						<label for="email" class="col-form-label">Email</label>
						<input class="form-control" id="email" name="email" type="text" value="{$current_user->email}"
							autocomplete="off"
							{if !'user_settings'|user_access and !$current_user->email|empty}disabled{/if} />
					</li>
					<li>
						<label for="phone" class="col-form-label">Телефон</label>
						<input class="form-control" id="phone" name="phone" type="text" value="{$current_user->phone}"
							autocomplete="off" />
					</li>

					{if 'user_settings'|user_access}
						<li>
							<label for="token" class="col-form-label">Токен</label>
							<input class="form-control" id="token" name="token" type="text" value="{$current_user->token}"
								autocomplete="off" disabled />
						</li>
					{/if}

					{if !$current_user->te_name|empty}
						<li>
							<label for="te_name" class="col-form-label">Телеграм имя</label>
							<input class="form-control" id="te_name" name="te_name" type="text" value="{$current_user->te_name}"
								autocomplete="off" disabled />
						</li>
					{/if}

					<li>
						<label for="created" class="col-form-label">Дата регистрации</label>
						<input class="form-control" id="created" type="text" disabled
							value="{$current_user->created|date}" />
					</li>
					<li>
						<label for="ip" class="col-form-label">Последний IP</label>
						<input class="form-control" id="ip" type="text" disabled value="{$current_user->last_ip}" />
					</li>
					<li>
						<label for="comment" class="col-form-label">Заметки</label>
						<textarea class="form-control" id="comment" name="comment">{$current_user->comment}</textarea>
					</li>
				</ul>

				<div class="col-12 btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>
		</div>
	</form>


	<!-- Статистика продаж менеджера -->
	{if 'user_manager'|user_access OR $current_user->id == $user->id}
		<div class="product_stats">
			<div class="chart_actions btn_row">
				<a class="btn btn-light" id="product_stats_reset">Reset zoom</a>
			</div>
			<div id="product_stats"></div>
		</div>
	{/if}


	<div class="layer mt-5">

		<div class="header_top">
			<h1>
				{if $orders_count}{$orders_count}{else}Нет{/if} заказ{$orders_count|plural:'':'ов':'а'}
				{if 'order_finance'|user_access AND $orders_price->sum_total_price}
					<span class="sum_total">оплачено на сумму: {$orders_price->sum_total_price|price_html|raw}
						<span class="sum_profit_price">{$orders_price->sum_profit_price|price_html:profit|raw}</span>
					</span>
				{/if}
			</h1>
		</div>

		{include file='parts/pagination.tpl'}

		<div class="list">
			{foreach $orders as $order}
				{include file='order/parts/order_item_part.tpl'}
			{/foreach}
		</div>

		{include file='parts/pagination.tpl'}

	</div>
{/block}


{block name=body_script append}
	<script type="text/javascript" src="{'js/chart/luxon.js'|asset}"></script>
	<script type="module">
		import 'https://cdn.jsdelivr.net/npm/apexcharts';
		import { makeChart } from '{"js/common.js"|asset}';

		const php_manager_id = '{$current_user->id}';
		const php_currency_name = '{$currency->name}';
		const php_currency_sign = '{$currency->sign}';

		{literal}

			let statsChart = makeChart(
				document.getElementById('product_stats'), {
					chart: { type: 'bar', height: 250 },
					title: { text: 'Статистика продаж менеджера' },
					subtitle: { text: 'Доход по месяцам' }
				},
				[{
						filter: {
							manager_id: php_manager_id,
							filter: 'byMonth',
							csrf: csrf,
							type: 'totalPrice'
						},
						options: {
							label: 'Сумма дохода, ' + php_currency_sign,
							color: '#76c100',
							url: '/admin/ajax/stats/order'
						}
					},
					{
						filter: {
							manager_id: php_manager_id,
							filter: 'byMonth',
							csrf: csrf,
							type: 'amount'
						},
						options: {
							label: 'Колл-во заказов, шт',
							color: '#000000',
							url: '/admin/ajax/stats/order'
						}
					},
					{
						filter: {
							manager_id: php_manager_id,
							filter: 'byMonth',
							csrf: csrf,
							type: 'totalPayments'
						},
						options: {
							label: 'Сумма платежей, ' + php_currency_sign,
							color: '#f8a13f',
							url: '/admin/ajax/stats/order'
						}
					}
				]
			);

			$('#product_stats_reset').click(function() {
				if (statsChart.chart) statsChart.chart.resetSeries();
			});

		{/literal}
	</script>
{/block}