<script type="module">
    import { asignFancyAjax } from "{'js/common.js'|asset}";

    let price_request_link = "{'ExtPriceRequestForm'|link}";

    $(function() {
        $('body').on('click', '#product-price-request', function(e) {
            e.preventDefault();

            const productId = $(this).data('product-id');

            $.fancybox.open({
                type: 'ajax',
                src: price_request_link,
                ajax: {
                    settings: {
                        method: 'POST',
                        data: {
                            product_id: productId,
                            csrf: window.csrf
                        }
                    }
                },
                touch: false,
                closeExisting: true,
                afterShow: asignFancyAjax
            });
        });
    });
</script>