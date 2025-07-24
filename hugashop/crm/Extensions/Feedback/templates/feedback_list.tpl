{extends file='wrapper/main.tpl'}
{include file='extension/parts/menu_part.tpl'}

{$meta_title='Обратная связь'}

{block name=content}

	{* Заголовок *}
	<div class="header_top">
		{if $feedbacks_count}
			<h1>{$feedbacks_count} {$feedbacks_count|plural:'сообщение':'сообщений':'сообщения'}</h1>
		{else}
			<h1>Нет сообщений</h1>
		{/if}

		{if $feedbacks || $keyword}
			<!-- Search -->
			<form method="get" id="search">
				<div class="input-group">
					<input class="search form-control" type="text" name="keyword" value="{$keyword}" />
					<input class="input-group-text search_button" type="submit" value="" />
				</div>
			</form>
		{/if}
	</div>

	<div id="main_list">
		{if $feedbacks->isNotEmpty()}
			{include file='parts/pagination.tpl'}

			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list">
					{foreach $feedbacks as $feedback}
						<div class="list_row {if !$feedback->visible}visible_off{/if}">
							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$feedback->id}" />
							</div>

							<div class='col comment_text'>
								{$feedback->message|strip_tags|nl2br|raw}
							</div>

							<div class="icons">
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						</div>
					{/foreach}
				</div>

				<div id="action">
					<span id='check_all' class='dash_link'>Выбрать все</span>
					<span id=select>
						<select class="form-select" name="action">
							<option value="">Выбрать действие</option>
							<option value="delete">Удалить</option>
						</select>
					</span>
					<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
				</div>
			</form>

			{include file='parts/pagination.tpl'}
		{else}
			Нет сообщений
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
					ajax_icon($(this), 'feedback', 'visible', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}