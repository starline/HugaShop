{if $languages}
    <div class="row mb-3">
        <div class="col-auto">
            <select id="language_select" class="form-select form-select">
                {foreach $languages as $language}
                    <option value="{$language->code}" {if $current_language->code == $language->code}selected{/if}>
                        {$language->name}
                    </option>
                {/foreach}
            </select>
        </div>

        {if $current_language->code != $main_language->code}
            <div class="col-auto">
                <button id="translate_button" type="button" class="btn btn-secondary position-relative">
                    <span class="spinner-border spinner-border-sm position-absolute d-none" aria-hidden="true"
                        style="left: 46%; top: 25%;"></span>
                    <span class="btn-content d-flex align-items-center gap-1">
                        <span class="material-icons">translate</span>
                        AI Перевод
                    </span>

                </button>
            </div>
        {/if}
    </div>

    {block name=body_script append}
        <script type="module">
            const entity = '{$entity}';

            {literal}

                $('#translate_button').on('click', function() {
                    const btn = $(this);

                    // Проверка: если entity не задан — отменяем
                    if (typeof entity === 'undefined' || !entity) {
                        console.warn('entity is not defined');
                        return false;
                    }

                    btn.prop('disabled', true);
                    btn.find('.spinner-border').removeClass('d-none');
                    btn.find('.btn-content').addClass('invisible');

                    $.ajax({
                        url: '/admin/extension/OpenAI/ajax/translate',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            entity: entity,
                            id: $('input[name=id]').val(),
                            lang: $('#language_select').val(),
                            csrf: csrf
                        },
                        success: function(data) {
                            for (const field in data) {
                                const value = data[field];
                                let el = $('[name="' + field + '"]');

                                // Если пришел массив значений (например, options)
                                if (Array.isArray(value)) {
                                    el = $('[name="' + field + '[]"]');
                                    el.each(function(index) {
                                        if (value[index] !== undefined) {
                                            $(this).val(value[index]);
                                        }
                                    });
                                    continue;
                                }

                                // Если это редактор TinyMCE
                                if (typeof tinymce !== 'undefined' && tinymce.get(field)) {
                                    tinymce.get(field).setContent(value);
                                }

                                // Если обычный элемент формы
                                else if (el.length) {
                                    el.val(value);
                                }
                            }
                        },
                        complete: function() {
                            btn.prop('disabled', false);
                            btn.find('.spinner-border').addClass('d-none');
                            btn.find('.btn-content').removeClass('invisible');
                        }
                    });
                    return false;
                });

            {/literal}
        </script>
    {/block}
{/if}