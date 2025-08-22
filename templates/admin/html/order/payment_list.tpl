{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{$meta_title='Способы оплаты'}


{block name=content}
	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">{if $message_error == 'order'}Невозможно удалить способ оплаты связанный с заказом{/if}</span>
		</div>
	{/if}

	<div class="header_top">
		<h1>{$meta_title}</h1>
		<a class="add" href="{'OrderPaymentNewAdmin'|link}">Добавить способ оплаты</a>
	</div>

	<div id="main_list">

		{if $payment_methods}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $payment_methods as $payment_method}
						<div class="list_row {if !$payment_method->enabled}enabled_off{/if} {if !$payment_method->enabled_public}enabled_public_off{/if}"
							item_id="{$payment_method->id}">

							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$payment_method->id}]" value="{$payment_method->position}" />
							</div>

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$payment_method->id}" />
							</div>

							<div class="col">
								<a href="{'OrderPaymentAdmin'|link:[id => $payment_method->id]}">{$payment_method->name}</a>
								<span class="badge text-bg-round">{$payment_method->public_name}</span>
								<div class="notice">{$payment_method->comment|strip_tags|nl2br|raw}</div>
							</div>

							<div class="icons">
								<i class="enable_public material-icons shopping_cart" data-bs-toggle="tooltip"
									title="Показывать клиенту при заказе"></i>
								<i class="enable material-icons visibility" data-bs-toggle="tooltip"
									title="Показывать менеджеру"></i>
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
					{include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
				</div>
			</form>
		{else}
			Нет способов оплаты
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
					ajax_icon($(this), 'payment_method', 'enabled', csrf);
					return false;
				});

				$("i.enable_public").click(function() {
					ajax_icon($(this), 'payment_method', 'enabled_public', csrf);
					return false;
				});
			});
		{/literal}
	</script>
{/block}