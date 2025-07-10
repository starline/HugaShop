{block name=browsed_products}
    {get_browsed_products var=browsed_products limit=8}

    {if $browsed_products}
        <div class="my-5">
            <div class="h2">{'Вы просматривали'|trans}</div>
            <ul class="products owl-carousel">
                {foreach $browsed_products as $product}
                    {include 'parts/product_item.tpl' type='short'}
                {/foreach}
            </ul>
        </div>
    {/if}
{/block}
