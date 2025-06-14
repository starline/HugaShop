{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='Информационный блок'}

{block name=content}

	<!-- Заголовок -->
	<div class="header_top">
		<h1>{$meta_title}</h1>
		<a class="add" href="/admin/extension/{$extension->module}/block">Добавить страницу</a>
	</div>

	<div id="main_list">

		{if $blocks}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $blocks as $block}
						<div class="{if !$block->enabled}enabled_off{/if} list_row">

							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$block->id}]" value="{$block->position}">
							</div>

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$block->id}" />
							</div>

							<div class="row col">
								<div class="col-12 col-sm-8">
									<a href="/admin/extension/{$extension->module}/block/{$block->id}">{$block->name}</a>
								</div>
								<div class="col-12 col-sm-4 text-end">
									<span class="round_box">{$block->id}</span>
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
							<option value="enable">Сделать видимыми</option>
							<option value="disable">Сделать невидимыми</option>
							<option value="delete">Удалить</option>
						</select>
					</span>
					<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
				</div>
			</form>

		{else}
			Нет страниц
		{/if}
	</div>
{/block}


{block name=body_script append}
	<script type="module">
		var csrf = "{setCSRF}";

		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Показать
				$("i.enable").click(function() {
					ajax_icon($(this), 'InfoBlock', 'enabled', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}