{if !$service_messages_empty|empty}
    <!-- Системное сообщение -->
    <div class="message message_empty">
        <div class="text">
            {if name|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Укажите название</span>
            {/if}
            {if public_name|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Укажите публичное название</span>
            {/if}
            {if url|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Укажите URL</span>
            {/if}
            {if place_id|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Выберите склад</span>
            {/if}
            {if contact|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Укажите контакт</span>
            {/if}
            {if notifier_id|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Укажите способ отправки</span>
            {/if}
            {if code|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Укажите код</span>
            {/if}
            {if not_updated|in_array:$service_messages_empty}
                <span class="badge text-bg-warning">Изменений не найдено</span>
            {/if}

            <span class="badge text-bg-secondary mx-2">{$now|date} в {$now|time}</span>
        </div>
    </div>
{/if}


{if !$service_messages_error|empty}
    <!-- Системное сообщение -->
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