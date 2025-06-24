{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{* Meta Title *}
{if $category}
    {$meta_title=$category->name}
{else}
    {$meta_title='Товары'}
{/if}

{block name=content}
    <div class="two_columns_list">
        <div class="header_top">
            {if $category->name}
                <h1>{$category->name}<span class="sum_total">{$products_count} {$products_count|plural:'товар':'товаров':'товара'}</span></h1>
            {else}
                <h1>Все товары <span class="sum_total">{$products_count} {$products_count|plural:'товар':'товаров':'товара'}</span></h1>
            {/if}
            <form method="post" class="d-inline-block ms-2">
                {getCSRFInput}
                <button class="btn btn-primary" type="submit" name="calculate" value="1">Посчитать</button>
            </form>
            <form method="get" id="search">
                {getCSRFInput}
                <div class="input-group">
                    <input class="search form-control" type="text" name="keyword" value="{$keyword}" placeholder="Название, артикул"/>
                    <input class="input-group-text search_button" type="submit" value="" />
                </div>
            </form>
        </div>

        <div class="navbar-expand-lg" id="right_menu">
            <div class="offcanvas offcanvas-start show-static">
                <div class="offcanvas-body">
                    {include file='parts/categories_tree_part.tpl'}
                </div>
            </div>
        </div>

        <div id="main_list">
            {if $products}
                {include file='parts/pagination.tpl'}
                <div class="list">
                    {foreach $products as $product}
                        <div class="list_row">
                            <div class="image">
                                <img src="{if $product->image->filename}{$product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}" />
                            </div>
                            <div class="col">
                                <a href="{'ProductAdmin'|urll:[id=>$product->id]}?return={$smarty.server.REQUEST_URI}">{$product->name}</a>
                                {if $product->variant_name}
                                    <div class="small text-muted">{$product->variant_name}</div>
                                {/if}
                            </div>
                            <div class="col-auto fw-bold align-self-center">{$product->filling}%</div>
                        </div>
                    {/foreach}
                </div>
                {include file='parts/pagination.tpl'}
            {/if}
        </div>
    </div>
{/block}
