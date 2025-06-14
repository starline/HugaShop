<!-- Google DataLayer -->
<script type="module">
    window.dataLayer = window.dataLayer || [];

    {* Main Page *}
    {if $route == 'Main'}
        dataLayer.push({
            'event': 'DynamicRemarketing',
            'dynamicParams': {
                'ecomm_pagetype': 'home',
                'ecomm_prodid': '',
                'ecomm_totalvalue': ''
            }
        });
    {/if}


    {* Product *}
    {if $route == 'Product'}
        window.dataLayer.push({
            'event': 'DynamicRemarketing',
            'dynamicParams': {
                'ecomm_pagetype': 'product',
                'ecomm_prodid': '{$product->variant->sku}',
                'ecomm_totalvalue': '{$product->variant->price}',
                'ecomm_category': '{$category->name}'
            }
        });

        window.dataLayer.push({
            'event': 'view_item',
            'ecommerce': {
                'currencyCode': '{$GoogleDataLayer->currency_code}',  
                'detail': {
                    'actionField': {
                        'list': 'Product Page'
                    },
                    'products': [{
                        'name': '{$product->name}{if $product->variant->name} - {$product->variant->name}{/if}',
                        'id': '{$product->variant->sku}',
                        'price': '{$product->variant->price}',
                        'category': '{$category->name}'
                        {if $product->variant->name}
                            ,'variant': '{$product->variant->name}'
                        {/if}
                    }]
                }
            }
        });
    {/if}


    {* Products *}
    {if $route == 'Products'}
        {if !$variants_sku|empty}
            window.dataLayer.push({
                'event': 'DynamicRemarketing',
                'dynamicParams': {
                    'ecomm_prodid': ['{$variants_sku|join:"','"}'],
                    'ecomm_pagetype': 'category',
                    'ecomm_category': '{$category->name}'
                }
            });
        {/if}

        window.dataLayer.push({
            'event': 'view_item_list',
            'ecommerce': {
                'currencyCode': '{$GoogleDataLayer->currency_code}',  
                'impressions': [
                    {foreach $products as $p}
                        {
                            'id': '{$p->variant->sku}',
                            'name': '{$p->name}',
                            {if $p->variant->name}
                                'variant': '{$p->variant->name}',
                            {/if}
                            'price': '{$p->variant->price}',
                            'position': {$p@index},
                            'category': '{$category->name}',
                            'list': '{$category->path[0]->name}'
                        }
                        {if !$p@last},{/if}
                    {/foreach}
                ]
            }
        });
    {/if}


    {* Cart *}
    {if $route == 'Cart' and !$purchases|empty}
        window.dataLayer.push({
            'event': 'addToCart',
            'ecommerce': {
                'currencyCode': '{$GoogleDataLayer->currency_code}',
                'add': {
                    'products': [
                        {foreach $purchases as $purch}
                            {
                                'id': '{$purch->variant->sku}',
                                'name': '{$purch->product->name}',
                                'variant': '{$purch->variant->name}',
                                'price': '{$purch->variant->price}',
                                'quantity': {$purch->amount},
                                'category': '{$purch->category->name}',
                                'list': '{$purch->category->path[0]->name}',
                                'position': {$purch@index}
                            }
                            {if !$purch@last},{/if}
                        {/foreach}
                    ]
                }
            }
        });
    {/if}


    {* Order *}
    {if $route == 'Order' and $message_success == 'added'}
        window.dataLayer.push({
            'event': 'conversion',
            'value': {$order->subtotal_price},
            'transaction_id': {$order->id},
            'currency': '{$GoogleDataLayer->currency_code}'
        });

        window.dataLayer.push({
            'event': 'DynamicRemarketing',
            'dynamicParams': {
                'ecomm_prodid': ['{$order->variants_sku|join:"','"}'],
                'ecomm_pagetype': 'purchase',
                'ecomm_totalvalue': {$order->subtotal_price}
            }
        });

        window.dataLayer.push({
            'event': 'purchase',
            'ecommerce': {
                'currencyCode': '{$GoogleDataLayer->currency_code}',
                'purchase': {
                    'actionField': {
                        'id': {$order->id},
                        'revenue': {$order->subtotal_price},
                        'affiliation': '{$settings->domain}'
                    },
                    'products': [
                        {foreach $purchases as $p}
                            {
                                'id': '{$p->variant->sku}',
                                'name': '{$p->product->name}',
                                'variant': '{$p->variant->name}',
                                'price': {$p->price},
                                'quantity': {$p->amount},
                                'category': '{$p->category->name}',
                                'position': {$p@index}
                            }
                            {if !$p@last},{/if}
                        {/foreach}
                    ]
                },
            }
        });
    {/if}
</script>