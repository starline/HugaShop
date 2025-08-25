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

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselPromo" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselPromo" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <script type='module'>
        import '{"js/owlcarousel/owl.carousel.min.js"|asset}';
        import { owlCarouselInit } from '{"js/common.js"|asset}';

        $(function() {
            owlCarouselInit($('#carouselPromo'));
        });
    </script>
{/if}