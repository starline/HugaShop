{extends 'wrapper/main.tpl'}

{block name=content}
	<!-- Хлебные крошки -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li class='home'></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <a href="{'Main'|linkLang}" itemprop="item">
					<span itemprop="name">Главная</span>
					<meta itemprop="position" content="1" />
				</a>
			</li>
			<li class='arrow'>/</li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">Все статьи</span>
				<meta itemprop="position" content="2" />
			</li>
		</ul>
	</div>

	<h1>{$seo->h1}</h1>

	<div class="blog">
		{foreach $posts as $post}
			<div>
				<div class="date">{$post->date|date}</div>
				<h3>
                                        <a data-post="{$post->id}" href="{'Post'|linkLang:[url => $post->url]}">{$post->name}</a>
				</h3>
				<p>{$post->annotation}</p>
			</div>
		{/foreach}
	</div>

	{include file='parts/pagination.tpl'}

{/block}