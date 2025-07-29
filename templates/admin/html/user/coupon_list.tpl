{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{$meta_title='Купоны'}

{block name=content}

	<div class="header_top">
		{if $coupons_count}
			<h1>{$coupons_count} {$coupons_count|plural:'купон':'купонов':'купона'}</h1>
		{else}
			<h1>Нет купонов</h1>
		{/if}
		<a class="add" href="/admin/user/coupon">Новый купон</a>
	</div>


	{if $coupons}
		<div id="main_list">

			{include file='parts/pagination.tpl'}

			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list">
					{foreach $coupons as $coupon}
						<div class="list_row {if $coupon->valid}highlight{/if}" item_id="{$coupon->id}">
							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$coupon->id}" />
							</div>

							<div class="col row">
								<div class="col-12 col-sm-4">
									<a href="/admin/user/coupon/{$coupon->id}">{$coupon->code}</a>
								</div>

								<div class="col-12 col-sm-4">
									Скидка {$coupon->value*1} {if $coupon->type=='absolute'}{$currency->sign}{else}%{/if}<br>
									{if $coupon->min_order_price>0}
										<div class="detail">
											Для заказов от {$coupon->min_order_price} {$currency->sign}
										</div>
									{/if}
								</div>

								<div class="col-12 col-sm-4">
									{if $coupon->single}
										<div class="detail">
											Одноразовый
										</div>
									{/if}
									{if $coupon->usages>0}
										<div class="detail">
											Использован {$coupon->usages} {$coupon->usages|plural:'раз':'раз':'раза'}
										</div>
									{/if}
									{if $coupon->expire}
										<div class="detail">
											{if $config->now|date_format:'%Y%m%d' <= $coupon->expire|date_format:'%Y%m%d'}
												Действует до {$coupon->expire|date}
											{else}
												Истёк {$coupon->expire|date}
											{/if}
										</div>
									{/if}
								</div>
							</div>

							<div class="icons">
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						</div>
					{/foreach}
				</div>

				<div id="action">
					<span id="check_all" class="dash_link">Выбрать все</span>
					<span id="select">
						<select class="form-select" name="action">
							<option value="">Выбрать действие</option>
							<option value="delete">Удалить</option>
						</select>
					</span>
					<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
				</div>

			</form>

			{include file='parts/pagination.tpl'}

		</div>
	{/if}

{/block}