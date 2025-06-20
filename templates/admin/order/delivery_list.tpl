{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{$meta_title='Способы доставки'}


{block name=content}
	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">{if $message_error == 'order'}Невозможно удалить способ доставки связанный с заказом{/if}</span>
		</div>
	{/if}

	<!-- Заголовок -->
	<div class="header_top">
		<h1>{$meta_title}</h1>
		<a class="add" href="{'OrderDeliveryNewAdmin'|urll}">Добавить способ доставки</a>
	</div>

	<div id="main_list">

		{if $deliveries}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $deliveries as $delivery}
						<div
							class="list_row {if !$delivery->enabled}enabled_off{/if} {if !$delivery->enabled_public}enabled_public_off{/if}">

							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$delivery->id}]" value="{$delivery->position}" />
							</div>

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$delivery->id}" />
							</div>

							<div class="col">
								<a href="{'OrderDeliveryAdmin'|urll:[id => $delivery->id]}">{$delivery->name}</a>
								<span class="badge text-bg-round">{$delivery->public_name}</span>
								<div class="notice">{$delivery->comment|strip_tags|nl2br|raw}</div>
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
					<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
				</div>
			</form>
		{else}
			Нет способов доставки
		{/if}
	</div>
{/block}


{block name=body_script append}
	<!-- Script -->
	<script type="module">
		var csrf = "{setCSRF}";

		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Скрыт/Видим
				$("i.enable").click(function() {
					ajax_icon($(this), 'delivery', 'enabled', csrf);
					return false;
				});

				$("i.enable_public").click(function() {
					ajax_icon($(this), 'delivery', 'enabled_public', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}