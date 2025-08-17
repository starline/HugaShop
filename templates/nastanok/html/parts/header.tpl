<!-- Header top -->
<div class="container" id="header_top">
	<div class="row align-items-center">

		<!-- Меню -->
		<div class="menu col-lg-9">
                        <div class=" {if $route|in_array:[PostList, Post]}selected{/if}">
                                <a href="{'PostList'|linkLang}">База знаний</a>
                        </div>

			{foreach 'ContentPage'|api:getMenu as $m}
				<div {if !$page|empty && $page->id == $m->id}class="selected" {/if}>
                                        <a data-page="{$p->id}" href="{'Page'|linkLang:[url => $m->url]}" data-bs-toggle="tooltip"
                                                title="{$m->name}">{$m->name}</a>
				</div>
			{/foreach}
		</div>

		<!-- Вход пользователя -->
		<div class="col-lg-3 text-end ps-2">
			{if $user->name}
				<span class="link-primary">
                                        <a href="{'User'|linkLang}" data-bs-toggle="tooltip"
                                                title="{if $user->group->discount>0}ваша скидка: {$user->group->discount}%{/if}"
                                                rel="nofollow">{$user->name}</a>
                                </span>
                                <a class="link-secondary ms-2" href="{'UserLogout'|linkLang}" rel="nofollow">выйти</a>
                        {else}
                                <a class="link-primary" href="{'UserLogin'|linkLang}" rel="nofollow">Вход</a>
                        {/if}
		</div>
	</div>
</div>


<!-- Header -->
<div class="container my-4" id="header">
	<div class="row g-4">

		<div class="col-6 col-lg-4">
                        <a class="logo" href="{'Main'|linkLang}" data-bs-toggle="tooltip"
                                title="{$settings->company_name} - {$settings->company_description}">
				<img loading="lazy" alt="{$settings->company_name} - {$settings->company_description}"
					src="{'images/logo.png'|asset}" />
			</a>
		</div>

		<div class="col-6 col-lg-4">
			<div class="search_wrap">
				<form id="search" action="/s">

					<input class="input_search" type="text" name="keyword" value="{$keyword}"
						placeholder="Поиск, например: фрезы">

					<span class="search_button">
						<svg id="search_icon" viewBox="0 0 4.15758 4.15745">
							<path
								d="M4.04343 3.49209l-0.954009 -0.953705c-0.142071,0.220816 -0.330047,0.408944 -0.551116,0.55104l0.953882 0.953806c0.1523,0.152275 0.399171,0.1523 0.551217,0 0.152199,-0.151946 0.152199,-0.398841 2.532e-005,-0.551141z">
							</path>
							<path
								d="M1.55908 2.72829c-0.644724,0 -1.16928,-0.524581 -1.16928,-1.16915 5.06401e-005,-0.644826 0.524505,-1.16941 1.16925,-1.16941 0.644826,-2.532e-005 1.16933,0.524581 1.16933,1.16941 5.06401e-005,0.644572 -0.524505,1.16915 -1.16931,1.16915zm1.55913 -1.16915c0,-0.86116 -0.698175,-1.55913 -1.55916,-1.55913 -0.860907,0 -1.55906,0.697972 
								-1.55906,1.55913 0,0.860882 0.69815,1.55898 1.55906,1.55898 0.861033,-2.532e-005 1.55913,-0.698074 1.55916,-1.55898z">
							</path>
							<path
								d="M0.649662 1.55913l0.25986 0c0,-0.358177 0.291434,-0.649738 0.649535,-0.649738l2.532e-005 -0.259758c-0.501388,0 -0.909445,0.407855 -0.90942,0.909496z">
							</path>
						</svg>
					</span>
				</form>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="row">
				<div class="col-9">
					{include file='parts/phones.tpl'}
				</div>

				<!-- Корзина -->
				<div class="col-3 clearfix" id="cart_informer">
					{* Обновляемая ajax корзина должна быть в отдельном файле *}
					{include file='parts/cart_informer.tpl'}
				</div>
			</div>
		</div>
	</div>

	<div class="soc-info">
		{extension  name='InfoBlock' id=1}
	</div>

	<div class="products-catalog-menu">
		<table id="action-zone">
			<tbody>
				<tr>
					{foreach $categories as $cat}
						{if $cat->visible}
							<td class="{if $cat@last}last{/if}{if $cat@first}first{/if}">
                                                                <a href="{'Products'|linkLang:[url=>$cat->url]}" class="p-c-title">
									<span class="p-c-title-text">{$cat->name}</span>
								</a>
							</td>
						{/if}
					{/foreach}
				</tr>
			</tbody>
		</table>
	</div>
</div>