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
                <button id="translate_button" type="button" class="btn btn-secondary">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <span class="btn-content">
                        <span class="material-icons">translate</span>
                        AI Перевод
                    </span>
                </button>
            </div>
        {/if}
    </div>

    {block name=body_script append}
        <script type="module">
            import { allButtonOn } from '{"js/common.js"|asset}';

            const entity = '{$entity}';
            const translate_url = "{'AddonOpenAITranslate'|link}";

            {literal}

                $('#translate_button').on('click', function() {
                    const btn = $(this);

                    // Проверка: если entity не задан — отменяем
                    if (typeof entity === 'undefined' || !entity) {
                        console.warn('entity is not defined');
                        return false;
                    }

                    $.ajax({
                        url: translate_url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            entity: entity,
                            id: $('input[name=id]').val(),
                            lang: $('#language_select').val(),
                            csrf: window.csrf
                        },
                        success: function(data) {
                            for (const field in data) {
                                const value = data[field];
                                let el = $('[name="' + field + '"]');

                                // Специальный формат: массив объектов {id, value}
                                if (
                                    field === 'options' &&
                                    Array.isArray(value) &&
                                    value.length &&
                                    typeof value[0] === 'object' &&
                                    'id' in value[0]
                                ) {
                                    value.forEach(function(opt) {
                                        const row = $('input[name^="options"][name$="[id]"][value="' +
                                            opt.id + '"]').closest('.list_row');
                                        if (row.length) {
                                            row.find('input[name^="options"][name$="[value]"]').val(opt
                                                .value);
                                        }
                                    });
                                    continue;
                                }

                                // Если пришел массив значений (например, options[])
                                if (Array.isArray(value)) {
                                    el = $('[name="' + field + '[]"]');
                                    el.each(function(index) {
                                        if (value[index] !== undefined) {
                                            $(this).val(value[index]);
                                        }
                                    });
                                    continue;
                                }

                                // Если пришел объект значений (например, options[id])
                                if (value && typeof value === 'object') {
                                    for (const key in value) {
                                        const optEl = $('[name="' + field + '[' + key + ']"]');
                                        if (optEl.length) {
                                            optEl.val(value[key]);
                                        }
                                    }
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
                            allButtonOn('#translate_button');
                        }
                    });
                    return false;
                });

            {/literal}
        </script>
    {/block}
{/if}