{extends 'wrapper/main.tpl'}

{block name=content}

	<!-- Хлебные крошки -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item"><span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1">
				</a>
				<span>→</span>
			</li>
			{$item_position = 2}
			{foreach $category->path as $path}
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{'Products'|linkLang:['url' => $path->url]}" itemprop="item">
						<span itemprop="name">{$path->name}</span>
						<meta itemprop="position" content="{$item_position++}">
					</a>{if !$path@last} → {/if}
				</li>
			{/foreach}
		</ul>
	</div>

	<div class="row product_one">
		<div class="col-12 col-lg-6">
			<div class="image_box me-lg-4 mb-4">

				<div class="promo_block">
					{if $product->sale}
						<div class="sale" title="{'Акция и скидка'|trans}">{'Супер цена!'|trans}</div>
					{/if}
				</div>

				{if $product->image}
					<div class="image">
						<a href="{$product->image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
							data-caption="{$product->name} - {'Фото'|trans}: 1">
							<img src="{$product->image->filename|resize:720:720:w}" alt="{$product->name} | {'Фото'|trans}: 1"
								title="{$product->name} - {'Фото'|trans}: 1">
						</a>
					</div>
				{/if}

				{if $product->images|count > 1}
					<div class="row g-2 my-2">
						{foreach $product->images as $i => $image}
							<div class="col-2">
								<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
									data-caption="{$product->name} - {'Фото'|trans}: {$i+1}">
									<img class="img-thumbnail img-fluid" src="{$image->filename|resize:60:60:c}"
										alt="{$product->name} - {'Фото'|trans}: {$i+1}"
										title="{$product->name} - {'Фото'|trans}: {$i+1}">
								</a>
							</div>
						{/foreach}
					</div>
				{/if}
			</div>
		</div>

		<div class="col-12 col-lg-6 position-relative">

			<h1>{$product->name} {if $product->variant_name} - {$product->variant_name}{/if}
				{if 'product_content'|user_access AND $product->id}
					{include file='parts/btn_edit.tpl' btn_edit_link={'ProductAdmin'|link:[id=>$product->id]}}
				{/if}
			</h1>


			<div class="my-3">{'Код'|trans}: <span class="badge text-bg-secondary rounded-pill">{$product->sku}</span></div>


			<form class="variants" id="variants" action="/cart">
				{getCSRFInput}

				<span class="price mb-4">
					{if $product->old_price > $product->price AND !$product->disable}
						<div class="old-price text-end">{$product->old_price|price_html|raw}</div>
					{/if}
					<span class="price_name">{'Цена:'|trans}</span> <span
						class="cur_price">{$product->price|price_html|raw}</span>
				</span>

				{if $product_variants->isNotEmpty()}
					<div class="d-flex flex-wrap gap-2 my-4">
						{foreach $product_variants as $product_variant}
							<div class="variant">
								<input type="radio" class="btn-check" name="product" value="{$product_variant->id}"
									id="product_{$product_variant->id}" autocomplete="off"
									{if $product->id === $product_variant->id}checked{/if}
									{if !$product_variant->stock AND !$product_variant->custom}disabled{/if}
									product_id="{$product_variant->id}" sku="{$product_variant->sku}"
									name="{$product_variant->name}" variant_name="{$product_variant->variant_name}"
									price="{$product_variant->price|price_html:clean}" max_stock="{$product_variant->stock}"
									old_price="{$product_variant->old_price|price_html:clean}" />

								{if $product_variant->variant_name}
									<label class="btn btn-outline-secondary" for="product_{$product_variant->id}">
										<div class="fw-bold">{$product_variant->variant_name}</div>
										<div class="border-top">
											<div class="status_stock">
												{if $product->disable}
													<span class="notinstock">{'Товар больше не поставляется'|trans}</span>
												{elseif $product_variant->stock>0}
													<span class="instock">{'В наличии'|trans}</span>
													{if $product_variant->stock|instock:4:{'заканчивается'|trans}}
														<span
															class="instock_count">{$product_variant->stock|instock:4:{'заканчивается'|trans}}</span>
													{/if}
												{elseif $product_variant->custom}
													<span class="awaiting">{'Под заказ'|trans}</span>
												{elseif $product_variant->awaiting}
													<span class="awaiting">{'Ожидается поставка'|trans}
														{if !$product_variant->awaiting_date|empty and $smarty.now < $product_variant->awaiting_date|strtotime}
															<span>{$product_variant->awaiting_date|date}</span>
														{/if}
													</span>
												{else}
													<span class="notinstock">{'Нет в наличии'|trans}</span>
												{/if}
											</div>

											<span class="variant_price">{$product_variant->price|price_html|raw}</span>
										</div>
									</label>
								{/if}
							</div>
						{/foreach}
					</div>
				{else}
					<input type="hidden" name="product" value="{$product->id}" product_id="{$product->id}"
						product_sku="{$product->sku}" product_name="{$product->name}" variant_name="{$product->variant_name}"
						product_price="{$product->price|price_html:clean}"
						product_old_price="{$product->old_price|price_html:clean}" product_max_stock="{$product->stock}" />
				{/if}



				<div class="status_stock my-3">
					{$show_buy_btn = false}
					{if $product->disable}
						<span class="notinstock">{'Товар больше не поставляется'|trans}</span>
					{elseif $product->stock > 0}
						{$show_buy_btn = true}
						<span class="instock">{'В наличии'|trans}</span>
						{if $product->stock|instock:4:{'заканчивается'|trans}}
							<span
								class="instock_count badge text-bg-warning">{$product->stock|instock:4:{'заканчивается'|trans}}</span>
						{/if}
					{elseif $product->custom}
						{$show_buy_btn = true}
						<span class="awaiting">{'Под заказ'|trans}</span>
					{elseif $product->awaiting}
						<span class="awaiting">{'Ожидается поставка'|trans}
							{if !$product->awaiting_date|empty and $smarty.now < $product->awaiting_date|strtotime}
								<span>{$product->awaiting_date|date}</span>
							{/if}
						</span>
					{else}
						<span class="notinstock">{'Нет в наличии'|trans}</span>
					{/if}
				</div>

				{if $show_buy_btn}
					<div class="row">
						<div class="col-auto">
							<div class="product_amount">
								<div class="input-group">
									<div class="input-group-text items minus">-</div>
									<input type="text" name="amount" value="1" class="form-control text-center"
										aria-label="Product amount" />
									<div class="input-group-text items plus">+</div>
								</div>
							</div>
						</div>

						<div class="col-7 col-lg-5">
							<button class="btn btn-primary" type="submit" value="to_cart"
								data-result-text="{'Добавлено'|trans}">
								<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
								<span class="btn-content">
									<svg class="cart-icon" viewBox="0 0 1024 1024">
										<path fill="#fff"
											d="M97.718857 109.714286a109.714286 109.714286 0 0 1 107.349333 87.064381L220.16 268.190476h0.24381l49.005714 234.666667L306.541714 682.666667h459.678476l70.460953-341.333334H285.500952l-15.286857-73.142857h566.491429a73.142857 73.142857 0 0 1 71.631238 87.942095l-70.460952 341.333334A73.142857 73.142857 0 0 1 766.22019 755.809524H306.541714a73.142857 73.142857 0 0 1-71.631238-58.343619l-69.241905-335.335619-0.463238 0.097524-31.695238-150.357334A36.571429 36.571429 0 0 0 97.718857 182.857143H35.157333v-73.142857zM304.761905 926.47619a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z m438.857143 0a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z">
										</path>
									</svg>
									<span>{'Купить'|trans}</span>
								</span>
							</button>
						</div>
					</div>

					<div class="my-3">
						<a href="#" id="product-price-request" class="you-price" data-product-id="{$product->id}"
							rel="nofollow">{'Хочу дешевле'|trans}</a>
					</div>
				{/if}
			</form>

			<div class="info-box">
				{addon name='InfoBlock' id=1 enabled=1}
			</div>

			{if $product_features}
				<h2 class="mt-4">{'Характеристики'|trans}</h2>
				<ul class="features">
					{foreach $product_features as $f}
						<li>
							<div class="label">
								<span>{$f->name}</span>
							</div>
							<div class="value">
								<span>{$f->value}</span>
							</div>
						</li>
					{/foreach}
				</ul>
			{/if}
		</div>
	</div>

	<!-- Описание товара -->
	{if $product->body}
		<h2 class="mt-4">{'Описание'|trans}</h2>
		<div class="description_html">
			{$product->body|raw}
		</div>
	{/if}


	{if $related_products}
		<div id="related_products" class="related_products_box">
			<h2>{'С этим товаром покупают'|trans}</h2>
			<div class="products owl-carousel">
				{foreach $related_products as $product}
					{include file='parts/product_item.tpl'}
				{/foreach}
			</div>
		</div>
	{/if}

	{include file='parts/comments.tpl' entity=$product}

{/block}