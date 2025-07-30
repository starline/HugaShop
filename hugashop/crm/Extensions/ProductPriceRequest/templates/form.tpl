<div id="wish_cheaper">
    <form id="price_request_form" method="post" class="needs-validation">
        {getCSRFInput}
        <input type="hidden" name="product_id" value="{$form_data->product_id}">
        <div class="mb-3">
            <label class="form-label" for="pr_name">ФИО</label>
            <input class="form-control {if 'name'|in_array:$form_invalid}is-invalid{/if}" type="text" name="name"
                id="pr_name" value="{$form_data->name}">
            <div class="invalid-feedback">Введите имя</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="pr_phone">Телефон</label>
            <input class="form-control {if 'phone'|in_array:$form_invalid}is-invalid{/if}" type="text" name="phone"
                id="pr_phone" value="{$form_data->phone}">
            <div class="invalid-feedback">Введите телефон</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="pr_email">Email</label>
            <input class="form-control {if 'email'|in_array:$form_invalid}is-invalid{/if}" type="email" name="email"
                id="pr_email" value="{$form_data->email}">
            <div class="invalid-feedback">Введите Email</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="pr_link">Ссылка на дешевле</label>
            <input class="form-control {if 'link'|in_array:$form_invalid}is-invalid{/if}" type="text" name="link"
                id="pr_link" value="{$form_data->link}">
            <div class="invalid-feedback">Укажите ссылку</div>
        </div>
        <div class="text-end">
            <button class="btn btn-light" type="submit">Отправить</button>
        </div>
    </form>
</div>