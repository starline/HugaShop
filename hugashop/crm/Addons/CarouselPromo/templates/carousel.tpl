{if $banners}
    <div id='carouselPromo' class="tiny-slider-wrapper">
        <div class='tiny-slider'>
            {foreach $banners as $banner}
                {if $banner->image->filename}
                    <div class='item'>
                        {if $banner->link}<a href="{$banner->link}">{/if}
                            <img src="{$banner->image->filename|resize:1300:305:c}" alt="{$banner->name}" width="1300" height="305"
                                class="img-fluid w-100" data-src="{$banner->image->filename|resize:1300:305:c}" />
                            {if $banner->link}</a>{/if}
                    </div>
                {/if}
            {/foreach}
        </div>

        <div class="owl-nav">
            <button type="button" role="presentation" class="owl-prev">
                <span aria-label="Previous">‹</span>
            </button>
            <button type="button" role="presentation" class="owl-next">
                <span aria-label="Next">›</span>
            </button>
        </div>
    </div>

    <script src='{"js/tiny-slider/tiny-slider.js"|asset}'></script>
    <link rel="stylesheet" href="{'js/tiny-slider/tiny-slider.css'|asset}" />
    <script type='module'>
        $(function() {
            var slider = tns({
                container: '#carouselPromo .tiny-slider',
                center: true,
                items: 1,
                loop: true,
                prevButton: $('#carouselPromo .owl-prev')[0],
                nextButton: $('#carouselPromo .owl-next')[0],
                autoplay: true,
                autoplayHoverPause: true,
                autoplayButtonOutput: false,
                autoplayTimeout: 2000,
                navPosition: 'bottom',
                lazyload: true,
            });
        });
    </script>
{/if}