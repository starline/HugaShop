{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{$meta_title='Оповещения пользователей'}

{block name=content}

	<div class="header_top">
		<h1>Оповещения пользователей</h1>
		<a class="add" href="{'NotifierNewAdmin'|link}">Добавить способ оповещения</a>
	</div>


	<div id="main_list">

		{if !$notifier_list|empty}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $notifier_list as $notifier}
						<div class="list_row {if !$notifier->enabled}enabled_off{/if}" item_id="{$notifier->id}">
							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$notifier->id}]" value="{$notifier->position}">
							</div>

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$notifier->id}" />
							</div>

							<div class="col">
								<a href="{'NotifierAdmin'|link:[id => $notifier->id]}">{$notifier->name}</a>
								<div class="notice">{$notifier->comment}</div>
							</div>


							<div class="icons">
								<i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Активеный"></i>
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						</div>
					{/foreach}
				</div>

				<div id="action">
					<span id="check_all" class="dash_link">Выбрать все</span>
					<span id=select>
						<select class="form-select" name="action">
							<option value="">Выбрать действие</option>
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
					ajax_icon($(this), 'user_notifier', 'enabled', csrf);
					return false;
				});
			});
		{/literal}
	</script>

{/block}