{block name=tabs}

	{if 'product_view'|user_access}
		<li
			class="mini {if $route|in_array:[ProductListAdmin, ProductAdmin, ProductPriceAdmin, ImportProductPAdmin]}active{/if}">
			<a href="{'ProductListAdmin'|link}">Товары</a>
		</li>
	{/if}

	<!-- Правая часть -->
	{if 'product_category'|user_access}
		<li class="mini right {if $route|in_array:[CategoryListAdmin, CategoryAdmin, CategoryNewAdmin]}active{/if}">
			<a href="{'CategoryListAdmin'|link}">Категории</a>
		</li>
	{/if}

	{if 'product_feature'|user_access}
		<li class="mini right {if $route|in_array:[FeatureListAdmin, FeatureAdmin, FeatureNewAdmin]}active{/if}">
			<a href="{'FeatureListAdmin'|link}">Характеристики</a>
		</li>
	{/if}

	{if 'product_brand'|user_access}
		<li class="mini right {if $route|in_array:[BrandListAdmin, BrandAdmin, BrandNewAdmin]}active{/if}">
			<a href="{'BrandListAdmin'|link}">Бренды</a>
		</li>
	{/if}

{/block}