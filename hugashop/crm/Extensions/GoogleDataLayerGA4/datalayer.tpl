<!-- Google DataLayer -->
<script type="module">
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    {* Main Page *}
    {if $route == 'Main'}
        try {
            dataLayer.push({
                'event': 'view_item_list',
                'ecommerce': {
                    'currency': '{$GoogleDataLayerGA4->currency_code}',
                    'item_list_id': 'home',
                    'item_list_name': 'Home Page',
                    'items': []
                }
            });
        } catch (err) {
            console.log(err);
        }
    {/if}


    {* Product *}
    {if $route == 'Product' and !$product|empty}
        try {
            dataLayer.push({
                'event': 'view_item',
                'ecommerce': {
                    'currency': '{$GoogleDataLayerGA4->currency_code}',  
                    'value': {$product->price},
                    'items': [{
                        'item_id': '{$product->sku}',
                        'item_name': '{$product->name}',
                        {if !$product->variant_name|empty}
                            'variant': '{$product->variant_name}',
                        {/if}
                        'price': {$product->price},
                        'quantity': 1,
                        {if !$product->brand->name|empty}
                            'item_brand': '{$product->brand->name}',
                        {/if}
                        {foreach $category->path as $path}
                            {if $path@index == 0}
                                {$cat_index = ''}
                            {else}
                                {$cat_index = $path@index + 1}
                            {/if}
                            'item_category{$cat_index}': '{$path->name}',
                        {/foreach}
                        'index': 0,
                        'google_business_vertical': 'retail'
                    }]
                }
            });
        } catch (err) {
            console.log(err);
        }
    {/if}


    {* Products *}
    {if $route == 'Products'}
        try {
            dataLayer.push({
                'event': 'view_item_list',
                'ecommerce': {
                    'currency': '{$GoogleDataLayerGA4->currency_code}',
                    'item_list_id': '{$category->url}',
                    'item_list_name': '{$category->name}',
                    'items': [
                        {foreach $products as $p}
                            {
                                'item_id': '{$p->sku}',
                                'item_name': '{$p->name}',
                                {if $p->variant_name}
                                    'item_variant': '{$p->variant_name}',
                                {/if}
                                'price': {$p->price},
                                {if !$p->brand->name|empty}
                                    'item_brand': '{$p->brand->name}',
                                {/if}
                                'index': {$p@index},
                                'google_business_vertical': 'retail'
                            }
                            {if !$p@last},{/if}
                        {/foreach}
                    ]
                }
            });
        } catch (err) {
            console.log(err);
        }
    {/if}

    {* Cart Add *}
    {if !$route|in_array:['Checkout', 'Order']}

        // $(document).trigger('addToCardEvent', item);
        $(document).on('addToCardEvent', function(e, item) {
            if (!item.price || !item.amount || !item.sku) {
                return;
            }

            try {

                let cookie_item = {};
                const cookie_json = getCookie("{$GoogleDataLayerGA4->cookie_key}");
                if (cookie_json) {
                    cookie_item = JSON.parse(cookie_json);
                }

                dataLayer.push({
                    'event': 'add_to_cart',
                    'ecommerce': {
                        'currency': '{$GoogleDataLayerGA4->currency_code}',
                        'value': item.price * item.amount,
                        'item_list_id': item.list_id || cookie_item.list_id || null,
                        'item_list_name': item.list_name || cookie_item.list_name || null,
                        'items': [{
                            'item_id': item.sku,
                            'item_name': item.name,
                            'item_variant': item.variant_name,
                            'price': item.price,
                            'quantity': item.amount,
                            'index': 0,
                            'google_business_vertical': 'retail'
                        }]
                    }
                });
            } catch (err) {
                console.log(err);
            }
        });
    {/if}


    {* Select item *}
    {if !$route|in_array:['Checkout', 'Order']}

        // $(document).trigger('selectItemEvent', item);
        $(document).on('selectItemEvent', function(e, item) {
            if (!item.sku || !item.list_id || !item.list_name) {
                return;
            }

            // Save list_id in $COOKIE
            document.cookie = "{$GoogleDataLayerGA4->cookie_key}=" + JSON.stringify(item);

            try {
                dataLayer.push({
                    'event': 'select_item',
                    'ecommerce': {
                        'item_list_id': item.list_id,
                        'item_list_name': item.list_name,
                        'items': [{
                            'item_id': item.sku,
                            'item_name': item.name,
                            'google_business_vertical': 'retail'
                        }]
                    }
                });
            } catch (err) {
                console.log(err);
            }
        });
    {/if}


    {* Cart *}
    {if $route == 'Cart' and !$purchases|empty}
        try {
            dataLayer.push({
                'event': 'view_cart',
                'ecommerce': {
                    'currency': '{$GoogleDataLayerGA4->currency_code}',
                    'value': {$cart->purchases_price},
                    'items': [
                        {foreach $purchases as $purch}
                            {
                                'item_id': '{$purch->product->sku}',
                                'item_name': '{$purch->product->name}',
                                {if !$purch->product->variant_name|empty}
                                    'item_variant': '{$purch->product->variant_name}',
                                {/if}
                                'price': {$purch->product->price},
                                'quantity': {$purch->amount},
                                {foreach $purch->product->category->path as $path}
                                    {if $path@index == 0}
                                        {$cat_index = ''}
                                    {else}
                                        {$cat_index = $path@index + 1}
                                    {/if}
                                    'item_category{$cat_index}': '{$path->name}',
                                {/foreach}
                                'index': {$purch@index},
                                'google_business_vertical': 'retail'
                            }
                            {if !$purch@last},{/if}
                        {/foreach}
                    ]
                }
            });
        } catch (err) {
            console.log(err);
        }
    {/if}


    {* Begin Checkout *}
    {if $route == 'Checkout' and !$purchases|empty}

        let cookie_item = {};
        const cookie_json = getCookie("{$GoogleDataLayerGA4->cookie_key}");
        if (cookie_json) {
            cookie_item = JSON.parse(cookie_json);
        }

        try {
            dataLayer.push({
                'event': 'begin_checkout',
                'ecommerce': {
                    'currency': '{$GoogleDataLayerGA4->currency_code}',
                    'value': {$cart->purchases_price},
                    {if !$cart->coupon_code|empty}
                        'coupon': '{$cart->coupon_code}',
                    {/if}
                    'item_list_id': cookie_item.list_id || null,
                    'item_list_name': cookie_item.list_name || null,
                    'items': [
                        {foreach $purchases as $purch}
                            {
                                'item_id': '{$purch->product->sku}',
                                'item_name': '{$purch->product->name}',
                                {if !$purch->product->variant_name|empty}
                                    'item_variant': '{$purch->product->variant_name}',
                                {/if}
                                'price': {$purch->product->price},
                                'quantity': {$purch->amount},
                                {foreach $purch->product->category->path as $path}
                                    {if $path@index == 0}
                                        {$cat_index = ''}
                                    {else}
                                        {$cat_index = $path@index + 1}
                                    {/if}
                                    'item_category{$cat_index}': '{$path->name}',
                                {/foreach}
                                'index': {$purch@index},
                                'google_business_vertical': 'retail'
                            }
                            {if !$purch@last},{/if}
                        {/foreach}
                    ]
                }
            });
        } catch (err) {
            console.log(err);
        }
    {/if}


    {* Order *}
    {if $route == 'Order' and $message_success == 'added'}

        let cookie_item = {};
        const cookie_json = getCookie("{$GoogleDataLayerGA4->cookie_key}");
        if (cookie_json) {
            cookie_item = JSON.parse(cookie_json);
            deleteCookie("{$GoogleDataLayerGA4->cookie_key}");
        }

        try {
            dataLayer.push({
                'event': 'purchase',
                'ecommerce': {
                    'currency': '{$GoogleDataLayerGA4->currency_code}',
                    'value': {$order->subtotal_price},
                    'transaction_id': {$order->id},
                    'item_list_id': cookie_item.list_id || null,
                    'item_list_name': cookie_item.list_name || null,
                    'items': [
                        {foreach $purchases as $purch}
                            {
                                'item_id': '{$purch->sku}',
                                'item_name': '{$purch->product_name}',
                                {if !$purch->variant_name|empty}
                                    'item_variant': '{$purch->variant_name}',
                                {/if}
                                'price': {$purch->price},
                                'quantity': {$purch->amount},
                                {foreach $purch->category->path as $path}
                                    {if $path@index == 0}
                                        {$cat_index = ''}
                                    {else}
                                        {$cat_index = $path@index + 1}
                                    {/if}
                                    'item_category{$cat_index}': '{$path->name}',
                                {/foreach}
                                'index': {$purch@index},
                                'google_business_vertical': 'retail'
                            }
                            {if !$purch@last},{/if}
                        {/foreach}
                    ]
                }
            });
        } catch (err) {
            console.log(err);
        }
    {/if}


    {literal}
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }

        function deleteCookie(name) {
            document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
    {/literal}
</script>