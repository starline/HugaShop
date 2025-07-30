<script type="module">
$(function(){
    $('body').on('click', '.you-price', function(e){
        e.preventDefault();
        const productId = $(this).data('product-id');
        $.get('{'ExtPriceRequestForm'|link}', {product_id: productId}, function(html){
            $.fancybox.open(html);
        });
    });

    $(document).on('submit', '#price_request_form', function(e){
        e.preventDefault();
        const form = $(this);
        $.post('{'ExtPriceRequest'|link}', form.serialize(), function(html){
            $.fancybox.open(html);
        });
    });
});
</script>
