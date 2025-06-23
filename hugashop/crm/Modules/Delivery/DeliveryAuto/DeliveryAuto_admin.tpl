<div class="delivery_note">
    <div class="row gx-5">
        <label class="col-form-label col-lg-3" for="delivery_note">Номер накладной</label>
        <div class="col-lg-3">
            <input id="delivery_note" class="form-control" type="text" name="delivery_note"
                value="{$order->delivery_note}" {if !$can_edit}disabled{/if} />
        </div>
        <div class="col-lg-6 align-content-center">
            {if $order->delivery_note}
                <span class="icons {if $order->settings->delivery_sms}sms_button{/if}">
                    <a class="send_sms send-delivery-sms" data-bs-toggle="tooltip" title="Отправить смс c накладной"
                        href="#">Отправить смс c ТТН</a>
                </span>
            {/if}
        </div>
    </div>

    {if $order->delivery_note}
        <div class='tracking_status'>
            <input name='delivery[module]' value='{$delivery->module}' type='hidden'>

            <div class="tracking_info">
                <a target='_blank'
                    href='https://www.delivery-auto.com/uk-UA/Receipts/DetailsNew?id={$order->delivery_note}'>delivery-auto.com
                    →</a>
            </div>
        </div>
    {/if}
</div>

<script type="module">
    {literal}

        $(function() {

            // Отправить SMS c накладной
            $("a.send-delivery-sms").click(function() {
                let icon = $(this);
                let line = icon.closest(".icons");
                let id = $('input[name="id"]').val();
                let state = line.hasClass('sms_button') ? 1 : 0;

                icon.addClass('loading_icon');

                $.ajax({
                    type: 'POST',
                    url: '/admin/ajax/sms',
                    data: {'id': id, 'type': 'delivery', 'csrf': csrf},
                    success: function(data) {
                        icon.removeClass('loading_icon');
                        if (!state)
                            line.addClass('sms_button');
                    },
                    dataType: 'json'
                });

                return false;
            });

        });

    {/literal}
</script>