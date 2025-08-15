{extends file='wrapper/main.tpl'}
{include file='finance/parts/menu_part.tpl'}

{$meta_title='Кошелки'}

{block name=content}

	<div class="header_top">
		{if $purses_count>0}
			<h1 class="total_amount">
				{$purses_count} {$purses_count|plural:'кошелек':'кошельков':'кошелька'}

				{foreach $total_amount as $ta}
					<div class="currency_amount">
						<span class="sum_total">{$ta->amount|price_html:no_currency:$ta->code|raw} <span
								class="sum_profit_price">{$ta->sign}</span></span>
					</div>
				{/foreach}
			</h1>
		{else}
			<h1>Нет кошельков</h1>
		{/if}

		<a class="add" href="{'PurseNewAdmin'|link}">Добавить кошелек</a>
	</div>


	<div id="main_list" class="finance">

		{if $purses}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $purses as $p}
						<div class="list_row {if !$p->enabled}enabled_off{/if}" item_id="{$p->id}">

							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$p->id}]" value="{$p->position}">
							</div>

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$p->id}" />
							</div>

							<div class="col row">
								<div class="col-12 col-sm-8">
									<a href="{'PurseAdmin'|link:[id => $p->id]}">{$p->name}</a>
									<div class="notice">{$p->comment|strip_tags|nl2br|raw}</div>
								</div>

								<div class="col-12 col-sm-4 text-sm-end">
									{$p->amount|price_html:color:$p->currency_code|raw}
								</div>
							</div>

							<div class="icons">
								<i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Активна"></i>
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
							<option value="enable">Включить</option>
							<option value="disable">Выключить</option>
							<option value="delete">Удалить</option>
						</select>
					</span>
					<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
				</div>
			</form>

		{/if}
	</div>
{/block}


{block name=body_script append}
	<script type="module">
		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Скрыт/Видим
				$("i.enable").click(function() {
					ajax_icon($(this), 'purse', 'enabled', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}