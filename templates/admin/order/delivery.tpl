{extends 'wrapper/main.tpl'}
{include "order/parts/menu_part.tpl"}


{if $delivery->id}
	{$meta_title = $delivery->name}
{else}
	{$meta_title = 'Новый способ доставки'}
{/if}


{block name=content}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$delivery->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
                                                <div class="form-check form-switch">
                                                        <input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch" id="active_checkbox"
                                                                {if $delivery->enabled}checked{/if} />
                                                        <label class="form-check-label" for="active_checkbox">Показывать меннеджеру</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                        <input class="form-check-input" name="enabled_public" value="1" type="checkbox" role="switch"
                                                                id="enabled_public" {if $delivery->enabled_public}checked{/if} />
                                                        <label class="form-check-label" for="enabled_public">Показывать клиенту при заказе</label>
                                                </div>
					</div>
				</div>

				<div class="name_row">
					<input class="form-control form-control-lg" name="name" type="text" value="{$delivery->name}" />
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Стоимость доставки</h2>
				<ul class="property_block">
					<li>
						<label for="public_name">Публичное название</label>
						<input class="form-control" name="public_name" id="public_name" type="text"
							value="{$delivery->public_name}" />
					</li>
					<li class="row_sm">
						<label for="price" class="col-form-label">Стоимость</label>
						<div class="input-group">
							<input class="form-control" id="price" name="price" type="text" value="{$delivery->price}" />
							<span class="input-group-text"> {$currency->sign}</span>
						</div>
					</li>
					<li class="row_sm">
						<label for="free_from" class="col-form-label">Бесплатна от</label>
						<div class="input-group">
							<input class="form-control" id="free_from" name="free_from" type="text"
								value="{$delivery->free_from}" />
							<span class="input-group-text">{$currency->sign}</span>
						</div>
					</li>

					<li>
						<div></div>
						<div class="form-check">
							<input class="form-check-input" id="separate_payment" name="separate_payment" type="checkbox"
								value="1" {if $delivery->separate_payment}checked{/if} />
							<label class="form-check-label" for="separate_payment">Оплачивается отдельно</label>
						</div>
					</li>

					<li>
						<label class="col-form-label" for="finance_purse_id">Кошелек для оплаты доставки</label>
						<select class="form-select" name="finance_purse_id" id="finance_purse_id">
							<option value="">Не выбран</option>
							{foreach $finance_purses as $finance_purse}
								<option class="{if !$finance_purse->enabled}disabled{/if}" value="{$finance_purse->id}"
									{if $delivery->finance_purse_id == $finance_purse->id}selected{/if}>
									{$finance_purse->name}</option>
							{/foreach}
						</select>
					</li>
					<li>
						<label for="comment" class="col-form-label">Заметки</label>
						<textarea class="form-control" name="comment" id="comment">{$delivery->comment}</textarea>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				<h2>Возможные способы оплаты</h2>
				<div>
					{foreach $payment_methods as $payment_method}
						<div class="form-check {if !$payment_method->enabled}disabled{/if}">
							<input class="form-check-input" type="checkbox" name="delivery_payments[]"
								id="payment_{$payment_method->id}" value='{$payment_method->id}'
								{if in_array($payment_method->id, $delivery->payments_ids)}checked{/if}>
							<label class="form-check-label" for="payment_{$payment_method->id}">{$payment_method->name}</label>
						</div>
					{/foreach}
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Модуль доставки</h2>
				<ul class="property_block">
					<li>
						<label class="col-form-label" for="modules">Модуль</label>
						<select class="form-select" name="module" id="modules">
							<option value="">Без модуля</option>
							{foreach $delivery_modules as $delivery_module}
								<option value="{$delivery_module@key}"
									{if $delivery->module == $delivery_module@key}selected{/if}>
									{$delivery_module->name}</option>
							{/foreach}
						</select>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				{include file='parts/module_settings_part.tpl' module_type='delivery' modules=$delivery_modules}
			</div>

			<div class="col-12 layer">
				<h2>Описание</h2>
				<textarea name="description" class="html_editor editor_small">{$delivery->description}</textarea>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>
{/block}


{block name=body_script append}

	{* Подключаем Tiny MCE *}
	{include 'parts/tinymce_init.tpl'}

{/block}