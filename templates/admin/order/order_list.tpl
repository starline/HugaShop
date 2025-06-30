{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{$meta_title='Заказы'}


{block name=content}
	{if $message_error}
		<div class="message message_error">
			<span class="text">{if $message_error == 'error_closing'}Нехватка некоторых товаров на
				складе{else}{$message_error}
				{/if}</span>
		</div>
	{/if}

	<div class="two_columns_list">

		<div class="header_top">
			<h1>{if $orders_count}{$orders_count}{else}Нет{/if} заказ{$orders_count|plural:'':'ов':'а'}

				{if 'order_finance'|user_access AND $orders_price->sum_total_price}
					<span class="sum_total">на сумму: {$orders_price->sum_total_price|price_html|raw}
						<span class="sum_profit_price">{$orders_price->sum_profit_price|price_html:profit|raw}</span>
					</span>
				{/if}
			</h1>

			<a class="add" href="/admin/order">Добавить заказ</a>

			{if $orders_count > 0 and 'export'|user_access}
				<form class="export_btn" method="post" action="/admin/orders/export?{$smarty.server.QUERY_STRING}"
					target="_blank">
					<input type="image" src="{'images/export_excel.png'|asset}" name="export" data-bs-toggle="tooltip"
						title="Экспортировать выбранные заказы">
				</form>
			{/if}

			<form method="get" id="search">
				{getCSRFInput}
				<div class="input-group">
					<input class="search form-control" type="text" name="keyword" value="{$keyword}"
						placeholder="№, телефон, город, имя" />
					<input class="input-group-text search_button" type="submit" value="" />
				</div>
			</form>
		</div>

		<div class="navbar-expand-lg" id="right_menu">

			<div class="popup_menu_btn navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#filter_menu_block"
				aria-controls="filter_menu_block">
				<span class="material-icons">menu</span>
				<span class="popup_btn_text">Фильтр</span>
			</div>


			<div class="offcanvas offcanvas-start" id="filter_menu_block" tabindex="-1" aria-labelledby="offcanvasLabel">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="offcanvasLabel"></h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>

				<div class="offcanvas-body">

					<!-- Метки -->
					{if !$labels|empty}
						<ul class="menu_list">
							<li class="{if !$label and !$paid}selected{/if}">
								<a href="{url label=null paid=null}">Все заказы</a>
							</li>

							{foreach $labels as $lab}
								{if ($lab->enabled OR 'order_edit'|user_access) AND $lab->in_filter}
									<li
										class="label {if $label->id|isset AND $label->id == $lab->id}selected{/if} {if !$lab->enabled}disabled{/if}">
										<a style="background-color:#{$lab->color};"
											href="{url label=$lab->id paid=null}">{$lab->name}</a>
									</li>
								{/if}
							{/foreach}

						</ul>
					{/if}

					<!-- Фильтры -->
					<ul class="menu_list layer">
						<li class="{if $paid === 1}selected{/if}">
							<a href="{url paid=1}">Оплачены</a>
						</li>
						<li class="{if $paid === 0}selected{/if}">
							<a href="{url paid=0}">Не оплачены</a>
						</li>
					</ul>
				</div>
			</div>
		</div>


		<!-- Список заказов -->
		<div id="main_list">
			{if !$orders|empty}

				{if !$pagination_hide}
					{include file='parts/pagination.tpl'}
				{elseif ($settings->products_num_admin <= $orders_count)}
					<div class="pagination">Показано только первые {$settings->products_num_admin} заказа</div>
				{/if}

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list">
						{foreach $orders as $order}
							{include file='order/parts/order_item_part.tpl'}
						{/foreach}
					</div>

					{if 'order_edit'|user_access and ($status|in_array:[2, 3, 4] || !$keyword|empty)}
						<div id="action">
							<span id="check_all" class="dash_link">Выбрать все</span>

							<span id="select">
								<select class="form-select" name="action">
									<option value="">Выбрать действие</option>
									{foreach $labels as $l}
										{if $l->enabled}
											<option value="set_label_{$l->id}">Отметить &laquo;{$l->name}&raquo;</option>
										{/if}
									{/foreach}

									{foreach $labels as $l}
										{if $l->enabled}
											<option value="unset_label_{$l->id}">Снять &laquo;{$l->name}&raquo;</option>
										{/if}
									{/foreach}

									{if $status !== 0}<option value="set_status_0">В новые</option>{/if}
									{if $status !== 1}<option value="set_status_1">В принятые</option>{/if}
									{if $status !== 4}<option value="set_status_4">В отгруженые</option>{/if}
									{if $status !== 2}<option value="set_status_2">В выполненные</option>{/if}

									{if 'order_delete'|user_access}
										<option value="delete">
											{if $status !== 3 and $keyword}
												Отменить выбранные заказы
											{else}
												Удалить выбранные заказы
											{/if}
										</option>
									{/if}
								</select>
							</span>
							<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
						</div>
					{/if}

				</form>

				{if !$pagination_hide}
					{include file='parts/pagination.tpl'}
				{/if}

			{/if}
		</div>

	</div>
{/block}