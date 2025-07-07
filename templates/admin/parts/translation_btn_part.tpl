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
                <button id="translate_button" type="button" class="btn btn-secondary d-flex align-items-center gap-1">
                    <span class="material-icons">translate</span>
                    <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                    AI Перевод
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
                                const el = $('[name="' + field + '"]');

                                // Если это редактор TinyMCE
                                if (typeof tinymce !== 'undefined' && tinymce.get(field)) {
                                    tinymce.get(field).setContent(data[field]);
                                }
                                
                                // Если обычный элемент формы
                                else if (el.length) {
                                    el.val(data[field]);
                                }
                            }
                        },
                        complete: function() {
                            btn.prop('disabled', false);
                            btn.find('.spinner-border').addClass('d-none');
                        }
                    });
                    return false;
                });

            {/literal}
        </script>
    {/block}
{/if}