{extends 'wrapper/main.tpl'}

{block name=content}

	{$category = 'ProductCategory'|api:getCategories:[[main => 1]]}

	{if $category_list}
		{foreach $category_list as $cat_list}
			<div class="row">
				{foreach $cat_list->subcategories as $cat}
					{if $cat->main}
						<div class="col-6 col-lg-4 category_prev_wrap"
							style="{if count($cat->subcategories) > 2}grid-row: 1/span {(count($cat->subcategories)/2)|round:0};{/if}">
							<a class="category_name" href="{'Products'|linkLang:[url => $cat->url]}" title="{$cat->name}">{$cat->name}</a>
							{foreach $cat->subcategories as $scat}
								{if $scat->images[0] AND $scat->main}
									<div class="sub_category_prev">
										<img loading="lazy" src="{$scat->images[0]->filename|resize:80:80}" alt="{$scat->name}">
										<a href="{'Products'|linkLang:[url => $scat->url]}" data-bs-toggle="tooltip"
											title="{$scat->name}">{$scat->name}</a>
									</div>
								{/if}
							{/foreach}
						</div>
					{/if}
				{/foreach}
			</div>
		{/foreach}
	{/if}

	{if $categories_products}
		{foreach $categories_products as $cat_products}
			<div class="accessories_products">
				<div class="title-main">
					<h2>{$cat_products->category->name}</h2>
					<span> → <a href="{'Products'|linkLang:[url => $cat_products->category->url]}" data-bs-toggle="tooltip"
							title="{$cat_products->category->name}">все
							{$cat_products->category->name}</a></span>
				</div>
				<ul class="products gallerywide">
					{foreach $cat_products->products as $product}
						{include file='parts/product_item.tpl'}
					{/foreach}
				</ul>
			</div>
		{/foreach}
	{/if}

{/block}