{* @version 0.1 *}
<div class="header-upper-wrapper">
	<div class="header-upper asva-container">

		<!-- Меню -->
		<div class="header-upper-links-wrapper" id="active_upper_menu">
			<button type="button" name="button" onclick="openTip('upper_menu'); return false;">
				<i></i>
				<i></i>
				<i></i>
			</button>
			<div class="header-upper-links">
				<span class="ico ico-close" onclick="closeTip('upper_menu'); return false;">x</span>
				<div class="header-upper-menu-links">
					{foreach 'ContentPage'|api:getMenu as $m}
						<a class="{if $page->id == $m->id}link-contact{/if}" {if $page->id == $m->id}aria-current="page"
						{/if} href="{'Page'|linkLang:[url => $m->url]}">{$m->name}</a>
					{/foreach}
				</div>
			</div>
		</div>

		<div class="header-logo">
			<a href="{'Main'|linkLang}"><img src="{'images/logo-m.png'|asset}" rel="nofollow"></a>
		</div>


		<div class="header-upper-menu" id="active_header_search">

			<!-- Окно поиска -->
			<div class="header-under-search">
				<form action="/search/" class="header-search-form">
					<input type="text" name="q" id="front_search_query" class="header-search-left" value=""
						placeholder="Поиск, например 175/65 R15">
					<input type="hidden" id="source" name="source" value="" />
					<button type="button" class="header-search-bt"
						onclick="$('.header-search-form').submit()">Search</button>
					<span class="close" onclick="closeTip('header_search'); return false;">x</span>
				</form>
			</div>

			<a href="#" class="search" onclick="openTip('header_search'); return false;">
				<i class="ico ico-search"></i>
			</a>


			<a href="#" class="phone" onclick="openTip('mob_phones'); return false;">
				<i class="ico ico-phone"></i>
			</a>


			<!-- Корзина -->
			<span id="cart_informer">
				{block name=cart_informer}
					<a href="{'Cart'|linkLang}" rel="nofollow" class="basket"
						title="{$cart->purchases_count} {$cart->purchases_count|plural:'товар':'товаров':'товара'}. {if $cart->purchases_price > 0}</br> На сумму: {$cart->purchases_price|price_html:no_html}{/if}">
						<i class="ico ico-basket"></i>

						{if $cart->purchases_count > 0}
							<span>
								{$cart->purchases_count}
							</span>
						{/if}
					</a>
				{/block}
			</span>


			<!-- Вход -->
			{if $user}
				<span class="user-area" id="userlogin">
					<i class="ico ico-user"></i>
					<span class="user-name">
						<a href="{'UserOrderList'|linkLang}">
							{$user->name}
						</a>
					</span>
					<span>
						<a id="user_logout" href="{'UserLogout'|linkLang}">{'выйти'|trans}</a>
					</span>
				</span>
			{else}
				<a href="{'UserLogin'|linkLang}" class="user-area user-name" rel="nofollow">
					<i class="ico ico-user"></i>
					<span class="user-name">{'Вход в магазин'|trans}</span>
				</a>
			{/if}

		</div>
	</div>
</div>


<div class="header-wrapper">
	<div class="header asva-container">
		<div class="header-logo hidden-phone">
			<a href="{'Main'|linkLang}" title="Интернет магазин шин и дисков АСВА">
				<img src="{'images/logo.png'|asset}" alt="Интернет магазин шин и дисков АСВА">
			</a>
		</div>
		<div class="header-links">
			<a href="{'Products'|linkLang:[url => 'shini']}" title="Шины на авто">
				<i class="ico ico-tire-car"></i>
				<span>Шины</span>
			</a>
			<a href="{'Products'|linkLang:[url => 'diski']}" title="Диски на авто">
				<i class="ico ico-tire-disk"></i>
				<span>диски</span>
			</a>
			<a href="{'Page'|linkLang:[url => 'uslugi-shinomontaga']}" title="Шиномонтаж">
				<i class="ico ico-tire-fitting"></i>
				<span>шиномонтаж</span>
			</a>
			<a href="{'Page'|linkLang:[url => 'tire-storage']}" title="Хранение">
				<i class="ico ico-tire-storage"></i>
				<span>Хранение</span>
			</a>
		</div>


		<!-- Контакты -->
		<div class="header-contacts ">
			<div class="phone hidden-phone">
				<i class="ico ico-phone"></i>
				<a class="phone-number" href="tel:+380445007500">(044) 500-7-500</a>
			</div>

			<div class="all-phones">
				<a class="hidden-phone" href="#" onclick="openTip('mob_phones'); return false;">
					<span>Ещё</span>
					<span>телефоны</span>
					<i class="ico ico-arrow-right"></i>
				</a>

				<div class="mobiles" id="active_mob_phones">
					<span class="ico ico-close" onclick="closeTip('mob_phones'); return false;">x</span>
					<div class="main-phone"><a class="phone-number" href="tel:+380445007500">(044) 500-7-500</a>
					</div>
					<div class="open-time">
						<span>ГРАФИК РАБОТЫ КОЛЛ-ЦЕНТРА</span>
						<span>Пн-Пт 9:00-20:00<br />Сб 9:00-16:00</span>
					</div>
					<div>
						<i class="ico ico-mobile kyivstar"></i>
						<a class="phone-number" href="tel:+380964044879">(096) 404-48-79</a>
					</div>
					<div>
						<i class="ico ico-mobile vodafone"></i>
						<a class="phone-number" href="tel:+380954044879">(095) 404-48-79</a>
					</div>
					<div>
						<i class="ico ico-mobile lifecell"></i>
						<a class="phone-number" href="tel:+380934044879">(063) 404-48-79</a>
					</div>
					<div>
						<i class="ico ico-mobile viber"></i>
						<a href="viber://add?number=380500548649">Viber</a>
					</div>
					<div>
						<i class="ico ico-mobile telegram"></i>
						<a class="phone-number" href="https://t.me/asva_tires" rel="nofollow"
							target="_blank">@asva_tires</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>