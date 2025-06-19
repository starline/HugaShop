<!-- Meta Pixel Code -->
{if !$FacebookPixel->domain_verification|empty}
    <meta name="facebook-domain-verification" content="{$FacebookPixel->domain_verification}" />
{/if}

{if !$FacebookPixel->pixel_id|empty}
    <script type="module">
        {literal}
            ! function(f, b, e, v, n, t, s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n, arguments): n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
        {/literal}

        fbq('init', '{$FacebookPixel->pixel_id}');
        fbq('track', 'PageView');


        {* Product *}
        {if $route == 'Product'}
            fbq('track', 'ViewContent', {
                value: {$product->price},
                currency: '{$FacebookPixel->currency_code}',
                content_ids: ['{$product->sku}'],
                content_type: 'product',
                content_category: '{$category->name}',
                content_name: '{$product->name}',
            });
        {/if}


        {* Cart Add *}
        {if !$route|in_array:['Checkout', 'Order']}

            // $(document).trigger('addToCardEvent', item);
            $(document).on('addToCardEvent', function(e, item) {
                if (!item.price || !item.amount || !item.sku) {
                    return;
                }
                try {
                    fbq('track', 'AddToCart', {
                        value: item.price * item.amount,
                        currency: '{$FacebookPixel->currency_code}',
                        content_ids: [item.sku],
                        contents: [{
                            'id': item.sku,
                            'quantity': item.amount,
                            'item_price': item.price
                        }],
                        content_type: 'product'
                    });
                } catch (err) {
                    console.log(err);
                }
            });
        {/if}


        {* Checkout *}
        {if $route == 'Checkout'}
            fbq('track', 'InitiateCheckout', {
                value: {$cart->purchases_price},
                num_items: {$cart->purchases_count},
                currency: '{$FacebookPixel->currency_code}',
                content_ids: [
                    {foreach $purchases as $purchase}
                        '{$purchase->sku}'{if !$purchase@last},{/if}
                    {/foreach}
                ],
                content_type: 'product'
            });
        {/if}


        {* Order *}
        {if $route == 'Order' and $message_success == 'added'}
            fbq('track', 'Purchase', {
                value: {$order->payment_price},
                num_items: {$order->purchases_count},
                currency: '{$FacebookPixel->currency_code}',
                content_ids: [
                    {foreach $purchases as $purchase}
                        '{$purchase->sku}'
                        {if !$purchase@last},{/if}
                    {/foreach}
                ],
                contents: [
                    {foreach $purchases as $purchase}
                        {
                            'id':  '{$purchase->sku}',
                            'quantity':  {$purchase->amount},
                            'item_price': {$purchase->price}
                        }
                        {if !$purchase@last},{/if}
                    {/foreach}
                ],
                content_type: 'product'
            });
        {/if}
    </script>

    <noscript>
        {if $route == 'Order' and $message_success == 'added'}
            <img height="1" width="1" alt="" style="display:none"
                src="https://www.facebook.com/tr?id={$FacebookPixel->pixel_id}&ev=Purchase&cd[value]={$order->payment_price}&cd[currency]={$FacebookPixel->currency_code}" />
        {else}
            <img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id={$FacebookPixel->pixel_id}&ev=PageView&noscript=1" />
        {/if}
    </noscript>
{/if}