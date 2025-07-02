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
                <h1>{$category->name}<span
                        class="sum_total">{$products_count}{$products_count|plural:'товар':'товаров':'товара'}</span></h1>
            {else}
                <h1>Все товары <span class="sum_total">{$products_count} 
                        {$products_count|plural:'товар':'товаров':'товара'}</span></h1>
            {/if}
            
            <form method="post" class="d-inline-block ms-2">
                {getCSRFInput}
                <button class="btn btn-primary" type="submit" name="calculate" value="1">Посчитать</button>
            </form>

            <form method="get" id="search">
                {getCSRFInput}
                <div class="input-group">
                    <input class="search form-control" type="text" name="keyword" value="{$keyword}"
                        placeholder="Название, артикул" />
                    <input class="input-group-text search_button" type="submit" value="" />
                </div>
            </form>
        </div>

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
                                <img
                                    src="{if $product->image->filename}{$product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}" />
                            </div>

                            <div class="col row">
                                <div class="col">
                                    <a
                                        href="{'ProductAdmin'|urll:[id=>$product->id]}?return={$smarty.server.REQUEST_URI}">{$product->name}</a>
                                    {if $product->variant_name}
                                        <span class="small"> - {$product->variant_name}</span>
                                    {/if}
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

                                        <div class="col-6">
                                            {foreach $product->fillings as $lang}
                                                <div class="mb-2 text-end">
                                                    <span
                                                        class="badge {if $lang->percent<20}text-bg-danger{elseif $lang->percent<80}text-bg-warning{else}text-bg-success{/if} ">{$lang->percent}%
                                                        {$lang->language_code}</span>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
                {include file='parts/pagination.tpl'}
            {/if}
        </div>
    </div>
{/block}