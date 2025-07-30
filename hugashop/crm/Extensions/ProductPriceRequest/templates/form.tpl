{block name=price_request}
    <div id="wish_cheaper">
        <div class="row">
            <div class="col-12 mb-3">
                <h1>Запросить цену</h1>
                <p>Если вы нашли этот товар дешевле, пожалуйста, сообщите нам об этом. Мы постараемся предложить вам лучшую
                    цену.</p>
            </div>

            <div class="col-12 col-md-6">
                <div>
                    <img src="{$product->image->filename|resize:720}" alt="{$product->name}" class="img-fluid mb-3">
                </div>
                <p>Товар: <strong>{$product->name}</strong></p>
                <p>Цена: <strong>{$product->price}</strong></p>
            </div>

            <div class="col-12 col-md-6">
                <form method="post" action="{'ExtPriceRequestForm'|link}" class="needs-validation">
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
                        <label class="form-label" for="pr_link">Ссылка на дешевле</label>
                        <input class="form-control" type="text" name="link" id="pr_link" value="{$request->link}">
                    </div>
                    <div class="text-end">
                        <button class="btn btn-light" type="submit">Отправить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/block}

{block name=request_sent}
    <div id="wish_cheaper_sent">
        <div class="alert alert-success">Спасибо, ваш запрос отправлен.</div>
    </div>
{/block}