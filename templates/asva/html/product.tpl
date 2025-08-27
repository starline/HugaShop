{extends 'wrapper/main.tpl'}

{block name=content}

	<!-- Хлебные крошки -->
	<div class="breadcrumbs-wrapper">
		<ul class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item"><span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1">
				</a>
			</li>
			<li><span class="delimiter"></span></li>
			{$item_position = 2}
			{foreach $category->path as $path}
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{'Products'|linkLang:['url' => $path->url]}" itemprop="item">
						<span itemprop="name">{$path->name}</span>
						<meta itemprop="position" content="{$item_position++}">
					</a>
				</li>
			{/foreach}
			<li><span class="delimiter"></span></li>
			<li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
				<span itemprop="name">{$product->name}{if $product->variant_name} - {$product->variant_name}{/if}</span>
				<meta itemprop="position" content="{$item_position++}">
			</li>
		</ul>
	</div>

	<!-- Карточка товара -->
	<div class="right-side left_product_page">
		<div class="block-title-wrapper">
			<div class="block-title">
				<h1>{$product->name}{if $product->variant_name} - {$product->variant_name}{/if}</h1>

				{if 'product_content'|user_access AND $product->id}
					<div class="admin_edit">
						<a href="{'ProductAdmin'|link:[id=>$product->id]}" data-bs-toggle="tooltip">Редактировать
							товар</a>
					</div>
				{/if}
			</div>
		</div>


		<div class="tire-description-wrapper">
			<div class="tire-image">
				{if $product->image}
					<a href="{$product->image->filename|resize:1080:1080:w}" data-fancybox="images"
						data-caption="{$product->name}{if $product->variant_name} - {$product->variant_name}{/if}">
						<img src="{$product->image->filename|resize:720:720:w}"
							alt="{$product->name}{if $product->variant_name} - {$product->variant_name}{/if}"
							title="{$product->name}{if $product->variant_name} - {$product->variant_name}{/if}">
					</a>
				{/if}

				{if $product->images|count > 1}
					<div class="tire-image-tiny">
						{foreach $product->images as $i => $image}
							<a href="{$image->filename|resize:1080:1080:w}" data-fancybox="images"
								data-caption="{$product->name} - Фото: {$i+1}">
								<img src="{$image->filename|resize:60:60:c}" alt="{$product->name} - Фото: {$i+1}"
									title="{$product->name} - Фото: {$i+1}" width="110" height="auto">
							</a>
						{/foreach}
					</div>
				{/if}
			</div>


			<div class="tire-description icons">
				<a class="tire-brand" href="" title="{$product->brand->name}">
					{if $product->brand->image}
						<img src="{$product->brand->image->filename|resize:154:48}" alt="{$product->brand->name}"
							title="{$product->brand->name}" width="154" height="48">
					{else}
						{$product->brand->name}
					{/if}
				</a>
				<div class="tire-code">
					<div><b>АРТИКУЛ:</b> {$product->sku}</div>
					<ul class="tire-rating star-rating">
						<li class="current-rating" style="width:177.5px"></li>
					</ul>
				</div>
			</div>


			<div class="tire-description">
				<div class="detail-item-description  no">
					<form class="sale_block" id="sale_block_form" action="/cart">
						{getCSRFInput}
						<div class="price-tire">
							<span class="price">
								<span class="price-pdv"></span>
								<span class="price-amount">{$product->price|price_html|raw}</span>
							</span>
							<a href="#" id="product-price-request" class="you-price" data-product-id="{$product->id}">Хочу
								дешевле</a>
						</div>

						<div class="product-item-purchase">
							<div class="productQty-item">
								<div class="basketQtyMinus minus_click fa fa-minus" id="sub" rel="{$product->id}"></div>
								<input type="text" class="basketQty" name="quantity" id="item_count{$product->id}"
									count="{$product->stock}" value="4" size="4" maxlength="6">

								<div class="basketQtyPlus plus_click fa fa-plus" id="add" rel="{$product->id}"></div>
								<span>из {$product->stock}</span>
							</div>

							<div class="buy-item">
								<input type="hidden" name="product" value="{$product->id}" product_id="{$product->id}"
									product_sku="{$product->sku}" product_name="{$product->name}"
									variant_name="{$product->variant_name}"
									product_price="{$product->price|price_html:clean}"
									product_old_price="{$product->old_price|price_html:clean}"
									product_max_stock="{$product->stock}" />

								<button class="button buy" type="submit"
									onclick="ajaxAdd2Basket({$product->id},'item_count{$product->id}');return false;"
									name="BUY">Купить</button>

								<!-- Кредит онлайн -->
								<span class="button credit" id="finmaster" shopid="02_ACV_001" pid="{$product->id}"
									ptitle="{$product->name}{if $product->variant_name} - {$product->variant_name}{/if}"
									pprice="10550">купить в кредит</span>
							</div>
						</div>
					</form>
				</div>

				<div class="detal-item">
					<div class="specifications-title">
						<div>характеристики шины</div>
						<div class="tire-icons">
							<i class="ico ico-truck-trl" alt="Грузовые, прицепная ось" title="Грузовые, прицепная ось"></i>
							<i class="ico ico-allseason" alt="Всесезонная шина" title="Всесезонная шина"></i>
						</div>
					</div>

					{if $product->features}
						<div class="specifications-items">
							{foreach $product->features as $f}
								<div feature_id="{$f->id}">
									<span>{$f->name}:</span>
									<a>{$f->value}</a>
								</div>
							{/foreach}
						</div>
					{/if}

					<div class="info">
						В исключительных случаях информация о Стране происхождения и Дате выпуска товара может отличаться
						(показаны данные последней поставки).
					</div>
				</div>
			</div>
		</div>


		<div class="tires-description-details">
			<h3 class="tire-description-title">описание шины</h3>

			<div class="tire-description-content">
				{if $product->body}
					{$product->body|raw}
				{else}
					<!-- Автоматический SEO модуль -->
					<p class="seo-modul">
						Всесезонная шина типоразмера 385/65 R22.5 подходит для установки на Грузовые авто.
						Представленная автошина выпущена на заводе в стране: Китай. Маркер даты изготовления этого экземпляра:
						1824. Обычно указывается неделя и год выпуска покрышки, но иногда на странице информации о шине показан
						только год. Поскольку высота протектора составляет 65% от ширины протектора - это делает движение по
						асфальту комфортным. Диаметр колеса R22.5 оптимальный для многих машин.
						Покупая авторезину стоит обратить внимание на индекс скорости и нагрузки, от этих значений зависит режим
						эксплуатации автошины.
						Для Grenlander FT138 индекс скорости "L", а индекс нагрузки составляет 160.
						Когда вы убедились в правильности подобранных параметров, можно купить шину Grenlander FT138 385/65
						R22.5 оформив заказ на сайте или по телефону.
					</p>
				{/if}

				<p>
					<span>Детальное описание и таблица типоразмеров на странице модели <a class="desc-more"
							href="{'Products'|linkLang:[url => $product->url]}">шины {$product->name}</a></span>
				</p>
			</div>
		</div>


		<div class="analogs-by-type-wrapper">
			<h3 class="analogs-by-type-title">Аналоги по типоразмеру</h3>

			<div class="analogs-by-type-items">
				<div class="analogs-by-type-item-title">
					<div class="art">Артикул</div>
					<div class="name">название</div>
					<div class="spec">характеристики</div>
					<div class="price">цена</div>
					<div class="buy"></div>
				</div>

				{foreach $product_analogs as $aproduct}
					<div class="analogs-by-type-item">
						<div class="art">{$aproduct->name}</div>
						<a href="{'Products'|linkLang:[url => $product->url]}" class="name">
							385/65 R 22.5 Satoya ST-082 160K прич </a>

						<div class="spec">
							<i class="ico ico-truck-trl" alt="Грузовые, прицепная ось" title="Грузовые, прицепная ось"></i> <i
								class="ico ico-allseason" alt="Всесезонная шина" title="Всесезонная шина"></i>
						</div>

						<div class="price">9979 </div>

						<div class="buy">
							<a class="button" href="{'Products'|linkLang:[url => $product->url]}">Купить</a>
						</div>
					</div>
				{/foreach}

			</div>

			<p>* Возможно, показаны не все представленные в магазине аналоги по типоразмерам. Для выбора из полного
				ассортимента воспользуйтесь сервисом</p>

			<div class="analogs-by-type-more">
				<a href="#" class="button">показать
					еще шины 385/65 R22.5 </a>
			</div>
		</div>

		{include file='parts/comments.tpl' entity=$product}
	</div>

	<!-- Информация для покупки -->
	<div class="left-side right_product_page">
		<div class="main-selection-buyer">

			<div class="selection-buyer delivery">
				<div class="selection-buyer-title">
					<span>Только у нас</span>
				</div>

				<div class="selection-buyer-info unique">
					<p>- Свяжемся с вами в течение 15 минут</p>
					<p>- Все менеджеры - шинные эксперты</p>
					<p>- Оригинальная продукция. Сертификаты</p>
					<p>- Официальный диллер</p>
					<p>- 20 лет на рынке</p>
				</div>
			</div>


			<div class="selection-buyer delivery">
				<div class="selection-buyer-title">
					<span>доставка</span>
				</div>
				<div class="selection-buyer-info delivery-srvices">
					<div class="delivery-srvice pickup">
						<h3>Самовывоз в Киеве </h3>
						<p>- <a href="{'Page'|linkLang:[url => 'contact']}" rel="nofollow">из офиса</a>
							<span>(бесплатно)</span>
						</p>
						<p>- <a href="{'Page'|linkLang:[url => 'uslugi-shinomontaga']}" ref="nofollow">установка шин на
								шиносервисе</a></p>
					</div>
					<div class="delivery-srvice city">
						<h3>Доставка по Киеву </h3>
						<p><a href="{'Page'|linkLang:[url => 'delivery']}" rel="nofollow">Стоимость услуги 500
								грн</a></p>
					</div>
					<div class="delivery-srvice country">
						<h3>Доставка по Украине </h3>
						<p><span>по тарифам перевозчиков</span></p>
					</div>
					<div class="delivery-srvice transport">
						<p><a href="{'Page'|linkLang:[url => 'delivery']}" rel="nofollow">Условия доставки</a>
						</p>
						<img src="{'images/ico-np.jpg'|asset}" alt="НоваяПочта">
						<img src="{'images/ico-cat.jpg'|asset}" alt="CAT">
						<img src="{'images/ico-delivery.jpg'|asset}" alt="DELIVERY">
					</div>
				</div>
			</div>


			<div class="selection-buyer warranty">
				<div class="selection-buyer-title">
					<span>гарантия</span>
				</div>
				<div class="selection-buyer-info warranty-info">
					<div class="warranty-srvice from-store">
						<a href="{'Page'|linkLang:[url => 'garanty']}" rel="nofollow">Гарантия от магазина</a>
					</div>

					<div class="warranty-srvice from-store">
						<a href="{'Page'|linkLang:[url => 'garanty']}" rel="nofollow"> Вы можете вернуть товар
							(14 дней)</a>
					</div>
				</div>
			</div>
		</div>
	</div>

{/block}