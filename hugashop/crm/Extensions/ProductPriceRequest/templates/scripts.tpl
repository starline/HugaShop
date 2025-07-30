<script type="module">
    let price_request_form_link = "{'ExtPriceRequestForm'|link}";
    let price_request_link = "{'ExtPriceRequest'|link}";

    $(function() {
        $('body').on('click', '.you-price', function(e) {
            e.preventDefault();
            const productId = $(this).data('product-id');
            $.post(price_request_form_link, {
                product_id: productId,
                csrf: window.csrf
            }, function(html) {
                $.fancybox.open(html);
            });
        });

        $(document).on('submit', '#price_request_form', function(e) {
            e.preventDefault();
            const form = $(this);
            $.post(price_request_link, form.serialize(), function(html) {
                $.fancybox.open(html);
            });
        });
    });
</script>