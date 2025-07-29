<div class="block-item" product_id="{$product->id}">

	<div class="block-item-image">
		<a href="{'Product'|linkLang:[url => $product->url]}" title="{$product->name}">
			<img src="{$product->image->filename|resize:120}" alt="{$product->name}" title="{$product->name}"
				width="120">
		</a>

		<i class="ico ico-recomend" title="АСВА Рекомендует!"></i>

		{if $product->featured}
			<div class="hit_prodaj">Хит продаж</div>
		{/if}
	</div>

	<div class="block-item-content">
		<div class="tyre-name">
			<a href="{'Product'|linkLang:[url => $product->url]}">{$product->name}</a>
		</div>

		<div class="tyre-info-block">
			<div class="forecast">
				<i class="ico ico-car" alt="Легковая резина" title="Легковая резина"></i> <i class="ico ico-allseason"
					alt="Всесезонная шина" title="Всесезонная шина"></i>
			</div>
			<div class="tyre-info">
				<ul class="tire-rating star-rating">
					{if $product->annotation}
						<span>{$product->annotation}</span>
					{/if}
					<li class="current-rating" style="width:177.5px"></li>
				</ul>
				<div class="review-count">Отзывов: ----</div>
			</div>
		</div>

		<i class="ico-provide provide-continental"></i>

	</div>

	<div class="block-item-price">
		<input name="product" value="{$product->id}" type="hidden" product_id="{$product->id}"
			product_sku="{$product->sku}" product_name="{$product->name}" variant_name="{$product->variant_name}"
			product_price="{$product->price|price_html:clean}"
			product_old_price="{$product->old_price|price_html:clean}" product_max_stock="{$product->stock}" />
		<input name="amount" value="1" type="hidden" />
		<div class="item-price">
			<span class="text">Цена</span>
			<span class="price">{$product->price|price_html|raw}</span>
		</div>
		<div class="item-buy">
			<button type="submit" data-result-text="{'Добавлено'|trans}" class="button"
				value="to_cart">{'в корзину'|trans}</a>
		</div>
	</div>
</div>