{extends 'wrapper/main.tpl'}
{include 'content/parts/menu_part.tpl'}

{* Title *}
{$meta_title = 'Страницы'}

{block name=content}

	<div class="header_top">
		<h1>Страницы</h1>
		<a class="add" href="/admin/page">Добавить страницу</a>
	</div>


	<div id="main_list">
		{if $pages}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $pages as $page}
						<div class="list_row {if !$page->visible}visible_off{/if} {if !$page->menu}menu_off{/if}">
							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$page->id}]" value="{$page->position}">
							</div>
							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$page->id}" />
							</div>

							<div class="col row">
								<div class="col-12 col-sm-10">
									<a href="/admin/page/{$page->id}">{$page->name}</a>
								</div>
								<div class="col-12 col-sm-2 text-end">
									{if $page->menu}
										<div class="badge text-bg-round">меню</div>
									{/if}
								</div>
							</div>

							<div class="icons">
								<a class="material-icons launch" data-bs-toggle="tooltip" title="Предпросмотр в новом окне"
									href="{$config->root_url}/info/{$page->url}" target="_blank"></a>
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
		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Показать
				$("i.enable").click(function() {
					ajax_icon($(this), 'page', 'visible', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}