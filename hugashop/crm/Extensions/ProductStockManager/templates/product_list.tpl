{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='Товары'}

{block name=content}
    <div class="two_columns_list">
        <div class="header_top">
            <h1>Все товары <span class="sum_total">{$products_count} {$products_count|plural:'товар':'товаров':'товара'}</span></h1>

            <!-- Search -->
            <form method="get" id="search">
                <div class="input-group">
                    <input class="search form-control" type="text" name="keyword" value="{$keyword}"
                        placeholder="Название, артикул" />
                    <input class="input-group-text search_button" type="submit" value="" />
                </div>
            </form>
        </div>

        <div id="main_list">
            {if $products}
                {include file='parts/pagination.tpl'}
                <div class="list">
                    {foreach $products as $product}
                        <div class="list_row" item_id="{$product->id}">
                            <div class="image">
                                <img src="{if $product->image->filename}{$product->image->filename|resize:60:60:c}{else}{'images/cargo.png'|asset}{/if}" />
                            </div>

                            <div class="col row">
                                <div class="col">
                                    <a href="{'ProductAdmin'|link:[id=>$product->id]}?return={$smarty.server.REQUEST_URI}">{$product->name}</a>
                                    {if $product->variant_name}<span class="small"> - {$product->variant_name}</span>{/if}
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="row">
                                        <div class="col-6 text-end">
                                            {if $product->sku}
                                                <div class="badge text-bg-round copy_field" value="{$product->sku}">{$product->sku}
                                                    <div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
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
