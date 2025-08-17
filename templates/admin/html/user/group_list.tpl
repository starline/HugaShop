{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{$meta_title='Группы пользователей'}

{block name=content}

	<div class="header_top">
		<h1>Группы пользователей</h1>
                <a class="add" href="{'GroupNewAdmin'|link}">Добавить группу</a>
	</div>


	<div id="main_list">
		<form method="post" class="list_form">
			{getCSRFInput}

			<div class="list sortable_on">
				{foreach $groups as $group}
					<div class="list_row" item_id="{$group->id}">

						{if 'user_group_edit'|user_access}
							<div class="move">
								<div class="move_zone"></div>
								<input type="hidden" name="positions[{$group->id}]" value="{$group->position}">
							</div>
						{/if}

						<div class="checkbox">
							<input class="form-check-input" type="checkbox" name="check[]" value="{$group->id}"
								{if !'user_group_delete'|user_access}disabled{/if} />
						</div>

						<div class="col row">
							<div class="col-12 col-sm-10 name">
                                                                <a href="{'GroupAdmin'|link:[id => $group->id]}">{$group->name}</a>
							</div>

							<div class="col-12 col-sm-2 text-end">
								<span class="badge text-bg-round">
									{$group->discount} %
								</span>
							</div>
						</div>

						{if 'user_group_delete'|user_access}
							<div class="icons">
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						{/if}

					</div>
				{/foreach}
			</div>

			{if 'user_group_delete'|user_access}
				<div id="action">
					<span id="check_all" class="dash_link">Выбрать все</span>
					<span id=select>
						<select class="form-select" name="action">
							<option value="">Выбрать действие</option>
							<option value="delete">Удалить</option>
						</select>
					</span>
					<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
				</div>
			{/if}

		</form>
	</div>

{/block}
