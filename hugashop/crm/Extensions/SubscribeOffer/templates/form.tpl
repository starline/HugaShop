{block name=subscribe_offer}
    <div id="subscribe_offer">
        <div class="mb-4">
            <h1>Подпишитесь на новости</h1>
            <p>Введите email и получите купон на скидку</p>
        </div>

        {if $error=='email_exists'}
            <div class="alert alert-danger">Такой email уже зарегистрирован</div>
        {/if}

        <form method="post" action="{'ExtSubscribeOfferForm'|link}" class="needs-validation">
            {getCSRFInput}

            {if $id}
                <input type="hidden" name="id" value="{$id}" />
            {/if}

            <div class="mb-3">
                <label class="form-label" for="sbo_email">Email</label>
                <input class="form-control {if $error=='email_exists'}is-invalid{/if}" type="email" name="email"
                    id="sbo_email" value="{$email}" required>
                <div class="invalid-feedback">Введите email</div>
            </div>

            <div class="text-end">
                <button class="btn btn-primary" type="submit">Получить купон</button>
            </div>
        </form>
    </div>
{/block}

{block name=request_sent}
    <div id="subscribe_offer_sent">
        <div class="alert alert-success">Спасибо, ваш купон: <strong>{$coupon}</strong></div>
    </div>
{/block}