{extends 'wrapper/main.tpl'}

{block name=content}

	<!-- Хлебные крошки -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|urll}" itemprop="item"><span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1">
				</a>
				<span>→</span>
			</li>
			{$item_position = 2}
			{foreach $category->path as $path}
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{'Products'|urll:['url' => $path->url]}" itemprop="item">
						<span itemprop="name">{$path->name}</span>
						<meta itemprop="position" content="{$item_position++}">
					</a>{if !$path@last} → {/if}
				</li>
			{/foreach}
		</ul>
	</div>

	<div class="row product_one">
		<div class="col-12 col-lg-6">

			<div class="image_box mb-4">

				<div class="promo_block">
					{if $product->sale}
						<div class="sale" title="Акция и скидка">Супер цена!</div>
					{/if}
				</div>

				{if $product->image}
					<div class="image">
						<a href="{$product->image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
							data-caption="{$product->name} - Фото: 1">
							<img src="{$product->image->filename|resize:720:720:w}" alt="{$product->name} | Фото: 1"
								title="{$product->name} - Фото: 1">
						</a>
					</div>
				{/if}

				{if $product->images|count > 1}
					<div class="row g-2 my-2">
						{foreach $product->images as $i => $image}
							<div class="col-3">
								<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
									data-caption="{$product->name} - Фото: {$i+1}">
									<img class="img-thumbnail img-fluid" src="{$image->filename|resize:220:220}"
										alt="{$product->name} - Фото: {$i+1}" title="{$product->name} - Фото: {$i+1}">
								</a>
							</div>
						{/foreach}
					</div>
				{/if}
			</div>
		</div>

		<div class="col-12 col-lg-6">
			<h1>{$product->name}</h1>

			{if 'product_content'|user_access AND $product->id}
				<div class="admin_edit">
					<a href="{'ProductAdmin'|urll:[id=>$product->id]}" data-bs-toggle="tooltip">Редактировать
						товар</a>
				</div>
			{/if}

			{if $product->variants|count > 0}
				<form class="variants" id="variants" action="/cart">
					{getCSRFInput}

					<span class="price mb-4">
						{if $product->variant->old_price > $product->variant->price AND !$product->disable}
							<div class="old-price text-end">{$product->variant->old_price|price_html|raw}</div>
						{/if}
						<span class="price_name">Цена:</span> <span
							class="cur_price">{$product->variant->price|price_html|raw}</span>
					</span>

					<div class="d-flex flex-wrap gap-2 my-4">
						{foreach $product->variants as $v}
							<div class="variant">
								<input type="radio" class="btn-check" name="variant" value="{$v->id}" id="variant_{$v->id}"
									autocomplete="off" {if $product->variant->id == $v->id}checked{/if}
									{if !$v->stock AND !$v->custom}disabled{/if} variant_sku="{$v->sku}"
									product_name="{$product->name}" variant_name="{$v->name}" price="{$v->price|price_html:clean}"
									max_stock="{$v->stock}" old_price="{$v->old_price|price_html:clean}" />

								{if $v->name}
									<label class="btn btn-outline-secondary" for="variant_{$v->id}">
										<div class="fw-bold">{$v->name}</div>
										<div class="border-top">
											<div class="status_stock">
												{if $product->disable}
													<span class="notinstock">Товар больше не поставляется</span>
												{elseif $v->stock>0}
													<span class="instock">В наличии</span>
													{if $v->stock|instock:4:'заканчивается'}
														<span class="instock_count">{$v->stock|instock:4:'заканчивается'}</span>
													{/if}
												{elseif $v->custom}
													<span class="awaiting">Под заказ</span>
												{elseif $v->awaiting}
													<span class="awaiting">Ожидается поставка
														{if !$v->awaiting_date|empty and $smarty.now < $v->awaiting_date|strtotime}
															<span>{$v->awaiting_date|date}</span>
														{/if}
													</span>
												{else}
													<span class="notinstock">Нет в наличии</span>
												{/if}
											</div>

											<span class="variant_price">{$v->price|price_html|raw}</span>
										</div>
									</label>
								{/if}
							</div>
						{/foreach}
					</div>

					{$show_buy_btn = false}

					<div class="status_stock my-3">
						{if $product->disable}
							<span class="notinstock">Товар больше не поставляется</span>
						{elseif $product->variant->stock > 0}
							{$show_buy_btn = true}
							<span class="instock">В наличии</span>
							{if $product->variant->stock|instock:4:'заканчивается'}
								<span
									class="instock_count badge text-bg-warning">{$product->variant->stock|instock:4:'заканчивается'}</span>
							{/if}
						{elseif $product->variant->custom}
							{$show_buy_btn = true}
							<span class="awaiting">Под заказ</span>
						{elseif $product->variant->awaiting}
							<span class="awaiting">Ожидается поставка
								{if !$product->variant->awaiting_date|empty and $smarty.now < $product->variant->awaiting_date|strtotime}
									<span>{$product->variant->awaiting_date|date}</span>
								{/if}
							</span>
						{else}
							<span class="notinstock">Нет в наличии</span>
						{/if}
					</div>


					{if $show_buy_btn}
						<div class="row">
							<div class="col-auto">
								<div class="product_amount">
									<label class="d-none">Кол-во:</label>
									<div class="input-group">
										<div class="input-group-text items minus">-</div>
										<input type="text" name="amount" value="1" class="form-control text-center" />
										<div class="input-group-text items plus">+</div>
									</div>
								</div>
							</div>

							<div class="col-7 col-lg-5 d-grid">
								<button type="submit" class="btn btn-primary" value="в корзину" data-result-text="Добавлено">
									<svg class="cart-icon" viewBox="0 0 1024 1024">
										<path fill="#fff"
											d="M97.718857 109.714286a109.714286 109.714286 0 0 1 107.349333 87.064381L220.16 268.190476h0.24381l49.005714 234.666667L306.541714 682.666667h459.678476l70.460953-341.333334H285.500952l-15.286857-73.142857h566.491429a73.142857 73.142857 0 0 1 71.631238 87.942095l-70.460952 341.333334A73.142857 73.142857 0 0 1 766.22019 755.809524H306.541714a73.142857 73.142857 0 0 1-71.631238-58.343619l-69.241905-335.335619-0.463238 0.097524-31.695238-150.357334A36.571429 36.571429 0 0 0 97.718857 182.857143H35.157333v-73.142857zM304.761905 926.47619a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z m438.857143 0a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z">
										</path>
									</svg>
									<span>{'Добавить в корзину'|trans}</span>
								</button>
							</div>
						</div>
					{/if}
				</form>
			{/if}

			<div class="info-box">
				{extension name='InfoBlock' id=1}
			</div>

			{if $product->features}
				<h2 class="mt-4">Характеристики</h2>
				<ul class="features">
					{foreach $product->features as $f}
						<li>
							<div class="label">
								<span>{$f->name}</span>
							</div>
							<div class="value">
								<span>{$f->value->value}</span>
							</div>
						</li>
					{/foreach}
				</ul>
			{/if}
		</div>
	</div>

	<!-- Описание товара -->
	{if $product->body}
		<h2 class="mt-4">Описание</h2>
		<div class="description_html">
			{$product->body|raw}
		</div>
	{/if}


	{if $product->related}
		<div class="related_products_box">
			<h2>С этим товаром покупают</h2>
			<ul class="products owl-carousel">
				{foreach $product->related as $product}
					{include file='parts/product_item.tpl'}
				{/foreach}
			</ul>
		</div>
	{/if}

	{include file='parts/comments.tpl'}

{/block}