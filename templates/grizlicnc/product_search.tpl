{extends 'wrapper/main.tpl'}

{block name=content}

	<!-- Breadcrumbs -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">

			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item">
					<span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1">
				</a>
			</li>
			<li>→</li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">{'Поиск'|trans}</span>
				<meta itemprop="position" content="2">
			</li>
		</ul>
	</div>

	<div class="row">

		<div class="col-lg-9 wrap_products wide">

			{* Заголовок страницы *}
			<h1>{'Поиск'|trans} {$keyword}</h1>


			<!-- Products -->
			{if $products}

				{* Features filter *}
				{if $features}
					<table id="features">
						{foreach $features as $key=>$f}
							<tr>
								<td class="feature_name" data-feature="{$f->id}">
									{$f->name}:
								</td>
								<td class="feature_values">
									<a href="{url params=[$f->id=>null, page=>null]}" {if !$smarty.get.$key}class="selected"
										{/if}>Все</a>
									{foreach $f->options as $o}
										<a href="{url params=[$f->id=>$o->value, page=>null]}"
											{if $smarty.get.$key == $o->value}class="selected" {/if}>{$o->value}</a>
									{/foreach}
								</td>
							</tr>
						{/foreach}
					</table>
				{/if}


				{* Product sort *}
				{if $products|count > 0}
					<div class="sort">
						{'Сортировать по'|trans}
						<a {if $sort=='position'} class="selected" {/if}
							href="{url sort=position page=null}">{'умолчанию'|trans}</a>
						<a {if $sort=='price'} class="selected" {/if} href="{url sort=price page=null}">{'цене'|trans}</a>
					</div>
				{/if}

				<ul class="product_list products gallerywide catalog" list_id="{$category->url}" list_name="{$category->name}">
					{foreach $products as $product}
						{include file='parts/product_item.tpl'}
					{/foreach}
				</ul>

				{include file="parts/pagination.tpl"}

			{else}
				{'Товары не найдены'|trans}
			{/if}

		</div>
	</div>
{/block}