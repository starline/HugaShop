{extends file='wrapper/main.tpl'}
{include file='finance/parts/menu_part.tpl'}

{if $payment->id}
	{$meta_title = "Платеж №`$payment->id`"}
{else}
	{$meta_title = "Новый платеж"}
{/if}

{block name=content}

	<div class="col-12 header_top">
		<h1>{if $payment->id|isset}
				{if $cur_type == 2}Перевод{else}Платеж{/if} #{$payment->id}
			{else}
				Новый {if $cur_type==2}перевод{else}платеж{/if}
			{/if}
		</h1>
	</div>

	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$payment->id}" />
		{getCSRFInput}

		<div class="row gx-5">
			<div class="col-lg-6">
				<ul class="property_block">
					<li>
						<label class="col-form-label" for="type">Тип платежа</label>
						<select class="form-select status" name="type" id="type"
							{if $cur_type == 2 || $payment->verified}disabled{/if}>
							{if ($cur_type == 2 AND $payment->type == 0) || $cur_type != 2 || ($cur_type == 2 AND !$rel_payment->id)}
								<option value="0" {if $payment->type == 0}selected{/if}>Расход</option>
							{/if}
							{if (($cur_type == 2 AND $payment->type == 1) || $cur_type != 2)}
								<option value="1" {if $payment->type == 1 || $cur_type == 1}selected{/if}>Приход</option>
							{/if}
						</select>
					</li>

					<li>
						<label class="col-form-label" for="purse_id">Кошелек</label>
						<select class="form-select" name="purse_id" id="purse_id" {if $payment->verified}disabled{/if}>
							{foreach $purses as $purse}
								<option class="{if !$purse->enabled}disabled{/if}" value="{$purse->id}"
									currency_sign="{$purse->currency->sign}" currency_id="{$purse->currency_id}"
									{if $payment->purse_id == $purse->id}selected{/if}>{$purse->name}
									({$purse->currency->sign})
								</option>
							{/foreach}
						</select>
					</li>

					{if $cur_type == 2}
						<li>
							<label class="col-form-label" for="purse_to_id">Кошелек
								{if $payment->type == 0}куда{else}откуда{/if}</label>
							<select class="form-select" name="purse_to_id" id="purse_to_id"
								{if $payment->verified}disabled{/if}>
								{foreach $purses as $purse}
									<option class="{if !$purse->enabled}disabled{/if}" value="{$purse->id}"
										currency_sign="{$purse->currency->sign}" currency_id="{$purse->currency_id}"
										{if $rel_payment->purse_id|isset AND $rel_payment->purse_id == $purse->id}selected{/if}>
										{$purse->name} ({$purse->currency->sign})
									</option>
								{/foreach}
							</select>
						</li>
					{else}
						<li>
							<label class="col-form-label" for="finance_category_id">Категория</label>
							<select class="form-select" name="finance_category_id" id="finance_category_id"
								{if $payment->verified}disabled{/if}>
								{foreach $categories as $c}
									<option value="{$c->id}" class="type_{$c->type}" {if $payment->finance_category_id == $c->id}
										selected {/if}>{$c->name}</option>
								{/foreach}
							</select>
						</li>
					{/if}

					<li class="mt-3">
						<label for="amount" class="col-form-label">Сумма</label>

						<div class="row">
							<div class="col-6">
								<div class="input-group col-6">
									<input class="numbermask_2 form-control text-end" id="amount" type="text" name="amount"
										value="{$payment->amount}" autocomplete='off'
										{if $payment->verified}disabled{/if} />
									<span id="currency_sign" class="input-group-text">{$current_currency->sign}</span>
								</div>
							</div>
							<div class="col-6">
								<div class="input-group col-6">
									<input class="numbermask_4 form-control text-end" name="currency_rate"
										id="currency_rate" type="text"
										value="{if $payment->currency_rate>0}{$payment->currency_rate}{else}{$current_currency->rate_to}{/if}"
										{if $payment->verified}disabled{/if} />
									<input type="hidden" name="currency_amount" value="{$payment->currency_amount}" />
									<span class="input-group-text" id="to_currency">{$payment->currency_amount}
										{$to_currency->sign}</span>
								</div>
							</div>
						</div>
					</li>

					<li class="mt-3">
						<label class="col-form-label" for="comment">Комментарий</label>
						<textarea class="form-control" id="comment" name="comment"
							{if $payment->verified}disabled{/if}>{$payment->comment}</textarea>
					</li>
				</ul>

				<div class="btn_row">
					{include file="parts/button.tpl"}
				</div>
			</div>


			<div class="col-lg-6">
				<div class="order_date_time">
					{if !$payment->date|empty}
						<div>
							Создан <span>{$payment->date|date} в {$payment->date|time}</span>
						</div>
					{/if}

					{if !$payment->manager->id|empty}
						<div class="order_manager">
							Кем: <a href="{'UserAdmin'|link:[id => $payment->manager->id]}">{$payment->manager->name}</a>
						</div>
					{/if}

					{if !$payment->purse_amount|empty}
						<div>
							Остаток: <span>{$payment->purse_amount|price_html:null:$current_currency->code|raw} </span>
						</div>
					{/if}
				</div>

				{if !$payment->id|empty}
					<div class="form-check mt-2">
						<input type="hidden" name="verified" value="0">
						<input class="form-check-input" type="checkbox" name="verified" id="verified" value="1"
							{if $payment->verified}checked{/if}>
						<label class="form-check-label" for="verified">Сверено</label>
					</div>
				{/if}

				<div class="order_date_time">
					{if $payment->verified}
						<div>
							Проверен <span>{$payment->verified_date|date} в {$payment->verified_date|time}</span>
						</div>
						<div class="order_manager">
							Кем: <a
								href="{'UserAdmin'|link:[id => $payment->verified_user->id]}">{$payment->verified_user->name}</a>
						</div>
					{/if}
				</div>


				<!-- Изображения -->
				<div class="layer">
					<h2>Фотоотчет</h2>
					{include file='parts\image_upload_part.tpl' images=$payment->images can_edit=true}
				</div>


				<!-- Контрагент -->
				<div class="layer">
					<h2>Контрагент
						<i class="btn_edit_entity material-icons" data-bs-toggle="tooltip" title="Редактировать">edit</i>
						{if $contractor}
							<i class="btn_delete_entity material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
						{/if}
					</h2>

					<div class='view_entity'>
						{if !$contractor->entity_id|empty}
							<a href="/admin/{$contractor->view_name}/{$contractor->entity_id}">{$contractor->entity->name}</a>
						{/if}
					</div>

					<div class="edit_entity" {if $contractor}style="display:none;" {/if}>
						<ul class="property_block">
							<li>
								<label class="col-form-label" for="entity_name">Тип контрагента</label>
								<select class="form-select" name="entity_name" id="entity_name">
									<option value="">Выбирите тип контрагента</option>
									{foreach $contractor_types as $contr}
										<option value="{$contr['entity_name']}" data-type="{$contr['search']}"
											{if $contr['entity_name'] == 'user'} data-sort="manager" {/if}
											{if !$contractor->entity_name|empty and $contractor->entity_name == $contr['entity_name']}selected{/if}>
											{$contr['name']}
										</option>
									{/foreach}
								</select>
							</li>
							<li class="hide_input select_entity">
								<label class="col-form-label" for="entity">Контрагент</label>
								<input type="hidden" name="entity_id" value="{$contractor->entity_id ?? ''}">
								<input type="text" id="entity" class="form-control input_autocomplete"
									placeholder="Выберите контрагента">
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</form>



	<script type="module">
		import '{"js/jquery/jquery.numbermask.js"|asset}';


		{if $cur_type != 2}
			{if $payment->id}
				let current_type = '{$payment->type}';
				let current_finance_category_id = '{$payment->finance_category_id}';
			{else}
				let current_type = '{$cur_type}';
				let current_finance_category_id = null;
			{/if}
		{else}
			let current_type = null;
			let current_finance_category_id = null;
		{/if}

		let currencies = {$currencies|json_encode|raw};
		let purses = {$purses|json_encode|raw};
		let to_currency_sign = '{$to_currency->sign}';

		{literal}

			$(function() {

				// Сhange main purse
				$('select[name="purse_id"]').change(function() {
					let purse_id = $(this).val();

					let index_purse = purses.findIndex(function(x) {
						return x.id === purse_id
					})

					let purse_currency_id = purses[index_purse].currency_id;
					let currency = '';

					for (const [key, value] of Object.entries(currencies)) {
						if (value.id == purse_currency_id) {
							currency = value;
						}
					}

					let currency_sign = $('option:selected', this).attr('currency_sign');
					$('#currency_sign').html(currency_sign);

					$('input[name="currency_rate"]').val(currency.rate_to);
					currency_amount_update();
				});


				// Change to purse
				$('select[name="purse_to_id"]').change(function() {
					to_currency_sign = $('option:selected', this).attr('currency_sign');
					currency_amount_update();
				});


				// Обновляем сумму по курсу
				$('input[name="amount"], input[name="currency_rate"]').keyup(function() {
					currency_amount_update();
				});

				function currency_amount_update() {
					let currency_rate = $('input[name="currency_rate"]').val().replace(",", ".");
					let amount = $('input[name="amount"]').val().replace(",", ".");
					let result = (currency_rate * amount).toFixed(2);
					$('#to_currency').html(result + ' ' + to_currency_sign);
					$('input[name="currency_amount"]').val(result);
				}


				// Устанавливаем категорию
				change_categories(current_type);

				function change_categories(type) {
					$('select[name="finance_category_id"]').find('option').hide();
					$('select[name="finance_category_id"]').find('option.type_' + type).show();
				}


				// Смена типа платежа
				$('select[name="type"]').change(function() {
					var type = $(this).val()
					change_categories(type);

					// Set first option
					$('select[name="finance_category_id"]').val($(
						'select[name="finance_category_id"] option.type_' + type + ':first').val());
				});


				// Выбираем контрагента
				$("i.btn_edit_entity").click(function() {
					$("div.view_entity").hide();
					$("div.edit_entity").show();
					$('input[name="entity_id"]').val('');
					$('select[name="entity_name"]').val('');
					return false;
				});


				// удаляем контрагента
				$("i.btn_delete_entity").click(function() {
					$("div.view_entity").hide();
					$("div.edit_entity").show();
					$('input[name="entity_id"]').val('');
					$('select[name="entity_name"]').val('');
					return false;
				});


				// Выбираем тип контрагента
				$('select[name="entity_name"]').change(function() {
					let entity_type = $(this).find('option:selected').data('type');
					let params = {
						csrf: window.csrf
					};

					let entity_sort = $(this).find('option:selected').data('sort');
					if (entity_sort) {
						params.sort = entity_sort;
					}

					if (entity_type !== undefined) {
						$(".select_entity").removeClass('hide_input');
						$("input#entity").autocomplete({
							serviceUrl: '/admin/ajax/' + entity_type,
							minChars: 0,
							noCache: false,
							params: params,
							onSelect: function(suggestion) {
								$('input[name="entity_id"]').val(suggestion.data.id);
							}
						});
					} else {
						$('input[name="entity_id"]').val('');
						$("input#entity").val('');
						$(".select_entity").hide();
					}
				});


				// Устанавливаем формат
				$('input.numbermask_2').numberMask({type: 'float', afterPoint: 2, decimalMark: ['.', ',']});
				$('input.numbermask_4').numberMask({type: 'float', afterPoint: 4, decimalMark: ['.', ',']});
			});

		{/literal}
	</script>

{/block}