{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='Товары'}

{block name=content}
    <div class="two_columns_list">
        <div class="header_top">
            <h1>Все товары <span class="sum_total">{$products_count}
                    {$products_count|plural:'товар':'товаров':'товара'}</span></h1>

            <!-- Search -->
            <form method="get" id="search">
                <div class="input-group">
                    <input class="search form-control" type="text" name="keyword" value="{$keyword}"
                        placeholder="Название, артикул" />
                    <input class="input-group-text search_button" type="submit" value="" />
                </div>
            </form>
        </div>


        <!-- Меню -->
        <div class="navbar-expand-lg" id="right_menu">

            <div class="popup_menu_btn navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#filter_menu_block">
                <span class="material-icons">menu</span>
                <span class="popup_btn_text">Фильтр</span>
            </div>

            <div class="offcanvas offcanvas-start" id="filter_menu_block" tabindex="-1" aria-labelledby="offcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                <div class="offcanvas-body">

                    <!-- Фильтры -->
                    <ul class="menu_list layer">
                        <li class="{if !$filter}selected{/if}">
                            <a href="{url page=null filter=null date_from=null}">Все товары</a>
                        </li>
                        <li class="{if $filter == 'sale'}selected{/if}">
                            <a href="{url page=null filter='sale' date_from=null}">Акция</a>
                        </li>
                        <li class="{if $filter == 'featured'}selected{/if}">
                            <a href="{url page=null filter='featured' date_from=null}">Рекомендуемые</a>
                        </li>
                        <li {if $filter == 'discounted'}class="selected" {/if}>
                            <a href="{url page=null filter='discounted' date_from=null}">Со скидкой</a>
                        </li>
                        <li {if $filter == 'visible'}class="selected" {/if}>
                            <a href="{url page=null filter='visible' date_from=null}">Активные</a>
                        </li>
                        <li {if $filter == 'hidden'}class="selected" {/if}>
                            <a href="{url page=null filter='hidden' date_from=null}">Неактивные</a>
                        </li>
                        <li {if $filter == 'outofstock'}class="selected" {/if}>
                            <a href="{url page=null filter='outofstock' date_from=null}">Нет в наличии</a>
                        </li>
                        <li {if $filter == 'instock'}class="selected" {/if}>
                            <a href="{url page=null filter='instock' date_from=null}">В наличии</a>
                        </li>

                        {if 'product_price'|user_access}
                            <li {if $filter == 'stagnation'}class="selected" {/if}>
                                <a href="{url keyword=null page=null filter='stagnation' date_from=null}">Застой склада</a>
                            </li>

                            <li {if $filter == 'purchase'}class="selected" {/if}>
                                <a href="{url keyword=null page=null filter='purchase' date_from='-60 days'|date:'Y-m-d'}">Необходимо
                                    закупить</a>
                            </li>

                            <li {if $filter == 'top' AND $date_from == '-30 days'|date:'Y-m-d'}class="selected" {/if}>
                                <a href="{url keyword=null page=null filter='top' date_from='-30 days'|date:'Y-m-d'}">Лучшие
                                    продажи за 30 дней</a>
                            </li>
                            <li {if $filter == 'top' AND $date_from == '-90 days'|date:'Y-m-d'}class="selected" {/if}>
                                <a href="{url keyword=null page=null filter='top' date_from='-90 days'|date:'Y-m-d'}">Лучшие
                                    продажи за 90 дней</a>
                            </li>
                        {/if}
                    </ul>
                </div>

            </div>
        </div>


        <div id="main_list">
            {if $products}
                
                {include file='parts/pagination.tpl'}

                <div class="list">
                    {foreach $products as $product}
                        <div class="list_row" item_id="{$product->id}">
                            <div class="image">
                                <img
                                    src="{if $product->image->filename}{$product->image->filename|resize:60:60:c}{else}{'images/cargo.png'|asset}{/if}" />
                            </div>

                            <div class="col row">
                                <div class="col">
                                    <a
                                        href="{'ProductAdmin'|link:[id=>$product->id]}?return={$smarty.server.REQUEST_URI}">{$product->name}</a>
                                    {if $product->variant_name}<span class="small"> - {$product->variant_name}</span>{/if}
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="row">
                                        <div class="col-6 text-end">
                                            {if $product->sku}
                                                <div class="badge text-bg-round copy_field" value="{$product->sku}">{$product->sku}
                                                    <div class="copy_hover" data-bs-toggle="tooltip"
                                                        data-bs-original-title="Скопировать">
                                                        <i class="material-icons">content_copy</i>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                        <div class="col-6 text-end">
                                            {if $product->stock !== null}
                                                <span class="badge text-bg-secondary">{$product->stock}</span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
                {include file='parts/pagination.tpl'}
            {else}
                <div class="p-3">Нет товаров</div>
            {/if}
        </div>
    </div>
{/block}