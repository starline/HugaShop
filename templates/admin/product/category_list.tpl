{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{$meta_title='Категории товаров'}

{block name=content}

	{* Заголовок *}
	<div class="header_top">
		<h1>{$meta_title}</h1>
		<a class="add" href="/admin/product/category">Добавить
			категорию</a>
	</div>


	<div id="main_list">
		{if $categories}

			<form class="list_form" method="post">
				{getCSRFInput}

				{function name=categories_tree level=0}
					{if $categories}
						<div class="list sortable">
							{foreach $categories as $category}
								<div class="tree_row">
									<div class="list_row {if !$category->visible}visible_off{/if}" item_id="{$category->id}">
										<input type="hidden" name="positions[{$category->id}]" value="{$category->position}">

										<div class="move" style="margin-left:{$level*18}px">
											<div class="move_zone"></div>
										</div>

										<div class="checkbox">
											<input class="form-check-input" type="checkbox" name="check[]" value="{$category->id}" />
										</div>

										<div class="col row">
											<div class="col-12 col-md-10 {if $category->level == 1}fw-semibold{/if}">
												<a href="/admin/product/category/{$category->id}">{$category->name}</a>
											</div>
											<div class="col-12 col-md-2 text-end">
												{if $category->main}
													<span class="badge text-bg-round">На главной</span>
												{/if}
											</div>
										</div>

										<div class="icons">
											<a class="material-icons launch" title="Предпросмотр в новом окне"
												href="{$config->root_url}/{$category->url}"></a>
											<i class="enable material-icons visibility" title="Активна"></i>
											<i class="delete material-icons" title="Удалить">cancel</i>
										</div>
									</div>

									{categories_tree categories=$category->subcategories level=$level+1}
								</div>
							{/foreach}
						</div>
					{/if}
				{/function}

				{categories_tree categories=$categories}

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
			Нет категорий
		{/if}
	</div>
{/block}


{block name=body_script append}
	<script type="module">
		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Сортировка списка
				$(".sortable").sortable({
					items: ".tree_row",
					handle: ".move_zone",
					cancel: ".sortable_off",
					tolerance: "pointer",
					opacity: 0.90,
					axis: "y",
					update: function() {
						$("form input[name*='check']").prop('checked', false);
						$("form").ajaxSubmit();
					}
				});


				// Скрыт/Видим
				$("i.enable").click(function() {
					ajax_icon($(this), 'category', 'visible', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}