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
				<a href="/blog" itemprop="item">
					<span itemprop="name">Все статьи</span>
					<meta itemprop="position" content="2" />
				</a>
			</li>
			<li class='arrow'>/</li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">{$post->name}</span>
				<meta itemprop="position" content="3" />
			</li>
		</ul>
	</div>


	<!-- Заголовок /-->
	<h1 data-post="{$post->id}">{$post->name}</h1>

	{if 'blog'|user_access AND $post->id}
		<div class="admin_edit">
			<a href="/admin/post/{$post->id}" data-bs-toggle="tooltip" title="Редактировать статью">Редактировать статью</a>
		</div>
	{/if}

	<p>{$post->date|date}</p>

	<!-- Тело поста /-->
	<div class="description_html">
		{$post->body|raw}
	</div>

	<!-- Соседние записи /-->
	<div id="back_forward">
		{if $prev_post}
			←&nbsp;<a class="prev_page_link" href="/blog/{$prev_post->url}">{$prev_post->name}</a>
		{/if}
		{if $next_post}
			<a class="next_page_link" href="/blog/{$next_post->url}">{$next_post->name}</a>&nbsp;→
		{/if}
	</div>

	{include file='parts/comments.tpl'}
{/block}