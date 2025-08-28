{extends file='wrapper/main.tpl'}
{include file='content/parts/menu_part.tpl'}

{$meta_title='Комментарии'}

{block name=content}

	<div class="two_columns_list">

		<!-- Заголовок -->
		<div class="header_top">
			{if $keyword && $comments_count}
				<h1>{$comments_count|plural:'Нашелся':'Нашлось':'Нашлись'} {$comments_count}
					{$comments_count|plural:'комментарий':'комментариев':'комментария'}</h1>
			{elseif !$type}
				<h1>{$comments_count} {$comments_count|plural:'комментарий':'комментариев':'комментария'}</h1>
			{elseif $type=='product'}
				<h1>{$comments_count} {$comments_count|plural:'комментарий':'комментариев':'комментария'} к товарам</h1>
			{elseif $type=='blog'}
				<h1>{$comments_count} {$comments_count|plural:'комментарий':'комментариев':'комментария'} к записям в блоге
				</h1>
			{/if}

			{if $comments || $keyword}
				<!-- Search -->
				<form method="get" id="search">
					<div class="input-group">
						<input class="search form-control" type="text" name="keyword" value="{$keyword}" />
						<input class="input-group-text search_button" type="submit" value="" />
					</div>
				</form>
			{/if}
		</div>

		<!-- Меню -->
		<div id="right_menu">
			<ul class="menu_list">
				<li {if !$type}class="selected" {/if}>
					<a href="{url type=null}">Все комментарии</a>
				</li>
				<li {if $type == 'product'}class="selected" {/if}>
					<a href='{url keyword=null type=product}'>К товарам</a>
				</li>
				<li {if $type == 'blog'}class="selected" {/if}>
					<a href='{url keyword=null type=blog}'>К блогу</a>
				</li>
			</ul>
		</div>


		<div id="main_list">
			{if $comments}

				{include file='parts/pagination.tpl'}

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list">
						{foreach $comments as $comment}
							<div class="list_row {if !$comment->approved}approved_off{/if}">
								<div class="checkbox">
									<input class="form-check-input" type="checkbox" name="check[]" value="{$comment->id}" />
								</div>
								<div class="col">
									<div class="mb-2">
										<a href="{'CommentAdmin'|link:[id => $comment->id]}">{$comment->name}</a>
										<span class="badge text-bg-round">IP: {$comment->ip}</span>
										{if !$comment->approved}<a class="approve" href="#">Одобрить</a>{/if}
									</div>

									<div class="comment_text">
										{$comment->text|strip_tags|nl2br|raw}
									</div>

									<div class="comment_info">
										Комментарий оставлен {$comment->date|date} в {$comment->date|time}

										{if $comment->type == 'product'}
											к товару <a target="_blank"
												href="{'ProductShortId'|linkLang:[id => $comment->product->id]}#comment_{$comment->id}">{$comment->product->name}</a>
										{elseif $comment->type == 'blog'}
											к статье <a target="_blank"
												href="{'Post'|linkLang:[url => $comment->post->url]}#comment_{$comment->id}">{$comment->post->name}</a>
										{/if}
									</div>
								</div>
								<div class="icons">
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
								</div>
							</div>
						{/foreach}
					</div>

					<div id="action">
						Выбрать
						<span id="check_all" class="dash_link">все</span> или <span id="check_unapproved"
							class="dash_link">ожидающие</span>

						<span id="select">
							<select class="form-select" name="action">
								<option value="">Выбрать действие</option>
								<option value="approve">Одобрить</option>
								<option value="delete">Удалить</option>
							</select>
						</span>
						{include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
					</div>
				</form>

				{include file='parts/pagination.tpl'}

			{else}
				Нет комментариев
			{/if}
		</div>
	</div>
{/block}


{block name=body_script append}
	<script type="module">
		import { ajaxEntityUpdateIcon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Выделить ожидающие
				$("#check_unapproved").click(function() {
					$('.list input[type="checkbox"][name*="check"]').prop('checked', false);
					$('.list .unapproved input[type="checkbox"][name*="check"]').prop('checked', true);
				});

				// Одобрить
				$("a.approve").click(function() {
					ajaxEntityUpdateIcon($(this), 'comment', 'approved', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}