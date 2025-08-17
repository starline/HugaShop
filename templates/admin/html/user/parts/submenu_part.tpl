{block name=subtabs}

	{if $current_user->manager}
		<ul id="submenu" class="submenu">
			{if 'user_settings'|user_access and $current_user->id}
				<li {if $route == 'UserAdmin'}class="active" {/if}>
                                        <a href="{'UserAdmin'|link:[id => $current_user->id]}">Информация</a>
				</li>
			{/if}

			{if 'user_settings'|user_access and $current_user->id}
				<li {if $route == 'UserSettingsAdmin'}class="active" {/if}>
                                        <a href="{'UserSettingsAdmin'|link:[id => $current_user->id]}">Настройки сотрудника</a>
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
