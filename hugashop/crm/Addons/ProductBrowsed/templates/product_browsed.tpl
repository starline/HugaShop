<div id="product_browsed">

    <div class="spinner_box" hx-get="{'AddonProductBrowsedAjax'|linkLang}" hx-trigger="load" hx-swap="outerHTML">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">{'Загрузка'|trans}...</span>
        </div>
    </div>

    {block name=product_browsed}
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
</div>

<script type="module">
    import '{"js/owlcarousel/owl.carousel.min.js"|asset}';
    import { owlCarouselInit } from '{"js/common.js"|asset}';

    document.body.addEventListener('htmx:afterOnLoad', function(event) {
        if (event.target && event.target.id === 'product_browsed') {
            owlCarouselInit(event.target);
        }
    });
</script>