{extends 'wrapper/main.tpl'}

{block name=content}
	<!-- Хлебные крошки -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li class='home'></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item">
					<span itemprop="name">Главная</span>
					<meta itemprop="position" content="1">
				</a>
			</li>

			{$item_position = 2}
			{if $category}
				{foreach $category->path as $cat}
					<li class='arrow'>/</li>
					<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
						<a href="{'Products'|linkLang:[url => $cat->url]}" itemprop="item"><span itemprop="name">{$cat->name}</span>
							<meta itemprop="position" content="{$item_position++}">
						</a>
					</li>
				{/foreach}
			{/if}
		</ul>
	</div>

	<div class="row">

		<div class="col-lg-3" id="catalog_menu">
			{if $category}
				<ul>
					{foreach $category->path[0]->subcategories as $c}
						{if $c->visible}
							<li class="category_main {if $category->id == $c->id}selected{/if}">
								<a href="{'Products'|linkLang:[url => $c->url]}" data-category="{$c->id}">{$c->name}</a>
								{if $c->subcategories}
									<ul>
										{foreach $c->subcategories as $sc}
											<li {if $category->id == $sc->id}class="selected" {/if}>
												<a href="{'Products'|linkLang:[url => $sc->url]}" data-category="{$sc->id}">{$sc->name}</a>
											</li>
										{/foreach}
									</ul>
								{/if}
							</li>
						{/if}
					{/foreach}
				</ul>
			{/if}
		</div>


		<div class="col-lg-9 wrap_products {if !$category}wide{/if}">

			<h1>{$h1}</h1>

			{if 'product_category'|user_access AND $category->id}
				<div class="admin_edit">
					<a href="{'CategoryAdmin'|link:[id => $category->id]}" data-bs-toggle="tooltip"
						title="Редактировать категорию">Редактировать
						категорию</a>
				</div>
			{/if}


			{if $category->annotation}
				<div class="description_html">
					{$category->annotation|raw}
				</div>
			{/if}


			{* Описание бренда *}
			{if $pagination->current_page == 1}
				{$brand->description|raw}
			{/if}


			<!--Каталог товаров-->
			{if $products}

				{* Фильтр по свойствам *}
				{if $features}
					<div class="sort features">
						{foreach $features as $key=>$f}
							<div class="feature_row">
								<span class="feature_name">{$f->name}:</span>
								<a href="{url params=[$f->id=>null, page=>null]}" rel="nofollow" {if !$smarty.get.$key}class="selected"
									{/if}>Все</a>

								{foreach $f->options as $o}
									<a href="{url params=[$f->id=>$o->value, page=>null]}" rel="nofollow"
										{if $smarty.get.$key==$o->value}class="selected" {/if}>{$o->value}</a>
								{/foreach}
							</div>
						{/foreach}
					</div>
				{/if}

				{* Сортировка *}
				{if $products|count > 0}
					<div class="sort">
						Сортировать по:
						<a {if $sort=='position'} class="selected" {/if} href="{url sort=position page=null}"
							rel="nofollow">умолчанию</a>
						<a {if $sort=='price'} class="selected" {/if} href="{url sort=price page=null}" rel="nofollow">цене</a>
					</div>
				{/if}

				<ul class="products gallerywide catalog">
					{foreach $products as $product}
						{include file='parts/product_item.tpl'}
					{/foreach}
				</ul>

				{include file='parts/pagination.tpl'}

			{else}
				Товары не найдены
			{/if}

			{if $category->description AND $show_description}
				<div class="description_html description_html">
					{$category->description|raw}
				</div>
			{/if}

		</div>
	</div>
{/block}