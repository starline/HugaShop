{extends 'wrapper/main.tpl'}
{include 'warehouse/parts/menu_part.tpl'}

{$meta_title='Поставщики'}

{block name=content}

	{* Заголовок *}
	<div class="header_top">
		<h1>Поставщики</h1>
		<a class="add" href="/admin/warehouse/provider">Добавить
			поставщика</a>
	</div>


	<div id="main_list">

		{if $providers}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list">
					{foreach $providers as $provider}
						<div class="list_row" item_id="{$provider->id}">
							<div class="checkbox">
								<input class="form-check-input" type="checkbox" name="check[]" value="{$provider->id}" />
							</div>
							<div class="col">
								<a href="{'ProviderAdmin'|link:[id=>$provider->id]}">{$provider->name}</a>
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
		{else}
			Еще нет постащиков
		{/if}

	</div>

{/block}