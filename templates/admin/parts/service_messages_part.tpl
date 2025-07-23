{if !$service_messages_empty|empty}
    <!-- Системное Empty сообщение -->
    <div class="message message_empty">
        <div class="text">
            {foreach from=$service_messages_empty item=key}
                {assign var=message_text value=""}
                {if $key == 'name'}
                    {assign var=message_text value='Укажите название'}
                {elseif $key == 'public_name'}
                    {assign var=message_text value='Укажите публичное название'}
                {elseif $key == 'url'}
                    {assign var=message_text value='Укажите URL'}
                {elseif $key == 'redirect'}
                    {assign var=message_text value='Укажите URL для редиректа'}
                {elseif $key == 'place_id'}
                    {assign var=message_text value='Выберите склад'}
                {elseif $key == 'contact'}
                    {assign var=message_text value='Укажите контакт'}
                {elseif $key == 'notifier_id'}
                    {assign var=message_text value='Укажите способ отправки'}
                {elseif $key == 'code'}
                    {assign var=message_text value='Укажите код'}
                {elseif $key == 'not_updated'}
                    {assign var=message_text value='Изменений не найдено'}
                {else}
                    {assign var=message_text value="Укажите {$key}"}
                {/if}

                <span class="badge text-bg-warning">{$message_text|raw}</span>
            {/foreach}

            <span class="badge text-bg-secondary mx-2">{$now|date} в {$now|time}</span>
        </div>
    </div>
{/if}


{if !$service_messages_info|empty}
    <!-- Системное Info сообщение -->
    <div class="message message_empty">
        <div class="text">
            {if item_locked|in_array:$service_messages_info}
                <span class="badge text-bg-danger">Объект редактируется <a
                        href=`{'UserAdmin'|link:[id => $locked_user->id]}`>{$locked_user->name}</a></span>
            {/if}
        </div>
    </div>
{/if}


{if !$service_messages_error|empty}
    <!-- Системное Error сообщение -->
    <div class="message message_error">
        <div class="text">
            {if wh_place_has_products|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Нельзя удалить. На складе есть товары!</span>
            {/if}
            {if not_deleted|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Не могу удалить</span>
            {/if}
            {if not_updated|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Не могу обновить</span>
            {/if}
            {if not_added|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Не могу обновить</span>
            {/if}
            {if not_uploaded_image|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Не могу загрузить изображение</span>
            {/if}
            {if url_exists|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Страница с таким адресом уже существует</span>
            {/if}
            {if phone_exists|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Такой телефон уже используется</span>
            {/if}
            {if email_exists|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Такой email уже используется</span>
            {/if}
            {if error_closing|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Нехватка товара на складе</span>
            {/if}
            {if error_open|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Не могу вернуть товар на склад</span>
            {/if}
            {if error_paid|in_array:$service_messages_error}
                <span class="badge text-bg-danger">Выберите способ оплаты</span>
            {/if}

            <span class="badge text-bg-secondary mx-2">{$now|date} в {$now|time}</span>
        </div>
    </div>
{/if}


{if !$service_messages_success|empty}
    <!-- Системное сообщение -->
    <div class="message message_success">
        <div class="text">
            {if added|in_array:$service_messages_success}<span class="badge text-bg-success">Добавлен</span>{/if}
            {if updated|in_array:$service_messages_success}<span class="badge text-bg-success">Обновлен</span>{/if}
            {if deleted|in_array:$service_messages_success}<span class="badge text-bg-success">Удален</span>{/if}
            {if created|in_array:$service_messages_success}<span class="badge text-bg-success">Создан</span>{/if}
            {if restored|in_array:$service_messages_success}<span class="badge text-bg-success">Восстановлен</span>{/if}

            <span class="badge text-bg-secondary mx-2">{$now|date} в {$now|time}</span>
        </div>
    </div>
{/if}