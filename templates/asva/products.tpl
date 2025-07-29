{extends 'wrapper/main.tpl'}

{block name=content}

	<div class="left-side">
		<div class="main-selection-wrapper" id="active_main_selection">
			<div class="main-selection-title-m" onclick="openTip('main_selection'); return false;">
				<span>Фильтр шин</span>
				<i class="ico ico-arrow-3"></i>
			</div>

			<div class="main-mobile-menu-bg"></div>
			<div class="main-mobile-menu">
				<span class="ico ico-close" onclick="closeTip('main_selection'); return false;">x</span>
				<div class="main-mobile-menu-overflow">
					<div class="main-selection">
						<div class="selection-tire-wrapper">
							<div class="selection-tire-title">подбор по размеру</div>
						</div>

						<div class="selection-tire-filtres">
							<form id="form_filter" action="#" method="GET" class="sorting_form">

								<input type="hidden" name="season" value="7" />

								<div class="selection-tire-filter diametr">
									<label for="width">Ширина</label>
									<select name="tire_width" id="width">
										<option value="0">Все</option>
									</select>
								</div>

								<div class="selection-tire-filter width">
									<label for="height">Профиль</label>
									<select name="tire_height" id="height">
										<option value="0">Все</option>
									</select>
								</div>

								<div class="selection-tire-filter profile">
									<label for="diameter">Диаметр</label>
									<select name="tire_diameter" id="diameter">
										<option selected="selected" value="0">Все</option>
									</select>
								</div>

								<button>подобрать</button>
							</form>

							<div class="selection-tire car">
								<a href="#">Подобрать по автомобилю</a>
							</div>
						</div>


						<div class="main-filters-title">параметры подбора</div>

						<div class="main-filters">
							<div class="main-filter selected">
								<div class="main-filter-item-wrapper">
									<div class="main-filter-item">
										<a href="#">
											<i class="ico ico-x-filter"></i>
											<span class="link">очистить фильтры</span>
										</a>
									</div>
								</div>
							</div>

							{* Features filter *}
							{if $features}
								<table id="features">
									{foreach $features as $key=>$f}
										<tr>
											<td class="feature_name" data-feature="{$f->id}">
												{$f->name}:
											</td>
											<td class="feature_values">
												<a href="{url params=[$f->id=>null, page=>null]}"
													{if !$smarty.get.$key}class="selected" {/if} rel="nofollow">Все</a>
												{foreach $f->options as $o}
													<a href="{url params=[$f->id=>$o->value, page=>null]}"
														{if $smarty.get.$key == $o->value}class="selected" {/if}
														rel="nofollow">{$o->value}</a>
												{/foreach}
											</td>
										</tr>
									{/foreach}
								</table>
							{/if}


							<div class="main-filter">
								<div class="main-filter-title">Сезонность</div>
								<div class="main-filter-item-wrapper">
									<div class="main-filter-item">
										<a href="#" class="">
											<i class="ico ico-li"></i>
											<i class="ico ico-summer"></i>
											<span class="link">Летние</span>
										</a>
									</div>
									<div class="main-filter-item">
										<a href="#" class="">
											<i class="ico ico-li"></i>
											<i class="ico ico-winter"></i>
											<span class="link">Зимние</span>
										</a>
									</div>
									<div class="main-filter-item">
										<a href="#" class="selected">
											<i class="ico ico-li"></i>
											<i class="ico ico-allseason"></i>
											<span class="link">Всесезонные</span>
										</a>
									</div>
								</div>
							</div>

							<div class="main-filter">
								<div class="main-filter-title">Производитель</div>
								<div class="main-filter-item-wrapper manufacturer-filter" id="active_filter_more_f48">
									{foreach $brands as $brand}
										<div class="main-filter-item">
											<a href="#" class="">
												<i class="ico ico-li"></i>
												<span class="link">Accelera</span>
											</a>
										</div>
									{/foreach}
								</div>

								<div class="filter-more">
									<a href="#" class="more" onclick="openTip('filter_more_f48'); return false;">
										<i class="ico ico-plus"></i>
										<span>Больше</span>
									</a>
									<a href="#" class="less" onclick="closeTip('filter_more_f48'); return false;">
										<i class="ico ico-minus"></i>
										<span>Меньше</span>
									</a>
								</div>
							</div>


							<div class="main-filter">
								<div class="main-filter-title">применение</div>
								<div class="main-filter-item-wrapper">
									<div class="main-filter-item">
										<a href="#" class="">
											<i class="ico ico-li"></i>
											<i class="ico ico-car"></i>
											<span class="link">Легковые</span>
										</a>
									</div>
									<div class="main-filter-item">
										<a href="#" class="">
											<i class="ico ico-li"></i>
											<i class="ico ico-suv"></i>
											<span class="link">Внедорожные</span>
										</a>
									</div>
									<div class="main-filter-item">
										<a href="#" class="">
											<i class="ico ico-li"></i>
											<i class="ico ico-van"></i>
											<span class="link">На Микроавтобус</span>
										</a>
									</div>
									<div class="main-filter-item">
										<a href="#" class="">
											<i class="ico ico-li"></i>
											<i class="ico ico-truck"></i>
											<span class="link">Грузовые</span>
										</a>
									</div>
								</div>
							</div>

							<div class="main-filter">
								<div class="main-filter-title">Особые характеристики</div>
								<div class="main-filter-item-wrapper option-filter" id="active_filter_more_f58">

									<div class="main-filter-item">
										<a rel="nofollow" href="#" class="">
											<i class="ico ico-li"></i>
											<span class="link">"бархатная" боковина</span>
										</a>
									</div>

								</div>

								<div class="filter-more">
									<a href="#" class="more" onclick="openTip('filter_more_f58'); return false;">
										<i class="ico ico-plus"></i>
										<span>Больше</span>
									</a>
									<a href="#" class="less" onclick="closeTip('filter_more_f58'); return false;">
										<i class="ico ico-minus"></i>
										<span>Меньше</span>
									</a>
								</div>
							</div>

							<div class="main-filter links_block">
								<div class="main-filter-title">Популярные запросы</div>
								<ul class="main-filter-item-wrapper option-filter" style="list-style: none;">
									<li><a href="#">Белшина
											445/65 R22.5</a></li>
									<li><a href="#">Ауфине
											R20</a></li>
									<li><a href="#">Ауфине
											R22.5</a></li>
									<li><a href="#">Aufine
											R17.5 75 215</a></li>
									<li><a href="#">Aufine
											R17.5 75 235</a></li>
									<li><a href="#">Aufine
											285/70 R19.5</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="right-side">
		<!-- Breadcrumbs -->
		<div class="breadcrumbs-wrapper">
			<ul class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="{'Main'|linkLang}" itemprop="item">
						<span itemprop="name">{'Главная'|trans}</span>
						<meta itemprop="position" content="1">
					</a>
				</li>

				{$item_position = 2}
				{if $category}
					{foreach $category->path as $cat}
						<li><span class="delimiter"></span></li>
						<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
							<a href="{'Products'|linkLang:[url => $cat->url]}" itemprop="item">
								<span itemprop="name">{$cat->name}</span>
								<meta itemprop="position" content="{$item_position++}">
							</a>
						</li>
					{/foreach}
				{/if}
			</ul>
		</div>

		<div class="block-title-wrapper">
			<div class="block-title">
				<h1>{$h1}</h1>
				{if 'product_category'|user_access AND $category->id}
					<div class="admin_edit">
						<a href="{'CategoryAdmin'|link:[id => $category->id]}" data-bs-toggle="tooltip"
							title="{'Редактировать категорию'|trans}">{'Редактировать категорию'|trans}</a>
					</div>
				{/if}
			</div>
		</div>

		{if $category->annotation}
			<div class="seo-announce">
				{$category->annotation|raw}
			</div>
		{/if}


		<div class="block-item-list">
			{if $products->isNotEmpty()}
				{foreach $products as $product}
					{include file='parts/product_item.tpl'}
				{/foreach}
			{/if}
		</div>


		{include file="parts/pagination.tpl"}

		{if $category->description AND $show_description}
			<div class="description_html category_description">
				{$category->description|raw}
			</div>
		{/if}
	</div>

{/block}