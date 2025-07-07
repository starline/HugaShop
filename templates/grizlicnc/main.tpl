{extends 'wrapper/main.tpl'}

{block name=content}
	{if $categories_products}
		{foreach $categories_products as $cat_products}

			{$lazy_load = true}
			{if $cat_products@first}{$lazy_load = false}{/if}

			<div class="accessories_products">
				<div class="title-main">
					<h2>{$cat_products->category->name}</h2>
					<span> → <a href="{'Products'|linkLang:[url => $cat_products->category->url]}"
							title="{$cat_products->category->name}">{'все'|trans}
							{$cat_products->category->name}</a>
					</span>
				</div>

				<ul class="products gallerywide">
					{foreach $cat_products->products as $product}
						{include 'parts/product_item.tpl' lazy=$lazy_load}
					{/foreach}
				</ul>
			</div>
		{/foreach}
	{/if}
{/block}