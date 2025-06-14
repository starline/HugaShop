<div class="list-group">
    {if !$cart->session_start|empty}
        <div class="list-group-item">
            <div class="col-6">Начало сессии</div>
            <div class="text-end">
                <span class="badge text-bg-primary">{$cart->session_start|date}</span>
                <span class="badge text-bg-primary">{$cart->session_start|time}</span>
            </div>
        </div>
    {/if}

    {if !$cart->created|empty}
        <div class="list-group-item">
            <div class="col-6">Товар добавлен в корзину</div>
            <div class="text-end">
                <span class="badge text-bg-secondary">{$cart->created|date}</span>
                <span class="badge text-bg-secondary">{$cart->created|time}</span>
            </div>
        </div>
    {/if}

    {if !$cart->checkout_init|empty}
        <div class="list-group-item">
            <div class="col-6">Начало оформления заказа</div>
            <div class="text-end">
                <span class="badge text-bg-warning">{$cart->checkout_init|date}</span>
                <span class="badge text-bg-warning">{$cart->checkout_init|time}</span>
            </div>
        </div>
    {/if}

    {if !$cart->ordered|empty}
        <div class="list-group-item">
            <div class="col-6">Заказ оформлен</div>
            <div class="text-end">
                <span class="badge text-bg-success">{$cart->ordered|date}</span>
                <span class="badge text-bg-success">{$cart->ordered|time}</span>
            </div>
        </div>
    {/if}

    {if !$cart->ip|empty}
        <div class="list-group-item">
            <div>IP: {$cart->ip}</div>
            <div>
                <a class="badge text-bg-secondary" href='https://www.ipaddress.com/ipv4/{$cart->ip}' target="_blank">где
                    это?</a>
            </div>
        </div>
    {/if}

    {if !$cart->user_agent|empty}
        <div class="list-group-item">
            <div class="col-6">{$cart->user_agent->os} {$cart->user_agent->os_version}</div>
            <span class="badge text-bg-secondary">{$cart->user_agent->device_type}</span>
        </div>
        <div class="list-group-item">
            <div class="col-6">{$cart->user_agent->browser}</div>
            <span class="badge text-bg-secondary">{$cart->user_agent->device}</span>
        </div>
    {/if}

    {if !$cart->language|empty}
        <div class="list-group-item">
            <div class="col-6">Язык браузера</div>
            <div>
                <span class="badge text-bg-secondary">{$cart->language}</span>
            </div>
        </div>
    {/if}

    {if !$cart->referral|empty}
        <div class="list-group-item">
            <div class="col-6">Источник</div>
            <div>
                <span class="badge text-bg-danger">{$cart->referral}</span>
            </div>
        </div>
    {/if}
</div>