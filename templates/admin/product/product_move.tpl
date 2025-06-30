{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}
{include 'product/parts/submenu_part.tpl'}

{if $product->id}
    {$meta_title = $product->name}
{/if}

{block name=content}
    <div class="row">
        <div class="col-12">
            <div class="header_top mt-3">
                <h1>
                    {if $purchases_count}{$purchases_count}{else}Нет{/if} постав{$purchases_count|plural:'ка':'ок':'ки'}
                </h1>
            </div>
        </div>
        <div class="col-12">
            {include file='parts/pagination.tpl'}
            <div class="list">
                {foreach $purchases as $purchase}
                    <div class="list_row" item_id="{$purchase->id}">
                        <div class="order_date">
                            <a class="order_id" href="/admin/warehouse/movement/{$purchase->move_id}">#<span>{$purchase->move_id}</span></a>
                            <div class="date">{$purchase->warehouse_move->date|date}</div>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    {$purchase->warehouse_move->place->name}
                                </div>
                                <div class="col-12 col-md-6 mt-3 mt-md-0">
                                    <div class="order_price">{$purchase->amount} шт</div>
                                    <div class="notice">{$purchase->warehouse_move->note|strip_tags}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            {include file='parts/pagination.tpl'}
        </div>
    </div>
{/block}
