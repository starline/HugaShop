{extends 'wrapper/main.tpl'}

{block name=content}
	<!-- Хлебные крошки -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|urll}" itemprop="item">
					<span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1" />
				</a> →
			</li>

			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">{'Все статьи'|trans}</span>
				<meta itemprop="position" content="2" />
			</li>
		</ul>
	</div>

	<div class="row">
		<div class="col-lg-3" id="catalog_menu">
			<ul>
				<li class="category_main">
					<a class="{if $route|in_array:[PostList, Post]}selected{/if}" href="/blog">База знаний</a>
				</li>
				{foreach 'ContentPage'|api:getList:[[visible => 1], position] as $pm}
					<li class="category_main">
						<a class="{if (!$page|empty && $page->id == $pm->id)}selected{/if}"
							href="{'Page'|urll:[url => $pm->url]}">{$pm->name}</a>
					</li>
				{/foreach}
			</ul>
		</div>

		<div class="col-lg-9">
			<h1>{$seo->h1}</h1>

			<div class="blog">
				{foreach $posts as $post}
					<div>
						<h3>
							<a data-post="{$post->id}" href="{'Post'|urll:[url=>$post->url]}">{$post->name}</a>
						</h3>
						<div class="date">{$post->date|date}</div>
						<p>{$post->annotation|strip_tags}</p>
					</div>
				{/foreach}
			</div>

			{include file='parts/pagination.tpl'}
		</div>
{/block}