<?xml version="1.0"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>{$settings->company_name}</title>
        <description>{$settings->company_description}</description>
        <link>{$config->root_url}</link>
        {foreach $product_variants as $pv}
            <item>
                <g:id>{$pv->id}</g:id>
                <g:title>{$pv->name}</g:title>
                <g:description>{$pv->description}</g:description>
                <g:link>{$pv->link}</g:link>
                <g:price>{$pv->price}</g:price>
                {if !$pv->sale_price|empty}
                    <g:sale_price>{$pv->sale_price}</g:sale_price>
                {/if}
                <g:condition>{$pv->condition}</g:condition>
                <g:image_link>{$pv->image_link}</g:image_link>
                {if !$pv->additional_image_link|empty}
                    {foreach $pv->additional_image_link as $image_link}
                        <g:additional_image_link>{$image_link}</g:additional_image_link>
                    {/foreach}
                {/if}
                <g:product_type>{$pv->product_type}</g:product_type>
                <g:brand>{$pv->brand_name}</g:brand>
                <g:availability>{$pv->availability}</g:availability>
                {if !$pv->label_0|empty}
                    <g:custom_label_0>{$pv->label_0}</g:custom_label_0>
                {/if}
                {if !$pv->label_1|empty}
                    <g:custom_label_1>{$pv->label_1}</g:custom_label_1>
                {/if}
            </item>
        {/foreach}
    </channel>
</rss>