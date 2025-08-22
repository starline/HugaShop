<?xml version="1.0"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>{$settings->company_name}</title>
        <description>{$settings->company_description}</description>
        <link>{$config->root_url}</link>
        {foreach $products as $product}
            <item>
                <g:id>{$product->id}</g:id>
                <g:title>{$product->name}</g:title>
                <g:description>{$product->description}</g:description>
                <g:link>{$product->link}</g:link>
                <g:price>{$product->price}</g:price>
                {if !$product->sale_price|empty}
                    <g:sale_price>{$product->sale_price}</g:sale_price>
                {/if}
                <g:condition>{$product->condition}</g:condition>
                <g:image_link>{$product->image_link}</g:image_link>
                {if !$product->additional_image_link|empty}
                    {foreach $product->additional_image_link as $image_link}
                        <g:additional_image_link>{$image_link}</g:additional_image_link>
                    {/foreach}
                {/if}
                <g:product_type>{$product->product_type}</g:product_type>
                <g:brand>{$product->brand_name}</g:brand>
                <g:availability>{$product->availability}</g:availability>
                {if !$product->label_0|empty}
                    <g:custom_label_0>{$product->label_0}</g:custom_label_0>
                {/if}
                {if !$product->label_1|empty}
                    <g:custom_label_1>{$product->label_1}</g:custom_label_1>
                {/if}
            </item>
        {/foreach}
    </channel>
</rss>