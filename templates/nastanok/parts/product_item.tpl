<!-- Товар -->
{$variant = $product->variant}
{if $product->disable}
	{$is_instock = false}
{else}
	{foreach $product->variants as $v}
		{if $v->stock > 0}
			{$is_instock = true}
			{$variant = $v}
			{break}
		{/if}

		{if $v->custom == 1}
			{$is_custom = true}
			{$variant = $v}
			{break}
		{/if}

		{if $v->awaiting == 1}
			{$is_awaiting = true}
			{$awaiting_date = $v->awaiting_date}
		{/if}
	{/foreach}
{/if}


<li class="{if $product->featured}featured{/if} {if $product->disable}no_stock{/if}" product_id="{$product->id}">

	{if $product->sale}
		<div class="sale" data-bs-toggle="tooltip" title="Акция и скидка"></div>
	{/if}

	<!-- Фото товара -->
	{if $product->image}
		<a class="image" href="{'Product'|link:[url => $product->url]}">
			<img loading="lazy" src="{$product->image->filename|resize:200:200}" alt="{$product->name}"
				data-bs-toggle="tooltip" title="{$product->name}">
		</a>
	{/if}

	<div class="product_info">
		<a class="name" href="{'Product'|link:[url => $product->url]}" data-bs-toggle="tooltip"
			title="{$product->name}">{$product->name}</a>
		{if $product->brand_image}
			<img loading="lazy" class="brand-img" alt="{$product->brand_name}" data-bs-toggle="tooltip"
				title="{$product->brand_name}" src="/files/brands/{$product->brand_image}">
		{/if}
		{if $product->annotation}
			<div class="annotation">{$product->annotation}</div>
		{/if}
	</div>

	<div class="status_stock">
		{if $product->disable}
			<div class="notinstock">
				<span class="instock_status">Больше не поставляется</span>
			</div>
		{elseif $is_instock === true}
			<div class="instock">
				<span class="instock_status">В наличии</span>
				{if $variant->stock|instock:4:'заканчивается'}
					<span class="instock_count">{$variant->stock|instock:4:'заканчивается'}</span>
				{/if}
			</div>
		{elseif $is_custom === true}
			<div class="awaiting">
				<span class="instock_status">Под заказ</span>
			</div>
		{elseif $is_awaiting === true}
			<div class="awaiting">
				<span class=instock_status>Ожидается поставка</span>
				{if !$awaiting_date|empty and $smarty.now < $awaiting_date|strtotime}
					<span class="instock_count">{$awaiting_date|date}</span>
				{/if}
			</div>
		{else}
			<div class="notinstock">
				<span class="instock_status">Нет в наличии</span>
			</div>
		{/if}
	</div>


	<div class="product_price">

		<!-- Выбор варианта товара -->
		<form class="variants" action="/cart">

			{if $variant->id}
				<input id="variants_{$variant->id}" name="variant" value="{$variant->id}" type="hidden">
				<input name="amount" value="1" type="hidden">
			{/if}

			<div class="variant">
				<div class="price">
					<span class="add-text">Цена:</span> {$variant->price|price_html|raw}
				</div>
			</div>

			{if $is_instock === true || is_custom === true}
				<button type="submit" class="btn btn-primary" value="в корзину" data-result-text="добавлено">
					<svg class="cart-icon" viewBox="0 0 2.99438 2.65203">
						<path
							d="M1.11876 2.15418c0.1374,0 0.248786,0.111398 0.248786,0.248908 0,0.137388 -0.111386,0.248944 -0.248798,0.248944 -0.1374,0 -0.248883,-0.111544 -0.248883,-0.248944 0,-0.137534 0.111483,-0.248896 0.248896,-0.248908z">
						</path>
						<path
							d="M2.20501 2.15418c0.1374,0 0.248883,0.111398 0.248883,0.248908 1.2196e-005,0.137388 -0.111483,0.248944 -0.248883,0.248944 -0.13751,0 -0.248908,-0.111544 -0.248908,-0.248944 2.4392e-005,-0.137534 0.111386,-0.248896 0.248908,-0.248908z">
						</path>
						<path
							d="M2.83894 0.789836c-0.317876,0 -1.53624,3.6588e-005 -1.53624,3.6588e-005 0,-3.6588e-005 -0.0908235,0.020599 -0.0908235,0.120338 1.2196e-005,0.100007 0.0908235,0.129021 0.0908235,0.129021l1.37732 -1.2196e-005 -0.0773835 0.26186c-0.377014,0 -1.35377,1.2196e-005 -1.35377,1.2196e-005 1.2196e-005,-1.2196e-005 -0.0846279,0.0191599 -0.0846279,0.111959 0,0.0932261 0.0846279,0.12035 0.0846279,0.12035l1.28237 0 0.00287825 0 -0.0726637 0.244944 -1.39773 0 -0.0928114 -0.739223 -0.0301241 -0.249286c0,0 -0.0270141,-0.230272 -0.0422713,-0.325267 -0.0151596,-0.0956531 -0.253994,-0.215137 -0.76742,-0.457118 -0.120716,-0.0506987 -0.221711,0.170195 0,0.285776l0.404834 0.189099c0,0 0.0941652,0.0547478 0.1023,0.128058 0.00808594,0.0737125 0.172341,1.29155 0.172341,1.29155 1.2196e-005,-1.2196e-005 0.00656144,0.169512 0.160609,0.169512 0.153877,0 1.41588,0 1.54479,0 0.129326,0 0.15445,-0.135095 0.15445,-0.135095l0.306131 -0.909235c0,0 0.0908235,-0.237248 -0.137607,-0.237285z">
						</path>
					</svg>
					В корзину
				</button>
			{/if}

		</form>
	</div>
</li>