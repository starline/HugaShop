<!-- Product -->
<li class="product_item{if $product->featured} featured{/if}{if $product->disable} no_stock{/if}"
	product_id="{$product->id}">
	<div class="product_wrap">
		<div class="product_content">

			<div class="promo_block">
				{if $product->sale}
					<div class="sale" title="{'Супер цена'|trans}">{'Супер цена'|trans}</div>
				{/if}
			</div>

			<!-- Product photo -->
			{if $product->image}
				<a class="image" href="{'Product'|linkLang:[url => $product->url]}">
					<img {if $lazy}loading="lazy" {/if} class="object-fit-contain" width="220" height="220"
						src="{$product->image->filename|resize:220:220}" alt="{$product->name}" title="{$product->name}" />
				</a>
			{/if}

			<div class="product_info">
				<a class="name" href="{'Product'|linkLang:[url => $product->url]}"
					title="{$product->name}">{$product->name}</a>
				<div class="annotation">{$product->annotation}</div>
			</div>

			{if $type != 'short'}

				{if $product->stock > 0}
					{$is_instock = true}
				{/if}

				{if $product->custom == 1}
					{$is_custom = true}
				{/if}

				{if $product->awaiting == 1}
					{$is_awaiting = true}
					{$awaiting_date = $v->awaiting_date}
				{/if}

				<div class="status_stock">
					{if $product->disable}
						<span class="notinstock">{'Товар больше не поставляется'|trans}</span>
					{elseif $is_instock === true}
						<span class="instock">{'В наличии'|trans}</span>
					{elseif $is_custom === true}
						<span class="awaiting">{'Под заказ'|trans}</span>
					{elseif $is_awaiting === true}
						<span class="awaiting">
							{'Ожидается поставка'|trans}
							{if !$awaiting_date|empty and $smarty.now < $awaiting_date|strtotime}{$awaiting_date|date}{/if}</span>
					{else}
						<span class="notinstock">{'Нет в наличии'|trans}</span>
					{/if}
				</div>

				<div class="product_price">

					<!-- Product -->
					<form class="variants" action="/cart">

						<input name="product" value="{$product->id}" type="hidden" product_id="{$product->id}"
							product_sku="{$product->sku}" product_name="{$product->name}"
							variant_name="{$product->variant_name}" product_price="{$product->price|price_html:clean}"
							product_old_price="{$product->old_price|price_html:clean}"
							product_max_stock="{$product->stock}" />

						<input name="amount" value="1" type="hidden" />

						<div class="variant">
							<div class="price">{$product->price|price_html|raw}</div>
						</div>

						{if $is_instock === true || is_custom === true}
							<button class="btn btn-primary" type="submit" value="to_cart"
								data-result-text="{'Добавлено'|trans}">
								<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
								<span class="btn-content">
									<svg class="cart-icon" viewBox="0 0 1024 1024">
										<path fill="#fff"
											d="M97.718857 109.714286a109.714286 109.714286 0 0 1 107.349333 87.064381L220.16 268.190476h0.24381l49.005714 234.666667L306.541714 682.666667h459.678476l70.460953-341.333334H285.500952l-15.286857-73.142857h566.491429a73.142857 73.142857 0 0 1 71.631238 87.942095l-70.460952 341.333334A73.142857 73.142857 0 0 1 766.22019 755.809524H306.541714a73.142857 73.142857 0 0 1-71.631238-58.343619l-69.241905-335.335619-0.463238 0.097524-31.695238-150.357334A36.571429 36.571429 0 0 0 97.718857 182.857143H35.157333v-73.142857zM304.761905 926.47619a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z m438.857143 0a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z">
										</path>
									</svg>
									<span>{'В корзину'|trans}</span>
								</span>
							</button>
						{/if}

					</form>
				</div>
			{/if}
		</div>
	</div>
</li>