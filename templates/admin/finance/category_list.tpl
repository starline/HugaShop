{extends file='wrapper/main.tpl'}
{include file='finance/parts/menu_part.tpl'}

{$meta_title='Категории платежей'}

{block name=content}

	<div class="header_top">
		{if $categories_count>0}
			<h1>{$categories_count} {$categories_count|plural:'категория':'категорий':'категории'} платежей</h1>
		{else}
			<h1>Нет категорий платежей</h1>
		{/if}

		<a class="add" href="/admin/finance/category">Добавить категорию платежей</a>
	</div>

	{if $categories}
		<div id="main_list" class="finance">
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list sortable_on">
					{foreach $categories as $c}
						<div class="list_row" item_id="{$c->id}">

							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$c->id}]" value="{$c->position}">
							</div>

							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$c->id}" />
							</div>

							<div class="col row">
								<div class="col-12 col-sm-10">
									<a href="/admin/finance/category/{$c->id}">{$c->name}</a>
									<div class="notice">{$c->comment|strip_tags|nl2br|raw}</div>
								</div>

								<div class="col-12 col-sm-2 detail">
									{if $c->type == 1}
										Приход
									{else}
										Расход
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

		</div>
	{/if}

{/block}