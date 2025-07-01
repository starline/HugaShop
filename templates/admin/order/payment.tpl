{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{if $payment_method->id}
	{$meta_title = $payment_method->name scope=global}
{else}
	{$meta_title = 'Новый способ оплаты' scope=global}
{/if}


{block name=content}
	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$payment_method->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check">
							<input class="form-check-input" name="enabled" value="1" type="checkbox" id="enabled"
								{if $payment_method->enabled}checked{/if} />
							<label class="form-check-label" for="enabled">Показывать менеджеру</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" name="enabled_public" value="1" type="checkbox"
								id="enabled_public" {if $payment_method->enabled_public}checked{/if} />
							<label class="form-check-label" for="enabled_public">Показывать клиенту при заказе</label>
						</div>
					</div>
				</div>

				<div class="name_row">
					<input class="form-control form-control-lg " name="name" type="text" value="{$payment_method->name}"
						autocomplete="off" />
				</div>
			</div>


			<div class="col-lg-6 layer">
				<h2>Настройки оплаты</h2>
				<ul class="property_block">
					<li>
						<label for="public_name">Публичное название</label>
						<input class="form-control" name="public_name" id="public_name" type="text"
							value="{$payment_method->public_name}" />
					</li>
					<li>
						<label for="modules">Модуль Оплаты</label>
						<select class="form-select" name="module" id="modules">
							<option value="">Ручная обработка</option>
							{foreach $payment_modules as $payment_module}
								<option value="{$payment_module@key}"
									{if $payment_method->module == $payment_module@key}selected{/if}>
									{$payment_module->name}</option>
							{/foreach}
						</select>
					</li>
					<li>
						<label for="currency_id">Валюта</label>
						<select class="form-select" name="currency_id" id="currency_id">
							{foreach $currencies as $currency}
								<option value="{$currency->id}" {if $currency->id == $payment_method->currency_id}selected{/if}>
									{$currency->name}</option>
							{/foreach}
						</select>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				{include file='parts/module_settings_part.tpl' module_type='payment_method' modules=$payment_modules}
			</div>

			<div class="col-lg-6 layer">
				<h2>Дополнительные настройки</h2>
				<ul class="property_block">
					<li>
						<label for="finance_purse_id" class="col-form-label">Связанный кошелек</label>
						<select class="form-select" name="finance_purse_id" id="finance_purse_id">
							<option value="0">---</option>
							{foreach $purses as $purse}
								<option class="{if !$purse->enabled}disabled{/if}"
									{if $purse->id == $payment_method->finance_purse_id} selected {/if} value="{$purse->id}">
									{$purse->name} ({$purse->currency->sign})</option>
							{/foreach}
						</select>
					</li>
					<li>
						<label for="comment" class="col-form-label">Заметки</label>
						<textarea class="form-control" name="comment" id="comment">{$payment_method->comment}</textarea>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				<h2>Возможные способы доставки</h2>
				<div>
					{foreach $deliveries as $delivery}
						{if $delivery->enabled}
							<div class="form-check {if !$delivery->enabled}disabled{/if}">
								<input class="form-check-input" type="checkbox" name="payment_method_deliveries[]"
									id="delivery_{$delivery->id}" value="{$delivery->id}"
									{if in_array($delivery->id, $payment_method->deliveries_ids)}checked{/if}>
								<label class="form-check-label" for="delivery_{$delivery->id}">{$delivery->name}</label>
							</div>
						{/if}
					{/foreach}
				</div>
			</div>

			<div class="col-12 layer">
				<h2>Описание для клиента</h2>
				<textarea name="description" class="html_editor editor_small">{$payment_method->description}</textarea>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>

	</form>
{/block}


{block name=body_script append}

	<!-- Script -->
	{include file='parts/tinymce_init.tpl'}

{/block}