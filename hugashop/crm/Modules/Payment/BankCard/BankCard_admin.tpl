{if $payment_method->settings->sms_text}
    <span class="icons {if $order->settings->payment_sms}sms_button{/if}">
        <a class="send_sms send_payment_sms" data-bs-toggle="tooltip" title="Отправить смс c информацией об оплате"
            href="#">Отправить смс с
            номером карты</a>
    </span>
    <div class="sms_template">
        SMS: {$payment_method->settings->sms_text}
    </div>
{/if}

<h4 class="mt-4">Реквизиты оплаты</h4>

<div><span class='col-form-label'>Название Банка: </span>{$payment_method->settings->bank_name}</div>
<div><span class='col-form-label'>Номер карты: </span>{$payment_method->settings->card_number}</div>
<div><span class='col-form-label'>Владелец карты: </span>{$payment_method->settings->card_owner}</div>

{block name=body_script append}
    <script type="module">
        {literal}

            $(function() {

                // Отправить SMS c информацией об оплате
                $("a.send_payment_sms").on('click', function() {
                    let icon = $(this);
                    let line = icon.closest(".icons");
                    let id = $('input[name="id"]').val();
                    let state = line.hasClass('sms_button') ? 1 : 0;

                    icon.addClass('loading_icon');

                    $.ajax({
                        type: 'POST',
                        url: '/admin/ajax/sms',
                        data: {'id': id, 'type': 'payment', 'csrf': csrf},
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
{/block}