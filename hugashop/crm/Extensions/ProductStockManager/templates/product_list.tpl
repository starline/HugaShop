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
                            <a href="{url clear=true}">Все товары</a>
                        </li>
                        <li {if $filter == 'outofstock'}class="selected" {/if}>
                            <a href="{url filter='outofstock' clear=true}">Нет в наличии</a>
                        </li>
                        <li {if $filter == 'stagnation'}class="selected" {/if}>
                            <a href="{url filter='stagnation' clear=true}">Застой склада</a>
                        </li>
                        <li {if $filter == 'purchase'}class="selected" {/if}>
                            <a href="{url filter='purchase' date_from='-60 days'|date:'Y-m-d' clear=true}">Необходимо
                                закупить</a>
                        </li>
                        <li {if $filter == 'top' AND $date_from == '-30 days'|date:'Y-m-d'}class="selected" {/if}>
                            <a href="{url filter='top' date_from='-30 days'|date:'Y-m-d' clear=true}">Лучшие
                                продажи за 30 дней</a>
                        </li>
                        <li {if $filter == 'top' AND $date_from == '-90 days'|date:'Y-m-d'}class="selected" {/if}>
                            <a href="{url filter='top' date_from='-90 days'|date:'Y-m-d' clear=true}">Лучшие
                                продажи за 90 дней</a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>


        <div id="main_list">
            {if $products}

                {include file='parts/pagination.tpl'}

                <div class="list">
                    {foreach $products as $product}
                        <div class="list_row {if !$product->visible}visible_off{/if} {if $product->disable}disable{/if} {if !$product->featured}featured_off{/if} {if !$product->sale}sale_off{/if}"
                            item_id="{$product->id}">

                            <div class="col row">
                                <div class="col_image image">
                                    <img
                                        src="{if $product->image->filename}{$product->image->filename|resize:60:60:c}{else}{'images/cargo.png'|asset}{/if}" />
                                </div>

                                <div class="col">
                                    <a
                                        href="{'ProductAdmin'|link:[id => $product->id]}?return={$smarty.server.REQUEST_URI}">{$product->name}</a>

                                    {if $product->variant_name}
                                        <i class="small"> - {$product->variant_name}</i>
                                    {/if}

                                    {if $product->order_date}
                                        <div class="notice" data-bs-toggle="tooltip" title="Дата последнего заказа">
                                            Последний заказ: <span>{$product->order_date|date}</span>
                                            прошло <span>{(($config->now - $product->order_date|strtotime)/60/60/24)|round}</span>
                                            дней
                                        </div>
                                    {elseif $product->profit}
                                        <div class="notice">
                                            Прибыль: {$product->profit|price_html:profit|raw}
                                            Продано <span>{$product->sold} {$settings->units}</span>
                                        </div>
                                    {elseif $product->need}
                                        <div class="notice">
                                            Нужно закупить: <span>{$product->need} {$settings->units}</span>
                                            Продано <span>{$product->sold} {$settings->units}</span>
                                        </div>
                                    {elseif $product->order_date|is_null}
                                        <div class="notice">Ни разу не был заказан</div>
                                    {/if}

                                    <div class="icons flex-row mt-2">
                                        <a class="show_chart" data-bs-toggle="tooltip" title="Показать график продаж"></a>
                                        <a class="featured" data-bs-toggle="tooltip" title="Рекомендуемый"></a>
                                        <a class="sale" data-bs-toggle="tooltip" title="Акция"></a>
                                        <i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Активен"
                                            title="Активен"></i>
                                        <a class="material-icons launch" data-bs-toggle="tooltip" title="Предпросмотр в новом окне"
                                            href="{'ProductId'|link:[id => $product->id]}" target="_blank"></a>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4 variants">
                                    <div class="row">
                                        <div class="col-4 text-end">
                                            {if $product->sku}
                                                <div class="badge text-bg-round copy_field" value="{$product->sku}">{$product->sku}
                                                    <div class="copy_hover" data-bs-toggle="tooltip"
                                                        data-bs-original-title="Скопировать">
                                                        <i class="material-icons">content_copy</i>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>

                                        <span class="col-4 price">
                                            <a data-bs-toggle="tooltip" data-bs-html="true" {if $product->cost_price > 0}
                                                    title="Оптовая цена &mdash; {$product->cost_price|number} {$currency->sign}</br>Доход &mdash; {$product->profit_price|number} {$currency->sign}</br> Старая цена  &mdash; {$product->old_price|number} {$currency->sign}"
                                                {/if}
                                                href="{'ProductPriceAdmin'|link:[id => $product->id]}?return={$smarty.server.REQUEST_URI}">{$product->price|price_html|raw}</a>
                                        </span>

                                        <span class="col-4">
                                            <div class="stock">
                                                {if $product->stock|is_null}
                                                    ∞
                                                {else}
                                                    {$product->stock} {$settings->units}
                                                {/if}

                                                {if $product->movements_amount}
                                                    <span class="wmovements" data-bs-toggle="tooltip" data-bs-html="true"
                                                        title="{foreach $product->movements as $mov}<div class='text-nowrap'>Поставка №{$mov->move_id} | {$mov->awaiting_date|date} | +{$mov->amount}</div>{/foreach}">+{$product->movements_amount}</span>
                                                {/if}
                                            </div>
                                        </span>
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