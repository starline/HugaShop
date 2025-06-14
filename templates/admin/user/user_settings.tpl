{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}
{include 'user/parts/submenu_part.tpl'}

{if $current_user->id}
	{$meta_title = $current_user->name}
{/if}

{block name=content}

	<!-- Основная форма -->
	<form method="post">
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">

					</div>
				</div>
				<div class="name_row">
					<input name="id" type="hidden" value="{$current_user->id}" />
					<input class="form-control form-control-lg" name="name" type="text" value="{$current_user->name}"
						autocomplete="off" disabled />
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Данные пользователя</h2>
				<ul class="property_block">
					{if !$groups|empty}
						<li>
							<label class="col-form-label" for="group_id">Группа</label>
							<select class="form-select" id="group_id" name="group_id">
								<option value="">Не входит в группу</option>
								{foreach $groups as $g}
									<option value='{$g->id}' {if $current_user->group->id == $g->id}selected{/if}>{$g->name}
									</option>
								{/foreach}
							</select>
						</li>
					{/if}
				</ul>

				<div class="btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Права пользователя</h2>
				<select class="form-select multiple_categories" multiple name="permissions[]">
					{foreach $permissions_list as $value=>$name}
						<option value="{$value}" {if in_array($value, $permissions)}selected{/if}>{$name}</option>
					{/foreach}
				</select>
			</div>

			<div class="col-lg-6 layer">
				<h2>Настройки оповещений</h2>
				<ul class="property_block">
					{foreach $notifier_methods as $method}
						<li>
							<label for="{$method->module}" class="col-form-label">{$method->name}</label>
							<select class="form-select" name="user_notifier_types[{$method->id}][]" multiple
								id="{$method->module}">
								{foreach $notifier_types as $type_name => $type_description}
									<option value='{$type_name}'
										{if in_array($type_name, $user_notifier_types[$method->id])}selected{/if}>
										{$type_description}
									</option>
								{/foreach}
							</select>
						</li>
					{/foreach}
				</ul>

				<div class="col-12 btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>
		</div>
	</form>

{/block}