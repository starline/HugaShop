{extends 'wrapper/main.tpl'}
{include 'warehouse/parts/menu_part.tpl'}

{if $status == 3}
	{$meta_title='Списание товаров'}
{else}
	{$meta_title='Поставки товаров'}
{/if}

{block name=content}

	{if $message_error}
		<div class="message message_error">
			<span class="text">{if $message_error=='error_closing'}Нехватка некоторых товаров на
				складе{else}{$message_error}
				{/if}</span>
		</div>
	{/if}

	<div class="two_columns_list">

		<div class="header_top">
			<h1>
				{if $status == 3}
					{if $movements_count}{$movements_count}{else}Нет{/if} списан{$movements_count|plural:'ие':'ий':'ия'}
				{elseif $status == 4}
					{if $movements_count}{$movements_count}{else}Нет{/if} отмененн{$movements_count|plural:'ое':'ых':'ых'}
				{elseif $status == 2}
					{if $movements_count}Поступило {$movements_count}{else}Нет{/if}
					постав{$movements_count|plural:'ка':'ок':'ки'}
				{else}
					{if $movements_count}
						{if $status == 1}Ожидаем {elseif $status === 0}Новыe {else}Всего {/if}
						{$movements_count}
					{else}
						Нет
					{/if}
					постав{$movements_count|plural:'ка':'ок':'ки'}

					{if 'finance'|user_access AND !$total|empty AND $status !== null }
						<span class="sum_total">на сумму: {$total->cost_price|price_html|raw}
							<span class="sum_profit_price">
								{($total->retail_price - $total->cost_price)|price_html:profit|raw}
							</span>
						</span>

						<span class="sum_total"> ({$total->product_amount} единиц товара)</span>
					{/if}
				{/if}
			</h1>
			{if 'warehouse_edit'|user_access OR 'warehouse_add'|user_access}
				<a class="add" href="{'MoveNewAdmin'|link}">Добавить перемещение товара</a>
			{/if}
		</div>


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
					<ul class="menu_list">
						<li {if $status === null}class="selected" {/if}>
							<a href='{url status=null clear=true}'>Все поставки</a>
						</li>
						<li {if $status === 0}class="selected" {/if}>
							<a href='{url status='0' clear=true}'>Новые</a>
						</li>
						<li {if $status == 1}class="selected" {/if}>
							<a href='{url status=1 clear=true}'>Ожидаем</a>
						</li>
						<li {if $status == 2}class="selected" {/if}>
							<a href='{url status=2 clear=true}'>Поступило</a>
						</li>
					</ul>

					<ul class='menu_list layer'>
						<li {if $status == 3}class="selected" {/if}><a href='{url status=3 clear=true}'>Списано</a></li>
						<li {if $status == 4}class="selected" {/if}><a href='{url status=4 clear=true}'>Отмена</a></li>
					</ul>
				</div>
			</div>
		</div>


		<div id="main_list">
			{if $movements}

				{include file='parts/pagination.tpl'}

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list">
						{foreach $movements as $movement}
							<div class="list_row {if $movement->status == 2}highlight{/if}" item_id="{$movement->id}">

								{if 'warehouse_edit'|user_access && $status === 4}
									<div class="checkbox">
										<input class="form-check-input" type="checkbox" name="check[]" value="{$movement->id}" />
									</div>
								{/if}

								<div class="order_date">
									<a class="order_id" href="{'MoveAdmin'|link:[id => $movement->id]}">
										<span>{$movement->id}</span>
									</a>
									<div class="date">{$movement->date|date}</div>
								</div>

								<div class="col row">
									<div class="col-12 col-md-6">
										<div class="purchases">
											{foreach $movement->purchases as $purchase}
												<div class="image">
													<div class="amount">{$purchase->amount}</div>
													<img data-bs-toggle="tooltip"
														title="{$purchase->product_name}{if $purchase->variant_name}- {$purchase->variant_name}{/if}"
														src='{if $purchase->product->image->filename}{$purchase->product->image->filename|resize:60:60:c}{else}{'images/cargo.png'|asset}{/if}' />
												</div>
											{/foreach}
										</div>
									</div>

									<div class="col-12 col-md-6 mt-3 mt-md-0">
										{if $movement->status != 0}
											<div class="badge text-bg-round">{$movement->awaiting_date|date:m}</div>
										{/if}

										<div class="order_address">
											{$movement->note|strip_tags|nl2br|raw}
										</div>

										{if 'warehouse_edit'|user_access AND $movement->note_logist}
											<div class="notice_block">
												<div class="notice_block_text">{$movement->note_logist|strip_tags|nl2br|raw}</div>
												<div class="show_link_block">
													<a class="show_link" href="#">раскрыть ↓</a>
												</div>
											</div>
										{/if}
									</div>
								</div>

								<div class="icons flex-column">
									{if $movement->status == 0}
										<i>
											<img src="{'images/new.png'|asset}" data-bs-toggle="tooltip" title='Новый'>
										</i>
									{/if}
									{if $movement->status == 1}
										<i>
											<img src="{'images/time.png'|asset}" data-bs-toggle="tooltip" title='Ожидаем'>
										</i>
									{/if}
									{if $movement->status == 2}
										<i>
											<img src="{'images/tick.png'|asset}" data-bs-toggle="tooltip" title='Принят'>
										</i>
									{/if}
									{if $movement->status == 3  || $movement->status == 4}
										<i>
											<img src="{'images/cross.png'|asset}" data-bs-toggle="tooltip" title='Списан'>
										</i>
									{/if}
									{if $movement->images->isNotEmpty()}
										<i>
											<img src="{'images/clipboard.png'|asset}" data-bs-toggle="tooltip" title="Фотоотчет">
										</i>
									{/if}
								</div>

							</div>
						{/foreach}
					</div>

					{if 'warehouse_edit'|user_access && $status === 4}
						<div id="action">
							<span id='check_all' class="dash_link">Выбрать все</span>
							<span id="select">
								<select class="form-select" name="action">
									<option value="">Выбрать действие</option>
									<option value="delete">Удалить выбранные поставки</option>
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
	<script type="module">
		import { initNoticeBlocks } from '{"js/common.js"|asset}';
		$(function() { initNoticeBlocks(); });
	</script>
{/block}