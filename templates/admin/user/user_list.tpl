{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{$meta_title='Покупатели'}

{block name=content}

	<div class="two_columns_list">

		<div class="header_top">
			{if $keyword && $users_count > 0}
				<h1>{$users_count|plural:'Нашелся':'Нашлось':'Нашлись'} {$users_count}
					{$users_count|plural:'покупатель':'покупателей':'покупателя'}</h1>
			{elseif $users_count > 0}
				<h1>{$users_count} {$users_count|plural:'покупатель':'покупателей':'покупателя'}</h1>
			{else}
				<h1>Нет покупателей</h1>
			{/if}

			{if $users_count > 0 and 'export'|user_access}
				<form class="export_btn" method="post" action="/admin/users/export?{$smarty.server.QUERY_STRING}"
					target="_blank">
					<input type="image" src="{'images/export_excel.png'|asset}" name="export" data-bs-toggle="tooltip"
						title="Экспортировать этих покупателей" />
				</form>
			{/if}


			<form method="get" id="search">
				{getCSRFInput}
				<div class="input-group">
					<input class="search form-control" type="text" name="keyword" value="{$keyword}"
						placeholder="Имя, фамилия, телефон, email" />
					<input class="input-group-text search_button" type="submit" value="" />
				</div>
			</form>
		</div>


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
					{if $groups}
						<ul class="menu_list">
							<li class="{if !$group->id AND !$manager}selected{/if}">
								<a href="{url clear=true}">Показать всех</a>
							</li>
							{foreach $groups as $g}
								<li class="{if $group->id == $g->id}selected{/if}">
									<a href="{url group_id=$g->id clear=true}">{$g->name}</a>
								</li>
							{/foreach}
						</ul>
					{/if}

					{if 'user_manager'|user_access}
						<ul class="menu_list layer">
							<li class="{if $manager==1}selected{/if}">
								<a href="{url manager=1 clear=true}">Сотрудники</a>
							</li>
						</ul>
					{/if}
				</div>
			</div>
		</div>



		<div id="main_list">
			{if $users}

				<div class="list_top_row">
					{if !$pagination_hide}
						{include file='parts/pagination.tpl'}
					{elseif ($settings->products_num_admin <= $users_count)}
						<div class="pagination">Показано только первые {$settings->products_num_admin} покупателей</div>
					{/if}

					<div id="sort_links">
						Упорядочить по
						{if $sort!='name'}<a href="{url sort=name}">имени</a>{else}<b>Имени</b>{/if} или
						{if $sort!='date'}<a href="{url sort=date}">дате</a>{else}<b>Дате</b>{/if}
					</div>
				</div>

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list">
						{foreach $users as $u}
							<div class="list_row {if !$u->enabled}enabled_off{/if}" item_id="{$u->id}">

								<div class="checkbox">
									<input class="form-check-input" type="checkbox" name="check[]" value="{$u->id}"
										{if !'user_edit'|user_access}disabled{/if} />
								</div>

								<div class="col row">
									<div class="col-12 col-sm-4">
										<a href="/admin/user/{$u->id}">{if $u->name}{$u->name}{else}-{/if}</a>
										<div class="notice">{$groups[$u->group->id]->name}</div>
									</div>

									<div class="col-12 col-sm-4">
										{$u->phone|replace:',':' '}
									</div>

									<div class="col-12 col-sm-4">
										<a class="detail" href="mailto:{$u->name}<{$u->email}>">{$u->email}</a>
									</div>
								</div>

								<div class="icons">
									<i class="enable {if 'user_edit'|user_access}edit{/if} material-icons visibility"
										data-bs-toggle="tooltip" title="{if $u->enabled}Активен{else}Заблокирован{/if}"></i>
									{if 'user_delete'|user_access}
										<i class="delete material-icons" title="Удалить">cancel</i>
									{/if}
								</div>

							</div>
						{/foreach}
					</div>

					{if 'user_edit'|user_access}
						<div id="action">
							<span id="check_all" class="dash_link">Выбрать все</span>
							<span id="select">
								<select class="form-select" name="action">
									<option value="">Выбрать действие</option>
									<option value="disable">Заблокировать</option>
									<option value="enable">Разблокировать</option>
									{if 'user_delete'|user_access}
										<option value="delete">Удалить</option>
									{/if}
								</select>
							</span>
							<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
						</div>
					{/if}

				</form>

				{if !$pagination_hide}
					{include file='parts/pagination.tpl'}
				{/if}

			{/if}
		</div>

	</div>
{/block}


{block name=body_script append}
	<script type="module">
		import { ajax_icon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Скрыт/Видим
				$("i.enable.edit").on('click', function() {
					ajax_icon($(this), 'user', 'enabled', csrf);
					return false;
				});
			});
		{/literal}
	</script>
{/block}