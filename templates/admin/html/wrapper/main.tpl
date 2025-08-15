<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width initial-scale=1">

	<title>{$meta_title}</title>

	<link rel="icon" href='{"images/favicon.ico"|asset:"{$settings->theme}/assets"}' type="image/x-icon" />

	{importmap point='admin'}

	<script type="module">
		window.csrf = "{setCSRF}";

		{if $locked_key}
			import { initEditLock } from '{"js/lock.edit.js"|asset}';
			initEditLock({
				lock_url: "{'LockEditAjax'|link:[locked_key => $locked_key]}",
				unlock_url: "{'UnlockEditAjax'|link:[locked_key => $locked_key]}"
			});
		{/if}
	</script>

	{block name=head_css}{/block}
	{block name=head_script}{/block}

</head>

<body>

	<div class="wrap d-xxl-flex">

		<!-- Sidebar -->
		<div class="navbar_vertical navbar d-flex flex-column navbar-expand-xxl">
			<div class="container flex-xxl-column ps-xxl-4">

				<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="offcanvas offcanvas-start" id="sidebar">
					<div class="offcanvas-header">
						<h5 class="offcanvas-title" id="offcanvasLabel"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
					</div>
					<div class="offcanvas-body">
						<ul id="main_menu" class="ps-2">


							{if 'order'|user_access}
								<li>
									<span>
										<div class="menu_icon">
											<img loading="lazy" src="{'images/menu/orders.png'|asset}" />
										</div>
										<b>CRM</b>
									</span>

									<ul>
										<li
											class="mini {if $route|in_array:[OrderListAdmin, OrderAdmin, OrderNewAdmin]}active{/if}">
											<a href="{'OrderListAdmin'|link:[status => 0]}">Заказы</a>
											{if $orders_info_count[0]}
												<div class="badge rounded-pill bg-danger">
													{$orders_info_count[0]}</div>
											{/if}
										</li>

										<li class="mini {if $route|in_array:['CartListAdmin']}active{/if}">
											<a href="/admin/order/carts">Корзины</a>
										</li>

										{if 'user_manager'|user_access}
											<li class="mini right {if $route|in_array:[ManagerProfitAdmin]}active{/if}">
												<a href="/admin/order/manager_profit">Доход менеджера</a>
											</li>
										{/if}

										{if 'order_payment'|user_access}
											<li
												class="mini {if $route|in_array:[OrderPaymentListAdmin, OrderPaymentAdmin, OrderPaymentNewAdmin]}active{/if}">
												<a href="/admin/order/payments">Оплата</a>
											</li>
										{/if}

										{if 'order_delivery'|user_access}
											<li
												class="mini {if $route|in_array:[DeliveryListAdmin, OrderDeliveryAdmin, OrderDeliveryNewAdmin]}active{/if}">
												<a href="/admin/order/deliveries">Доставка</a>
											</li>
										{/if}

										{if 'order_label'|user_access}
											<li
												class="mini {if $route|in_array:[LabelListAdmin, LabelAdmin, LabelNewAdmin]}active{/if}">
												<a href="/admin/order/labels">Метки</a>
											</li>
										{/if}
									</ul>
								</li>
							{/if}


							{if ['product_view', 'warehouse']|user_access}
								<li>
									<span>
										<div class="menu_icon">
											<img loading="lazy" src="{'images/menu/catalog.png'|asset}" />
										</div>
										<b>Склад</b>
									</span>

									<ul>
										{if 'product_view'|user_access}
											<li
												class="mini {if $route|in_array:[ProductListAdmin, ProductAdmin, ProductPriceAdmin, ImportProductPAdmin]}active{/if}">
												<a href="{'ProductListAdmin'|link}">Товары</a>
											</li>
										{/if}

										{if 'warehouse'|user_access}
											<li class="mini {if $route|in_array:['MoveAdmin','MoveListAdmin']}active{/if}">
												<a href="/admin/warehouse/moves">Поставки</a>
											</li>
										{/if}
									</ul>
								</li>
							{/if}


							{if ['user', 'user_coupon', 'user_notifier']|user_access}
								<li>
									<span>
										<div class="menu_icon">
											<img loading="lazy" src="{'images/menu/users.png'|asset}">
										</div>
										<b>Клиенты</b>
									</span>

									<ul>
										{if 'user'|user_access}
											<li
												class="mini {if $route|in_array:[UserListAdmin, UserAdmin, UserSettingsAdmin]} active{/if}">
												<a href="/admin/users">Покупатели</a>
											</li>
										{/if}

										{if 'user_notifier'|user_access}
											<li
												class="mini {if $route|in_array:[MailingNewAdmin, MailingAdmin, MailingListAdmin, NotifierAdmin, NotifierListAdmin, NotifierNewAdmin, MailTemplateNewAdmin, MailTemplateListAdmin, MailTemplateAdmin]}active{/if}">
												<a href="/admin/user/mailings">Список рассылки</a>
											</li>
										{/if}

										{if 'user_coupon'|user_access}
											<li
												class="right mini {if $route|in_array:[CouponListAdmin, CouponAdmin, CouponNewAdmin]}active{/if}">
												<a href="/admin/user/coupons">Купоны</a>
											</li>
										{/if}
									</ul>
								</li>
							{/if}


							{if ['blog', 'comment', 'feedback', 'page']|user_access}
								<li>
									<span>
										<div class="menu_icon">
											<img loading="lazy" src="{'images/menu/pages.png'|asset}">
										</div>
										<b>Контент</b>
									</span>

									<ul>
										{if 'blog'|user_access}
											<li
												class="mini {if $route|in_array:[PostAdmin, PostListAdmin, PostNewAdmin]}active{/if}">
												<a href="/admin/posts">Блог</a>
											</li>
										{/if}

										{if 'comment'|user_access}
											<li class="mini {if $route|in_array:[CommentListAdmin, CommentAdmin]}active{/if}">
												<a href="/admin/comments">Комментарии</a>
												{if $new_comments_counter}
													<div class="badge rounded-pill bg-danger">
														{$new_comments_counter}
													</div>
												{/if}
											</li>
										{/if}


										{if 'feedback'|user_access}
											<li class="mini right {if $route|in_array:[FeedbackListAdmin]}active{/if}">
												<a href="/admin/feedbacks">Обратная связь</a>
											</li>
										{/if}

										{if 'page'|user_access}
											<li class="mini right  {if $route|in_array:[PageListAdmin, PageAdmin]}active{/if}">
												<a href="/admin/pages">Страницы</a>
											</li>
										{/if}
									</ul>
								</li>
							{/if}


							{if ['finance', 'stats']|user_access}
								<li>
									<span>
										<div class="menu_icon">
											<img loading="lazy" src="{'images/menu/finance.png'|asset}">
										</div>
										<b>Финансы</b>
									</span>

									<ul>
										{if 'finance'|user_access}
											<li
												class="mini {if $route|in_array:[PaymentListAdmin, PaymentAdmin, PaymentNewAdmin]}active{/if}">
												<a href="{'PaymentListAdmin'|link}">Платежи</a>
											</li>
										{/if}
										{if 'stats'|user_access}
											<li class="mini {if $route|in_array:[StatsAdmin]}active{/if}">
												<a href="{'StatsAdmin'|link}">Статистика продаж</a>
											</li>
										{/if}
										{if 'finance'|user_access}
											<li class="mini {if $route|in_array:[CurrencyAdmin]}active{/if}">
												<a href="{'CurrencyAdmin'|link}">Валюты</a>
											</li>
										{/if}
									</ul>
								</li>
							{/if}


							{if 'extension'|user_access}
								<li>
									<a href="{'ExtensionListAdmin'|link}">
										<div class="menu_icon">
											<img loading="lazy" src="{'images/menu/wizards.png'|asset}">
										</div>
										<b>Модули</b>
									</a>
								</li>
							{/if}


							{if ['settings', 'backup', 'design']|user_access}
								<li>
									<span>
										<div class="menu_icon">
											<img loading="lazy" src="{'images/menu/settings.png'|asset}">
										</div>
										<b>Настройки</b>
									</span>

									<ul>
										{if 'settings'|user_access}
											<li class="mini {if $route == 'SettingsAdmin'}active{/if}">
												<a href="{'SettingsAdmin'|link}">Основные настройки</a>
											</li>
										{/if}

										{if 'settings'|user_access}
											<li class="mini {if $route == 'LanguageListAdmin'}active{/if}">
												<a href="{'LanguageListAdmin'|link}">Языки</a>
											</li>
										{/if}

										{if 'backup'|user_access}
											<li class="{if $route == 'BackupAdmin'}active{/if}">
												<a href="{'BackupAdmin'|link}">Бекап</a>
											</li>
										{/if}

										{if 'design'|user_access}
											<li
												class="{if $route|in_array:[ImagesAdmin, ThemeAdmin, StylesAdmin, TemplatesAdmin, ThemeAdmin]}active{/if}">
												<a href="{'ThemeAdmin'|link}">Тема</a>
											</li>
										{/if}
									</ul>
								</li>
							{/if}
						</ul>
					</div>
				</div>

				<div class="current_user">
					<a class="user_name" href="{'UserAdmin'|link:['id' => $user->id]}">{$user->name}</a>
					<a href="{'UserLogout'|link}" id="logout">Выход</a>
				</div>
			</div>
		</div>


		<!-- Content -->
		<div class="flex-grow-1 pt-3">
			<div class="container">

				{include file='parts/service_messages_part.tpl'}

				<ul id="tab_menu" class="overflow-x-auto">
					{block name=tabs}{/block}
				</ul>

				<main class="main_content">
					{block name=subtabs}{/block}
					{block name=content}{/block}
				</main>

				<footer class="mt-4 mb-4">
					<span>HugaShop v2.5</span>
				</footer>
			</div>
		</div>

	</div>

	<!-- Body Script -->
	{block name=body_script}{/block}


	<!-- Admin Bookmark -->
	<a class="admin_bookmark" href="{'Main'|link}" data-bs-toggle="tooltip" title="Перейти на сайт">
		<svg viewBox="0 0 24 24" focusable="false" class="dyAbMb">
			<path d="M0 0h24v24H0z" fill="none"></path>
			<path d="M8.59,16.59L13.17,12L8.59,7.41L10,6l6,6l-6,6L8.59,16.59z"></path>
		</svg>
	</a>
</body>

</html>