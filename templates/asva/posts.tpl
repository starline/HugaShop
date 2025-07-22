{extends 'wrapper/main.tpl'}

{block name=content}
	<!-- Хлебные крошки -->
	<div class="breadcrumbs-wrapper">
		<ul class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item">
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
			{include file='parts/menu_part.tpl'}
		</div>

		<div class="col-lg-9">
			<h1>{$seo->h1}</h1>

			<div class="blog">
				{foreach $posts as $post}
					<div>
						<h3>
							<a data-post="{$post->id}" href="{'Post'|linkLang:[url => $post->url]}">{$post->name}</a>
						</h3>
						<div class="date">{$post->date|date}</div>
						<p>{$post->annotation|strip_tags}</p>
					</div>
				{/foreach}
			</div>

			{include file='parts/pagination.tpl'}
		</div>
{/block}