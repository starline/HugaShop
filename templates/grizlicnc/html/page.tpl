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
				<span itemprop="name">{$page->h1}</span>
				<meta itemprop="position" content="2" />
			</li>
		</ul>
	</div>

	<div class="row">
		<div class="col-lg-3" id="catalog_menu">
			{include file='parts/menu_part.tpl'}
		</div>

		<div class="col-lg-9 position-relative">
			<h1>{$page->h1}
				{if 'page'|user_access AND $page->id}
					{include file='parts/btn_edit.tpl' btn_edit_link={'PageAdmin'|linkLang:[id => $page->id]}}
				{/if}
			</h1>

			<!-- Page Body -->
			<div class="description_html">
				{$page->body|raw}
			</div>
		</div>
	</div>
{/block}