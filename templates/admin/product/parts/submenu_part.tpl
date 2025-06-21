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

	<select id="language_select" class="form-select form-select-sm w-auto ms-auto">
		{foreach $languages as $language}
			<option value="{$language->code}" {if $current_language == $language->code}selected{/if}>{$language->name}</option>
		{/foreach}
	</select>

	{literal}
		<script>
			document.getElementById('language_select').addEventListener('change', function() {
				const url = new URL(window.location.href);
				url.searchParams.set('lang', this.value);
				window.location.href = url.toString();
			});
		</script>
	{/literal}

{/block}