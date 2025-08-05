{extends 'wrapper/main.tpl'}

{block name=content}

	<!-- Breadcrumbs -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">

			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item">
					<span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1">
				</a>
			</li>

			{$item_position = 2}
			{if $category}
				{foreach $category->path as $cat}
					<li>→</li>
					<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
						<a href="{'Products'|linkLang:[url => $cat->url]}" itemprop="item">
							<span itemprop="name">{$cat->name}</span>
							<meta itemprop="position" content="{$item_position++}">
						</a>
					</li>
				{/foreach}
			{/if}
		</ul>
	</div>


	<div class="row">
		<div class="col-lg-3" id="catalog_menu">
			<div class="offcanvas-lg offcanvas-start" tabindex="-1" id="bdSidebar"
				aria-labelledby="bdSidebarOffcanvasLabel">

				<div class="offcanvas-header border-bottom">
					<h5 class="offcanvas-title" id="bdSidebarOffcanvasLabel">{$category->name}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"
						data-bs-target="#bdSidebar"></button>
				</div>

				<div class="offcanvas-body">

					<ul>
						{if !$category|empty}
							{foreach $category->path[0]->subcategories as $c}
								<li class="category_main">
									<a {if $category->id == $c->id}class="selected" {/if}
										href="{'Products'|linkLang:[url => $c->url]}" data-category="{$c->id}">{$c->name}</a>
									{if $c->subcategories}
										<ul>
											{foreach $c->subcategories as $sc}
												<li>
													<a {if $category->id == $sc->id}class="selected" {/if} href="{$sc->url}"
														data-category="{$sc->id}">{$sc->name}</a>
												</li>
											{/foreach}
										</ul>
									{/if}
								</li>
							{/foreach}
						{/if}
					</ul>
				</div>

			</div>
		</div>

		<nav class="d-lg-none my-4" aria-label="Main navigation">
			<button class="navbar-toggler text-bg-secondary p-2" type="button" data-bs-toggle="offcanvas"
				data-bs-target="#bdSidebar" aria-controls="bdSidebar" aria-label="Toggle docs navigation">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="bi" fill="currentColor"
					viewBox="0 0 16 16">
					<path fill-rule="evenodd"
						d="M2.5 11.5A.5.5 0 0 1 3 11h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 7h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 3h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z">
					</path>
				</svg>

				<span class="fs-6 pe-1">Категории товаров</span>
			</button>
		</nav>

		<div class="col-lg-9 wrap_products">

			<h1>{$h1}</h1>

			{if 'product_category'|user_access AND $category->id}
				<div class="admin_edit">
					<a href="{'CategoryAdmin'|link:[id => $category->id]}" data-bs-toggle="tooltip"
						title="{'Редактировать категорию'|trans}">{'Редактировать категорию'|trans}</a>
				</div>
			{/if}


			{if $category->annotation}
				<div class="description_html category_annotation">
					{$category->annotation|raw}
				</div>
			{/if}


			<!-- Products -->
			{if $products}

				{* Features filter *}
				{if $features}
					<table id="features">
						{foreach $features as $f}
							<tr>
								<td class="feature_name" data-feature="{$f->id}">
									{$f->name}:
								</td>
								<td class="feature_values">
									<span class="option">
										<a href="{url params=[$f->url=>null, page=>null]}" {if !$smarty.get.{$f->url}}class="selected"
											{/if} rel="nofollow">Все</a>
									</span>
									{foreach $f->options as $o}
										<span class="option">
											<a href="{url params=[$f->url=>$o->url, page=>null]}"
												class="{if $smarty.get.{$f->url} ==$o->url}selected{/if} {if $o->disabled}disabled{/if}"
												{if !$f->index}rel="nofollow" {/if}>{$o->value}</a>
											<span class="product_count">{$o->product_count}</span>
										</span>
									{/foreach}
								</td>
							</tr>
						{/foreach}
					</table>
				{/if}


				{* Product sort *}
				{if $products|count > 0}
					<div class="sort">
						{'Сортировать по'|trans}
						<a {if $sort=='position'} class="selected" {/if} href="{url sort=null page=null}"
							rel="nofollow">{'умолчанию'|trans}</a>
						<a {if $sort=='price'} class="selected" {/if} href="{url sort=price page=null}"
							rel="nofollow">{'цене'|trans}</a>
					</div>
				{/if}

				<ul class="product_list products gallerywide catalog" list_id="{$category->url}" list_name="{$category->name}">
					{foreach $products as $product}
						{include file='parts/product_item.tpl'}
					{/foreach}
				</ul>

				{include file="parts/pagination.tpl"}

			{else}
				{'Товары не найдены'|trans}
			{/if}

			{if $category->description AND $show_description}
				<div class="description_html category_description">
					{$category->description|raw}
				</div>
			{/if}

		</div>
	</div>
{/block}