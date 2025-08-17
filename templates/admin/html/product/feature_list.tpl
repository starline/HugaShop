{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{$meta_title='Характеристики товаров'}

{block name=content}

	<div class="two_columns_list">

		<!-- Заголовок -->
<div class="header_top">
<h1>{$meta_title}</h1>
<a class="add" href="{'FeatureNewAdmin'|link}">Добавить свойство</a>
</div>

		<!-- Меню -->
		<div class="navbar-expand-lg" id="right_menu">

			<div class="popup_menu_btn navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#filter_menu_block">
				<span class="material-icons">menu</span>
				<span class="popup_btn_text">Фильтр</span>
			</div>

			<div class="offcanvas offcanvas-start" id="filter_menu_block" tabindex="-1" aria-labelledby="offcanvasLabel">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="offcanvasLabel"></h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>

				<div class="offcanvas-body">

					<!-- Категории товаров -->
					{include file='parts/categories_tree_part.tpl'}

					<!-- Фильтры -->
					<ul class="menu_list layer">
						<li {if !$filter}class="selected" {/if}>
							<a href="{url page=null filter=null}">Все характеристики</a>
						</li>
						<li {if $filter == 'in_filter'}class="selected" {/if}>
							<a href="{url page=null filter='in_filter'}">Используется в фильтре</a>
						</li>
					</ul>
				</div>
			</div>
		</div>


		<div id="main_list">
			{if $features}

				{include file='parts/pagination.tpl'}

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list sortable_on">
						{foreach $features as $feature}
							<div class="list_row {if !$feature->in_filter}in_filter_off{/if}" item_id="{$feature->id}">

								<div class="move">
									<div class="move_zone"></div>
									<input type="hidden" name="positions[{$feature->id}]" value="{$feature->position}">
								</div>

								<div class="checkbox">
									<input class="form-check-input" type="checkbox" name="check[]" value="{$feature->id}" />
								</div>

<div class="col">
<a href="{'FeatureAdmin'|link:[id => $feature->id]}">{$feature->name}</a>
</div>

								<div class="col-2">
									{if $feature->index}
										<span class="badge text-bg-round">index</span>
									{/if}
								</div>

								<div class="icons">
									<a title="Использовать в фильтре" class="in_filter" href='#'></a>
									<i class="delete material-icons" title="Удалить">cancel</i>
								</div>
							</div>
						{/foreach}
					</div>

					<div id="action">
						<span id="check_all" class="dash_link">Выбрать все</span>
						<span id="select">
							<select class="form-select" name="action">
								<option value="">Выбрать действие</option>
								<option value="set_in_filter">Использовать в фильтре</option>
								<option value="unset_in_filter">Не использовать в фильтре</option>
								<option value="delete">Удалить</option>
							</select>
						</span>
						<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
					</div>
				</form>

				{include file='parts/pagination.tpl'}

			{else}
				Нет свойств
			{/if}
		</div>
	</div>
{/block}


{block name=body_script append}
	<script type="module">
		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Указать "в фильтре"/"не в фильтре"
				$("a.in_filter").click(function() {
					ajax_icon($(this), 'feature', 'in_filter', csrf);
					return false;
				});

			});
		{/literal}
	</script>
{/block}