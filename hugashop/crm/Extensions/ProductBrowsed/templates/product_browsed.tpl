<div id="product_browsed">

    <div class="spinner_box" hx-get="{'ExtProductBrowsedAjax'|linkLang}" hx-trigger="load" hx-swap="outerHTML">
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

{block name=jss}
    <script>
        document.body.addEventListener('htmx:afterOnLoad', function(event) {
            if (event.target && event.target.id === 'product_browsed') {
                const $carousel = $(event.target).find('.owl-carousel');
                if ($carousel.length) {
                    $carousel.owlCarousel({
                        loop: true,
                        margin: 0,
                        nav: true,
                        dots: false,
                        responsive: {
                            0: { items: 2 },
                            760: { items: 3 },
                            1000: { items: 4 }
                        }
                    });
                }
            }
        });
    </script>
{/block}