{block name=tabs}

	{if 'settings'|user_access and $route|in_array:[SettingsAdmin]}
		<li class="mini active">
			<span>Настройки</span>
		</li>
	{/if}

	{if 'design'|user_access and $route|in_array:[BackupAdmin]}
		<li class="mini active">
			<span>Бекап</span>
		</li>
	{/if}

	{if 'design'|user_access and $route|in_array:[ImagesAdmin, ThemeAdmin, StylesAdmin, TemplatesAdmin, ThemeAdmin]}
		<li class="mini {if $route == 'ThemeAdmin'}active{/if}">
			<a href="{'ThemeAdmin'|urll}">Тема</a>
		</li>

		<li class="mini right {if $route == 'ImagesAdmin'}active{/if}">
			<a href="/admin/images">Изображения</a>
		</li>

		<li class="mini right {if $route == 'StylesAdmin'}active{/if}">
			<a href="/admin/styles">Стили</a>
		</li>

		<li class="mini right {if $route == 'TemplatesAdmin'}active{/if}">
			<a href="/admin/templates">Шаблоны</a>
		</li>
	{/if}

{/block}