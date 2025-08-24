{if $banners}
<div id='carouselPromo'>
    <div class='owl-carousel'>
        {foreach $banners as $banner}
            {if $banner->image->filename}
                <div class='item'>
                    {if $banner->link}<a href='{$banner->link}'>{/if}
                        <img src='{$banner->image->filename|resize:1920:600}' alt='{$banner->name}' class='img-fluid w-100' />
                    {if $banner->link}</a>{/if}
                </div>
            {/if}
        {/foreach}
    </div>
</div>

<script type='module'>
    import '{"js/owlcarousel/owl.carousel.min.js"|asset}';
    import { owlCarouselInit } from '{"js/common.js"|asset}';

    document.addEventListener('DOMContentLoaded', function () {
        owlCarouselInit(document.getElementById('carouselPromo'));
    });
</script>
{/if}

