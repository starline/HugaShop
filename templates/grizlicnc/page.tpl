{extends 'wrapper/main.tpl'}

{block name=content}

	<!-- Breadcrumbs -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|urll}" itemprop="item">
					<span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1" />
				</a> →
			</li>

			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">{$page->h1}</span>
				<meta itemprop="position" content="2" />
			</li>
		</ul>
	</div>

	<div class="row">
		<div class="col-lg-3" id="catalog_menu">
			<ul>
				<li class="category_main">
					<a class="{if $route|in_array:[Blog, Post]}selected{/if}" href="/blog">База знаний</a>
				</li>
				{foreach 'ContentPage'|api:getList:[[visible => 1], position] as $pm}
					<li class="category_main">
						<a class="{if (!$page|empty && $page->id == $pm->id)}selected{/if}" href="{'Page'|urll:[url => $pm->url]}">{$pm->name}</a>
					</li>
				{/foreach}
			</ul>
		</div>

		<div class="col-lg-9">
			<h1>{$page->h1}</h1>

			{if 'page'|user_access AND $page->id}
				<div class="admin_edit">
					<a href="{'PageAdmin'|urll:[id=>$page->id]}" data-bs-toggle="tooltip"
						title="{'Редактировать страницу'|trans}">
						{'Редактировать страницу'|trans}
					</a>
				</div>
			{/if}

			<!-- Page Body -->
			<div class="description_html">
				{$page->body|raw}
			</div>
		</div>
	</div>
{/block}