{block name=help_offer}
    <div id="help_offer">
        <div class="mb-4">
            <h1>Нужна помощь с выбором товара?</h1>
            <p>Заполните форму ниже и наши специалисты свяжутся с Вам в ближайшее время</p>
        </div>

        {if $error}
            <div class="alert alert-danger">
                {if $error=='captcha'}Подтвердите что вы не робот
                {elseif $error=='personal_data'}Вы должны согласиться с
                обработкой персональных данных{/if}
            </div>
        {/if}

        <form method="post" action="{'ExtTimerGetHelpOfferForm'|link}" class="needs-validation">
            {getCSRFInput}
            <div class="mb-3">
                <label class="form-label" for="hgo_name">Имя</label>
                <input class="form-control {if 'name'|in_array:$form_invalid}is-invalid{/if}" type="text" name="name"
                    id="hgo_name" value="{$request->name}">
                <div class="invalid-feedback">Введите имя</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="hgo_phone">Телефон</label>
                <input class="form-control {if 'phone'|in_array:$form_invalid}is-invalid{/if}" type="text" name="phone"
                    id="hgo_phone" value="{$request->phone}">
                <div class="invalid-feedback">Введите телефон</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="hgo_email">Email</label>
                <input class="form-control" type="email" name="email" id="hgo_email" value="{$request->email}">
            </div>
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input {if $error=='personal_data'}is-invalid{/if}" type="checkbox" role="switch"
                    value="1" id="hgo_personal_data" name="personal_data" {if $personal_data}checked{/if}>
                <label class="form-check-label" for="hgo_personal_data">Я согласен на обработку персональных данных</label>
                <div class="invalid-feedback">Нужно согласие</div>
            </div>

            <div class="text-end">
                <button class="btn btn-primary" type="submit">Отправить</button>
            </div>
        </form>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </div>
{/block}

{block name=request_sent}
    <div id="help_offer_sent">
        <div class="alert alert-success">Спасибо, ваша заявка отправлена.</div>
    </div>
{/block}