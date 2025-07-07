{extends 'wrapper/main.tpl'}

{block name=content}

	<!-- Breadcrumbs -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item">
					<span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1" />
				</a> →
			</li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'PostList'|linkLang}" itemprop="item">
					<span itemprop="name">{'Все статьи'|trans}</span>
					<meta itemprop="position" content="2" />
				</a> →
			</li>

			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">{$post->name}</span>
				<meta itemprop="position" content="3" />
			</li>
		</ul>
	</div>

	<div class="row">
		<div class="col-lg-3" id="catalog_menu">
			<ul>
				<li class="category_main">
					<a class="{if $route|in_array:[PostList, Post]}selected{/if}" href="{'PostList'|linkLang}">База знаний</a>
				</li>
				{foreach 'ContentPage'|api:getList:[[visible => 1], position] as $pm}
					<li class="category_main">
						<a class="{if (!$page|empty && $page->id == $pm->id)}selected{/if}"
							href="{'Page'|linkLang:[url => $pm->url]}">{$pm->name}</a>
					</li>
				{/foreach}
			</ul>
		</div>

		<div class="col-lg-9">

			<h1>{$post->name}</h1>

			{if 'blog'|user_access AND $post->id}
				<div class="admin_edit">
					<a href="{'PostAdmin'|linkLang:[id => $post->id]}" data-bs-toggle="tooltip"
						title="{'Редактировать статью'|trans}">{'Редактировать статью'|trans}</a>
				</div>
			{/if}

			<p>{$post->date|date}</p>

			<!-- Post Body -->
			<div class="description_html">
				{$post->body|raw}
			</div>

			<!-- Соседние записи -->
			<div id="back_forward">
				{if $prev_post}
					←&nbsp;<a class="prev_page_link" href="{'Post'|linkLang:[url => $prev_post->url]}">{$prev_post->name}</a>
				{/if}
				{if $next_post}
					<a class="next_page_link" href="{'Post'|linkLang:[url => $next_post->url]}">{$next_post->name}</a>&nbsp;→
				{/if}
			</div>

			{include file='parts/comments.tpl'}
		</div>
{/block}