{block name=tabs}

	{if 'settings'|user_access and $route|in_array:[SettingsAdmin]}
		<li class="mini active">
			<a href="{'SettingsAdmin'|link}">Настройки</a>
		</li>
	{/if}

	{if 'design'|user_access and $route|in_array:[BackupAdmin]}
		<li class="mini active">
			<a href="{'BackupAdmin'|link}">Бекап</a>
		</li>
	{/if}

	{if 'settings'|user_access and $route|in_array:[LanguageListAdmin, LanguageAdmin, LanguageNewAdmin]}
		<li class="mini active">
			<a href="{'LanguageListAdmin'|link}">Языки</a>
		</li>
	{/if}

	{if 'design'|user_access and $route|in_array:[ImagesAdmin, ThemeAdmin, StylesAdmin, TemplatesAdmin, ThemeAdmin]}
		<li class="mini {if $route == 'ThemeAdmin'}active{/if}">
			<a href="{'ThemeAdmin'|link}">Тема</a>
		</li>

		<li class="mini right {if $route == 'ImagesAdmin'}active{/if}">
			<a href="{'ImagesAdmin'|link}">Изображения</a>
		</li>

		<li class="mini right {if $route == 'StylesAdmin'}active{/if}">
			<a href="{'StylesAdmin'|link}">Стили</a>
		</li>

		<li class="mini right {if $route == 'TemplatesAdmin'}active{/if}">
			<a href="{'TemplatesAdmin'|link}">Шаблоны</a>
		</li>
	{/if}

{/block}