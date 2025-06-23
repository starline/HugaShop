{extends file='wrapper/main.tpl'}
{include file='content/parts/menu_part.tpl'}

{* Title *}
{$meta_title='Блог'}

{block name=content}

	<!-- Заголовок -->
	<div class="header_top">
		{if $keyword && $posts_count}
			<h1>{$posts_count|plural:'Нашлась':'Нашлись':'Нашлись'} {$posts_count}
				{$posts_count|plural:'запись':'записей':'записи'}</h1>
		{elseif $posts_count}
			<h1>{$posts_count} {$posts_count|plural:'запись':'записей':'записи'} в блоге</h1>
		{else}
			<h1>Нет записей</h1>
		{/if}

		<a class="add" href="/admin/post">Добавить запись</a>


		{if $posts || $keyword}
			<!-- Поиск -->
			<form method="get" id="search">
				{getCSRFInput}
				<div class="input-group">
					<input class="form-control search" type="text" name="keyword" value="{$keyword}" />
					<input class="input-group-text search_button" type="submit" value="" />
				</div>
			</form>
		{/if}
	</div>

	<!-- Статьи -->
	<div id="main_list">
		{if $posts}

			{include file='parts/pagination.tpl'}

			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list">
					{foreach $posts as $post}
						<div class="list_row {if !$post->visible}visible_off{/if}">

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$post->id}" />
							</div>

							<div class="col">
								<a href="/admin/post/{$post->id}">{$post->name}</a>
								<div class="comment_info">{$post->date|date}</div>
							</div>

							<div class="icons">
								<a class="material-icons launch" data-bs-toggle="tooltip" title="Предпросмотр в новом окне"
									href="{$config->root_url}/blog/{$post->url}" target="_blank"></a>
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
			{include file='parts/pagination.tpl'}

		{/if}
	</div>
{/block}


{block name=body_script append}
	<script type="module">
		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Скрыт/Видим
				$("i.enable").on('click', function() {
					ajax_icon($(this), 'blog', 'visible', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}