{if $banners}
    <div id='carouselPromo'>
        <div class='tiny-slider'>
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

        <button class="carousel-control-prev" type="button">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <script type='module'>
        import '{"js/tiny-slider/tiny-slider.js"|asset}';
        import '{"js/common.js"|asset}';

        $(function() {
            tns({
                container: '#carouselPromo .tiny-slider',
                items: 2,
                gutter: 0,
                loop: true,
                nav: false,
                controls: true,
                prevButton: document.querySelector('#carouselPromo .carousel-control-prev'),
                nextButton: document.querySelector('#carouselPromo .carousel-control-next'),
                responsive: {
                    760: { items: 3 },
                    1000: { items: 4 }
                }
            });
        });
    </script>
{/if}