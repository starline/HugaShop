{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{$meta_title='Метки заказов'}


{block name=content}
	{* Заголовок *}
	<div class="header_top">
		<h1>{$meta_title}</h1>
		<a class="add" href="{'LabelNewAdmin'|urll}">Новая метка</a>
	</div>

	<div id="main_list">

		{if $labels}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $labels as $label}
						<div class="list_row {if !$label->enabled} enabled_off{/if}{if !$label->in_filter}in_filter_off{/if}"
							item_id="{$label->id}">

							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$label->id}]" value="{$label->position}" />
							</div>

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$label->id}" />
							</div>

							<div class="col">
								<span style="background-color:#{$label->color};" class="order_label"></span>
								<a href="{'LabelAdmin'|urll:[id => $label->id]}">{$label->name}</a>
							</div>

							<div class="icons">
								<a class="in_filter" data-bs-toggle="tooltip" title="Использовать в фильтре" href='#'></a>
								<i class="enable edit material-icons visibility" data-bs-toggle="tooltip" title="Активна"></i>
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
		{else}
			Нет меток
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
				$("i.enable.edit").click(function() {
					ajax_icon($(this), 'label', 'enabled', csrf);
					return false;
				});

				// Указать "в фильтре"/"не в фильтре"
				$("a.in_filter").click(function() {
					ajax_icon($(this), 'label', 'in_filter', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}