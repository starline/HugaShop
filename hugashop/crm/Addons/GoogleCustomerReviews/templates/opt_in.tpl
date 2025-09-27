{if $route == 'Order' and $message_success == 'added' and $order->email}
    {assign var=payload value=$GoogleCustomerReviewsPayload}
    {if $payload.order_id and $payload.email}
        <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
        <script>
            window.renderOptIn = function() {
                window.gapi.load('surveyoptin', function() {
                    window.gapi.surveyoptin.render(
                        {$payload|json_encode|raw}
                    );
                });
            };
        </script>
    {/if}
{/if}
