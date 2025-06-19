{*<meta property="og:locale" content="ua_UA">*}

<!-- Open Graph -->
{if $route == 'Main'}
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{$settings->company_name} {$settings->company_description}" />
    <meta property="og:url" content="{$config->root_url}" />
    <meta property="og:title" content="{$settings->company_name}" />
    <meta property="og:description" content="{$settings->company_description}" />
    <meta property="og:image" content='{$config->root_url}{"images/favicon.ico"|asset:"{$settings->theme}"}' />
    <meta property="og:image:alt" content="{$settings->company_name}" />
    <meta property="og:image:type" content="image/png" />
{/if}


{if $route == 'Product'}
    <meta property="og:type" content="product" />
    <meta property="og:site_name" content="{$settings->company_name} {$settings->company_description}" />
    <meta property="og:url" content="{$config->root_url}/tovar-{$product->url}" />
    <meta property="og:title" content="{$product->name}" />
    <meta property="og:description" content="{$product->annotation}" />

    <meta property="product:category" content="{$product->category->name}">
    <meta property="product:availability"
        content="{if $product->stock > 0 || $product->stock|is_null}in stock{else}out of stock{/if}">

    {if $product->price|isset}
        {if $product->price < $product->old_price}
            <meta property="product:sale_price:amount" content="{$product->price}" />
            <meta property="product:sale_price:currency" content="{$currency->code}" />
            <meta property="product:sale_price_dates:start" content="{'now'|date:'Y-m-d'}T0:00{$timezone_offset}:00" />
            <meta property="product:sale_price_dates:end" content="{'+2 days'|date:'Y-m-d'}T0:00{$timezone_offset}:00" />

            <meta property="product:price:amount" content="{$product->old_price}" />
            <meta property="product:price:currency" content="{$currency->code}" />
        {else}
            <meta property="product:price:amount" content="{$product->price}" />
            <meta property="product:price:currency" content="{$currency->code}" />
        {/if}
    {/if}

    {if $product->pretax_price|isset}
        <meta property="product:pretax_price:amount" content="{$product->pretax_price}" />
        <meta property="product:pretax_price:currency" content="{$currency->code}" />
    {/if}

    {if $product->image->filename|isset}
        <meta property="og:image" content="{$product->image->filename|resize:720:720:w}" />
        <meta property="og:image:alt" content="{$product->name}" />
    {/if}
{/if}


{if $route == 'Products'}
    <meta property="og:type" content="product.group">
    <meta property="og:site_name" content="{$settings->company_name} {$settings->company_description}" />
    <meta property="og:title" content="{$category->name}">
    <meta property="og:url" content="{$config->root_url}/{$category->url}">
    <meta property="og:description" content="{$category->meta_description}">

    {if !$category->image->filename|empty}
        <meta property="og:image" content="{$category->image->filename|resize:720:720}" />
    {/if}
{/if}


{if $route == 'Page'}
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="{$settings->company_name} {$settings->company_description}" />
    <meta property="og:url" content="{$config->root_url}{$canonical}" />
    <meta property="og:title" content="{$page->name}" />
    <meta property="og:description" content="{$page->meta_description}" />
    <meta property="og:image" content='{$config->root_url}{"images/favicon.ico"|asset:"{$settings->theme}"}' />
    <meta property="og:image:alt" content="{$settings->company_name}" />
    <meta property="og:image:type" content="image/png" />
{/if}


{if $route == 'Post'}
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="{$settings->company_name} {$settings->company_description}" />
    <meta property="og:url" content="{$config->root_url}{$canonical}" />
    <meta property="og:title" content="{$post->name}" />
    <meta property="og:description" content="{$post->meta_description}" />

    {if !$post->image->filename|empty}
        <meta property="og:image" content="{$post->image->filename|resize:720:720:w}" />
    {/if}
{/if}