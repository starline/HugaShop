{block name=subtabs}

	{if $current_user->manager}
		<ul id="submenu" class="submenu">
			{if 'user_settings'|user_access and $current_user->id}
				<li {if $route == 'UserAdmin'}class="active" {/if}>
					<a href="/admin/user/{$current_user->id}">Информация</a>
				</li>
			{/if}

			{if 'user_settings'|user_access and $current_user->id}
				<li {if $route == 'UserSettingsAdmin'}class="active" {/if}>
					<a href="/admin/user/{$current_user->id}/settings">Настройки сотрудника</a>
				</li>
			{/if}

			{if $smarty.get.return}
				<li class="back">
					<a class="out_link" href="{$smarty.get.return}">Назад</a>
				</li>
			{/if}

		</ul>
	{/if}

{/block}