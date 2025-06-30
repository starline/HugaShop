{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}
{include 'product/parts/submenu_part.tpl'}

{if $product->id}
        {$meta_title = $product->name}
{/if}

{block name=content}
        <div class="row mt-5">
                <div class="col-12 layer">
                        <div class="header_top mt-3">
                                <h1>
                                        {if $orders_count > 0}{$orders_count}{else}Нет{/if} заказ{$orders_count|plural:'':'ов':'а'}
                                        {if 'finance'|user_access AND $orders_paid_price->sum_total_price}
                                                <span class="sum_total">оплаченных на сумму: {$orders_paid_price->sum_total_price|price_html|raw}
                                                        <span class="sum_profit_price">{$orders_paid_price->sum_profit_price|price_html:profit|raw}</span>
                                                </span>
                                        {/if}
                                </h1>
                                <form class="export_btn" method="post" action="/admin/product_orders/export?product_id={$product->id}" target="_blank">
                                        <input type="image" src="{'images/export_excel.png'|asset}" name="export" data-bs-toggle="tooltip" title="Экспортировать заказы с товаром" />
                                </form>
                        </div>
                </div>

                <div class="col-12">
                        {include file='parts/pagination.tpl'}
                        <div class="list">
                                {foreach $orders as $order}
                                        {include file='order/parts/order_item_part.tpl'}
                                {/foreach}
                        </div>
                        {include file='parts/pagination.tpl'}
                </div>
        </div>
{/block}

{block name=body_script append}
        <script type="module">
                {literal}
                        $(function() {
                                $(".notice_block").each(function() {
                                        let height = $(this).height();
                                        let minimize_height = 60;
                                        if (height > minimize_height && (height - minimize_height) > 40) {
                                                $(this).addClass("minimizeble minimize");
                                        }
                                });
                                $(".show_link_block a").click(function() {
                                        if ($(this).closest("div.notice_block").hasClass("minimize")) {
                                                $(this).closest("div.notice_block").removeClass("minimize");
                                                $(this).text("скрыть ↑");
                                        } else {
                                                $(this).closest("div.notice_block").addClass("minimize");
                                                $(this).text("раскрыть ↓");
                                        }
                                        return false;
                                });
                        });
                {/literal}
        </script>
{/block}
