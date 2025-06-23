{block name=subtabs}

	<ul id="submenu" class="submenu">

		{if 'product_content'|user_access}
			<li class="{if $route|in_array:[ProductAdmin, ProductNewAdmin]}active{/if}">
				<a href="/admin/product/{$product->id}">Контент</a>
			</li>
		{/if}

		{if 'product_price'|user_access and $product->id}
			<li class="{if $route == 'ProductPriceAdmin'}active{/if}">
				<a href="/admin/product/{$product->id}/price">Цены</a>
			</li>
		{/if}

		{if $smarty.get.return}
			<li class="back">
				<a class="out_link" href="{$smarty.get.return}">Назад</a>
			</li>
		{/if}

	</ul>
{/block}