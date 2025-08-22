{block name=price_request}
    <div id="wish_cheaper">
        <div class="row">
            <div class="col-12 col-md-6">
                <div>
                    <img src="{$product->image->filename|resize:720:720:w}" alt="{$product->name}" class="img-fluid mb-3">
                </div>
                <p>Товар: <strong>{$product->name}</strong></p>
                <p>Цена: <strong>{$product->price|price_html|raw}</strong></p>
            </div>

            <div class="col-12 col-md-6">

                <div class="mb-5">
                    <h1>Запросить цену</h1>
                    <p>Если вы нашли этот товар дешевле, пожалуйста, сообщите нам об этом. Мы постараемся предложить вам
                        лучшую
                        цену.</p>
                </div>

                {if $error}
                    <div class="alert alert-danger">
                        {if $error=='captcha'}
                            Подтвердите что вы не робот
                        {/if}
                    </div>
                {/if}

                <form method="post" action="{'AddonPriceRequestForm'|link}" class="needs-validation">
                    <input type="hidden" name="product_id" value="{$product->id}">
                    {getCSRFInput}

                    <div class="mb-3">
                        <label class="form-label" for="pr_name">ФИО</label>
                        <input class="form-control {if 'name'|in_array:$form_invalid}is-invalid{/if}" type="text"
                            name="name" id="pr_name" value="{$request->name}">
                        <div class="invalid-feedback">Введите имя</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pr_phone">Телефон</label>
                        <input class="form-control" type="text" name="phone" id="pr_phone" value="{$request->phone}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pr_email">Email</label>
                        <input class="form-control {if 'email'|in_array:$form_invalid}is-invalid{/if}" type="email"
                            name="email" id="pr_email" value="{$request->email}">
                        <div class="invalid-feedback">Введите Email</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pr_comment">Ссылка на дешевле или причина запроса</label>
                        <input class="form-control" type="text" name="comment" id="pr_comment" value="{$request->comment}">
                    </div>
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary" type="submit">Отправить</button>
                    </div>
                </form>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            </div>
        </div>
    </div>
{/block}

{block name=request_sent}
    <div id="wish_cheaper_sent">
        <div class="alert alert-success">Спасибо, ваш запрос отправлен.</div>
    </div>
{/block}