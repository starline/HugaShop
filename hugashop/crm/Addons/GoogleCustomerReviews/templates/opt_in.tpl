{if $route == 'Order' and $message_success == 'added' and $order->email}
    {if $GoogleCustomerReviewsData.order_id and $GoogleCustomerReviewsData.email}
        <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
        <script>
            window.renderOptIn = function() {
                window.gapi.load('surveyoptin', function() {
                    window.gapi.surveyoptin.render(
                        {$GoogleCustomerReviewsData|json_encode|raw}
                    );
                });
            };
        </script>
    {/if}
{/if}