<header class="mb-4">
	<div class="bg-body-tertiary">
		<nav class="navbar navbar-expand-lg bg-body-tertiary">
			<div class="container">

				<div class="navbar-collapse collapse" id="navbarTogglerDemo03">
					<div class="navbar-nav me-auto mb-2 mb-lg-0">
						<div class="nav-item">
							<a class="nav-link {if $route|in_array:[PostList, Post]}active{/if}"
								{if $route|in_array:[PostList, Post]}aria-current="page" {/if}
								href="{'PostList'|linkLang}">База
								знаний</a>
						</div>

						{foreach 'ContentPage'|api:getMenu as $m}
							<div class="nav-item">
								<a class="nav-link {if $page->id == $m->id}active{/if}"
									{if $page->id == $m->id}aria-current="page" {/if}
									href="{'Page'|linkLang:[url => $m->url]}">{$m->name}</a>
							</div>
						{/foreach}
					</div>
				</div>

				<button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse"
					data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false"
					aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="navbar-text">
					<div class="row">

						<div class="col-2">
							<div class="lang">
								{foreach $languages as $lang}
									<a href="{'current'|linkLang:['locale' => $lang->code]}" title="{$lang->name}"
										data-bs-toggle="tooltip">{$lang->code}</a>
								{/foreach}
							</div>
						</div>

						<!-- User Login -->
						<div class="col-7 text-end" id="account">
							{if !$user|empty}
								<span>
									<a href="{'UserOrderList'|linkLang}">{$user->name}</a>
								</span>
								<a class="logout ms-3" href="{'UserLogout'|linkLang}">{'выйти'|trans}</a>
							{else}
								<a class="login ms-3" href="{'UserLogin'|linkLang}" rel="nofollow">{'войти'|trans}</a>
							{/if}
						</div>

						<div class="col-3" id="cart_informer">
							{block name=cart_informer}
								<a class="cart_info float-end" href="{'Cart'|linkLang}" rel="nofollow"
									data-bs-toggle="tooltip" data-bs-html="true"
									title="{$cart->purchases_count} {$cart->purchases_count|plural:'товар':'товаров':'товара'}. {if $cart->purchases_price > 0}</br> На сумму: {$cart->purchases_price|price_html:no_html}{/if}">

									<svg class="cart-icon" viewBox="0 0 1024 1024">
										<path
											d="M97.718857 109.714286a109.714286 109.714286 0 0 1 107.349333 87.064381L220.16 268.190476h0.24381l49.005714 234.666667L306.541714 682.666667h459.678476l70.460953-341.333334H285.500952l-15.286857-73.142857h566.491429a73.142857 73.142857 0 0 1 71.631238 87.942095l-70.460952 341.333334A73.142857 73.142857 0 0 1 766.22019 755.809524H306.541714a73.142857 73.142857 0 0 1-71.631238-58.343619l-69.241905-335.335619-0.463238 0.097524-31.695238-150.357334A36.571429 36.571429 0 0 0 97.718857 182.857143H35.157333v-73.142857zM304.761905 926.47619a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z m438.857143 0a60.952381 60.952381 0 1 0 0-121.904761 60.952381 60.952381 0 0 0 0 121.904761z">
										</path>
									</svg>

									{if $cart->purchases_count > 0}
										<div class="badge rounded-pill bg-danger">{$cart->purchases_count}</div>
									{/if}
								</a>
							{/block}
						</div>
					</div>
				</div>
			</div>
		</nav>
	</div>

	<div class="header_middle my-4">
		<div class="container">
			<div class="row mb-4 mb-lg-0">

				<div class="col-12 col-lg-3 d-flex d-lg-block justify-content-center">
					<div class="logo">
						<a href="{'Main'|linkLang}" data-bs-toggle="tooltip"
							title="{$settings->company_name} - {$settings->company_description}">
							<img loading="lazy" alt="{$settings->company_name} - {$settings->company_description}"
								src="{'images/logo.png'|asset}" />
						</a>
					</div>
				</div>

				<div class="col-12 col-lg-5 mt-2">
					<form id="search" action="/s">
						<div class="input-group">
							<input id="search_input" class="form-control" type="text" name="keyword" value="{$keyword}"
								placeholder="Поиск, например: шпиндель" />

							<button class="btn btn-primary" type="submit">
								<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
								<span class="btn-content">
									<svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
										<path
											d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z">
										</path>
									</svg>
								</span>
							</button>
						</div>
					</form>

					<script type="module">
						$('#search_input').typePlaceholder({
							words: ["шпиндель", "редуктор", "подшипник sbr", "вал на опоре", "блок питания"]
						});
					</script>
				</div>

				<div class="col-12 col-lg-4 mt-4 mt-lg-0">
					<div class="row contact">
						{include file='parts/phones.tpl'}
					</div>
				</div>

			</div>

			{addon name='InfoBlock' id=2 enabled=1}
		</div>
	</div>

	<div class="header_buttom my-1">
		<div class="container">
			<div class="menu-scroller overflow-x-auto bg-body-tertiary">
				<nav class="nav">
					{foreach $categories as $cat}
						<div class="nav-item py-1 {if $cat@last}last{/if}{if $cat@first}first{/if}">
							<a class="nav-link {if $category->id == $cat->id}active{/if}"
								href="{'Products'|linkLang:[url=>$cat->url]}">
								{$cat->name}
							</a>
						</div>
					{/foreach}
				</nav>
			</div>
		</div>
	</div>
</header>