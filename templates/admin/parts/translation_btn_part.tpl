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
                    <span class="material-icons">translate</span>AI Перевод
                </button>
            </div>
        {/if}
    </div>

    {block name=body_script append}
        <script type="module">
            {literal}

                $('#translate_button').on('click', function() {
                    const btn = $(this);
                    btn.prop('disabled', true);
                    $.ajax({
                        url: '/admin/ajax/product/translate',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            product_id: $('input[name=id]').val(),
                            lang: $('#language_select').val(),
                            csrf: csrf
                        },
                        success: function(data) {
                            for (const field in data) {
                                const el = $('[name="' + field + '"]');
                                if (field === 'body' && typeof tinymce !== 'undefined') {
                                    tinymce.activeEditor.setContent(data[field]);
                                } else if (el.length) {
                                    el.val(data[field]);
                                }
                            }
                        },
                        complete: function() {
                            btn.prop('disabled', false);
                        }
                    });
                    return false;
                });

            {/literal}
        </script>
    {/block}
{/if}