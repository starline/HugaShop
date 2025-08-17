{extends 'wrapper/main.tpl'}

{$meta_title = "Корзина"}

{block name=content}
    <div class="cart_wrap">

        <h1>
            {if $purchases}
                В корзине {$cart->purchases_count} {$cart->purchases_count|plural:'товар':'товаров':'товара'}
            {else}
                Корзина пуста
            {/if}
        </h1>

        {if $purchases}
            <form id="cart_form" method="post" name="cart" action="/cart">
                {getCSRFInput}

                <div id="purchases" class="cart_purchases">

                    {foreach $purchases as $purchase}
                        <div class="purchase_row">
                            <div class="remove">
                                <a href="{'CartRemove'|linkLang:[product_id => $purchase->product->id]}" class="ajax">
                                    <img loading="lazy" src="{'/images/delete.png'|asset}" data-bs-toggle="tooltip"
                                        title="Удалить из корзины" alt="Удалить из корзины">
                                </a>
                            </div>
                            <div class="image">
                                <a href="{'Product'|linkLang:[url => $purchase->product->url]}">
                                    <img loading="lazy" src="{$purchase->product->image->filename|resize:120:120:c}"
                                        alt="{$purchase->product->name}">
                                </a>
                            </div>
                            <div class="name">
                                <a href="{'Product'|linkLang:[url => $purchase->product->url]}">{$purchase->product->name}</a>
                                {if $purchase->variant->name} - {$purchase->variant->name}{/if}
                            </div>
                            <div class="amount">
                                <select class="form-select" name="amounts[{$purchase->variant->id}]">
                                    {$loop = ($purchase->variant->custom || $purchase->variant->stock == null) ? $settings->max_order_amount : $purchase->variant->stock + 1}
                                    {section name=amounts start=1 loop=$loop step=1}
                                        <option value="{$smarty.section.amounts.index}"
                                            {if $purchase->amount == $smarty.section.amounts.index}selected{/if}>
                                            {$smarty.section.amounts.index} {$settings->units}</option>
                                    {/section}
                                </select>
                            </div>
                            <div class="price purchase_total_price">
                                {($purchase->variant->price * $purchase->amount)|price_html|raw}
                            </div>
                        </div>
                    {/foreach}
                </div>

                <div class="bottom_cart_row">
                    <div class="cart_total">
                        <div class="total">Итого: {$cart->purchases_price|price_html|raw}</div>
                        <div>
                            {if $is_ajax}
                                <div class="btn btn-light fancy_close mmx-3">Продолжить покупки</div>
                            {/if}
                            <a class="btn btn-primary" href="{'Checkout'|linkLang}">Оформить заказ</a>
                        </div>
                    </div>
                </div>
            </form>

        {else}
            <p>Выберите товары в каталоге. Когда корзина будет сформирована, можно будет оформить заказ. Приятных покупок!</p>
        {/if}

    </div>


    <script>
        $(function() {
            $('select[name*=amounts]').change(function() {
                $('form[name=cart]').submit();
            });

            $('.fancy_close').click(function() {
                $.fancybox.close();
            });
        });
    </script>
{/block}