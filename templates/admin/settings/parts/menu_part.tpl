{block name=tabs}

	{if 'settings'|user_access and $route|in_array:[SettingsAdmin]}
		<li class="mini active">
			<a href="{'SettingsAdmin'|urll}">Настройки</a>
		</li>
	{/if}

	{if 'design'|user_access and $route|in_array:[BackupAdmin]}
		<li class="mini active">
			<a href="{'BackupAdmin'|urll}">Бекап</a>
		</li>
	{/if}

	{if 'settings'|user_access and $route|in_array:[LanguageListAdmin, LanguageAdmin, LanguageNewAdmin]}
		<li class="mini active">
			<a href="{'LanguageListAdmin'|urll}">Языки</a>
		</li>
	{/if}

	{if 'design'|user_access and $route|in_array:[ImagesAdmin, ThemeAdmin, StylesAdmin, TemplatesAdmin, ThemeAdmin]}
		<li class="mini {if $route == 'ThemeAdmin'}active{/if}">
			<a href="{'ThemeAdmin'|urll}">Тема</a>
		</li>

		<li class="mini right {if $route == 'ImagesAdmin'}active{/if}">
			<a href="{'ImagesAdmin'|urll}">Изображения</a>
		</li>

		<li class="mini right {if $route == 'StylesAdmin'}active{/if}">
			<a href="{'StylesAdmin'|urll}">Стили</a>
		</li>

		<li class="mini right {if $route == 'TemplatesAdmin'}active{/if}">
			<a href="{'TemplatesAdmin'|urll}">Шаблоны</a>
		</li>
	{/if}

{/block}