{extends 'wrapper/main.tpl'}

{block name=content}
	<!-- Хлебные крошки -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li class='home'></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item"><span itemprop="name">Главная</span>
					<meta itemprop="position" content="1">
				</a>
			</li>
			<li class='arrow'>/</li>

			{$item_position = 2}
			{foreach $category->path as $path}
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{'Products'|linkLang:[url => $path->url]}" itemprop="item"><span
							itemprop="name">{$path->name}</span>
						<meta itemprop="position" content="{$item_position++}">
					</a>
				</li>
				<li class='arrow'>/</li>
			{/foreach}

			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">{$product->name}</span>
				<meta itemprop="position" content="{$item_position}">
			</li>
		</ul>
	</div>

	<h1>{$product->name}</h1>

	{if 'product_content'|user_access AND $product->id}
		<div class="admin_edit">
			<a href="{'ProductAdmin'|link:[id => $product->id]}" data-bs-toggle="tooltip"
				title="Редактировать товар">Редактировать
				товар</a>
		</div>
	{/if}

	<div class="product_one">
		<div class="row header">

			<div class="col-12 col-lg-6 images-box">
				{if $product->sale}
					<div class="sale" data-bs-toggle="tooltip" title="Акция и скидка"></div>
				{/if}

				{if $product->image}
					<div class="image">
						<a href="{$product->image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
							data-caption="{$product->name} - Фото: 1">
							<img loading="lazy" src="{$product->image->filename|resize:720:720:w}"
								alt="{$product->name} | Фото: 1" data-bs-toggle="tooltip" title="{$product->name} - Фото: 1">
						</a>
					</div>
				{/if}

				{if $product->images|count>1}
					<div class="images">
						{foreach $product->images|cut as $i=>$image}
							<div>
								<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images"
									data-caption="{$product->name} - Фото: {$i+1}">
									<img loading="lazy" src="{$image->filename|resize:200:200}"
										alt="{$product->name} - Фото: {$i+1}" data-bs-toggle="tooltip"
										title="{$product->name} - Фото: {$i+1}">
								</a>
							</div>
						{/foreach}
					</div>
				{/if}
			</div>


			<div class="col-12 col-lg-6 middle">
				{if $product->variants|count > 0}
					<form class="variants" action="/cart">
						{getCSRFInput}

						{$show_buy_btn = false}
						{foreach $product->variants as $v}
							<div class="variant">
								<input id="product_{$v->id}" name="variant" value="{$v->id}" type="radio"
									class="variant_radiobutton" {if $product->variant->id == $v->id}checked{/if}
									{if !$v->stock AND !$v->custom}disabled{/if}
									{if $product->variants|count<2}style="display:none;" {/if}>

								{if $v->name}
									<label class="variant_name" for="product_{$v->id}">{$v->name}</label>
								{/if}

								<div class="status_stock">
									{if $product->disable}
										<div class="notinstock">
											<span class="instock_status">Больше не поставляется</span>
										</div>
									{elseif $v->stock > 0}
										{$show_buy_btn = true}
										<div class="instock">
											<span class="instock_status">В наличии</span>
											{if $v->stock|instock:4:'заканчивается'}
												<span class="instock_count">{$v->stock|instock:4:'заканчивается'}</span>
											{/if}
										</div>
									{elseif $v->custom}
										{$show_buy_btn = true}
										<div class="awaiting">
											<span class="instock_status">Под заказ</span>
										</div>
									{elseif $v->awaiting}
										<div class="awaiting">
											<span class="instock_status">Ожидается поставка</span>
											{if !$v->awaiting_date|empty and $smarty.now < $v->awaiting_date|strtotime}
												<span class="instock_count">{$v->awaiting_date|date}</span>
											{/if}
										</div>
									{else}
										<div class="notinstock">
											<span class="instock_status">Нет в наличии</span>
										</div>
									{/if}
								</div>

								<div class="price">
									{if $v->old_price > $v->price AND !$product->disable}
										<div class="old_price_wrap">
											<span class="old_price">{$v->old_price|price_html|raw}</span>
											<span class="old_price_profit">Выгода {($v->old_price - $v->price)|price_html|raw}</span>
										</div>
									{/if}
									<div class="real_price">
										<span class="add-text">Цена:</span>{$v->price|price_html|raw}<span </div>
									</div>

								</div>
							{/foreach}

							<div class="info">Цена и наличие на сайте актуальны</div>

							{if $show_buy_btn}
								<div class="buy_btn mt-3">
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
								</div>
							{/if}
					</form>
				{/if}

				<div class="delivery_info mt-4">
					{addon  name='InfoBlock' id=1}
				</div>

				{if $product->features}
					<h2 class="mt-4">Характеристики</h2>

					<ul class="features">
						{foreach $product->features as $f}
							<li>
								<div class="feature_label">
									<span>{$f->name}</span>
								</div>
								<div class="feature_value">
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
			<h2 class="mt-4">Описание</h2>
			<div class="description_html">
				{$product->body|raw}
			</div>
		{/if}
	</div>



	{if $related_products}
		<div class="related_products_box">
			<h2>С этим товаром покупают</h2>
			<ul class="products gallerywide owl-carousel">
				{foreach $related_products as $product}
					{include file='parts/product_item.tpl'}
				{/foreach}
			</ul>
		</div>
	{/if}


	{include file='parts/comments.tpl' entity=$product}

{/block}