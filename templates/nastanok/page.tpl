{extends 'wrapper/main.tpl'}

{block name=content}
	<!-- Хлебные крошки -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li class='home'></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="/" itemprop="item">
					<span itemprop="name">Главная</span>
					<meta itemprop="position" content="1" />
				</a>
			</li>
			<li class='arrow'>/</li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">{$page->meta_title}</span>
				<meta itemprop="position" content="2" />
			</li>
		</ul>
	</div>

	<!-- Заголовок страницы -->
	<h1 data-page="{$page->id}">{$meta_title}</h1>

	{if 'page'|user_access AND $page->id}
		<div class="admin_edit">
			<a href="/admin/page/{$page->id}" data-bs-toggle="tooltip" title="Редактировать страницу">Редактировать
				страницу</a>
		</div>
	{/if}

	<!-- Тело страницы -->
	<div class="description_html">
		{$page->body|raw}
	</div>
{/block}