{block name=subtabs}

	<ul id="submenu" class="submenu">

		{if 'product_content'|user_access}
			<li class="{if $route|in_array:[ProductAdmin, ProductNewAdmin]}active{/if}">
				<a href="{'ProductAdmin'|link:[id => $product->id]}">Контент</a>
			</li>
		{/if}

		{if 'product_price'|user_access and $product->id}
			<li class="{if $route == 'ProductPriceAdmin'}active{/if}">
				<a href="{'ProductPriceAdmin'|link:[id => $product->id]}">Цены</a>
			</li>
		{/if}

		{if 'order'|user_access and $product->id}
			<li class="{if $route == 'ProductOrdersAdmin'}active{/if}">
				<a href="{'ProductOrdersAdmin'|link:[id => $product->id]}">Заказы</a>
			</li>
		{/if}

		{if 'warehouse'|user_access and $product->id}
			<li class="{if $route == 'ProductMoveAdmin'}active{/if}">
				<a href="{'ProductMoveAdmin'|link:[id => $product->id]}">Поставки</a>
			</li>
		{/if}

		{if $smarty.get.return}
			<li class="back">
				<a class="out_link" href="{$smarty.get.return}">Назад</a>
			</li>
		{/if}

	</ul>
{/block}