{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='OpenAI'}

{block name=content}
    <div class="header_top">
        <h1>Запрос в OpenAI</h1>
    </div>

    <form id="openai_form" method="post">
        {getCSRFInput}

        {if isset($models)}
            <div class="mb-3">
                <label class="form-label" for="model">Model</label>
                <select class="form-select" name="model" id="model">
                    {foreach $models as $m_key => $m_title}
                        <option value="{$m_key}" {if $m_key=='gpt-4o'}selected{/if}>{$m_title}</option>
                    {/foreach}
                </select>
            </div>
        {/if}

        <div class="mb-3">
            <label class="form-label" for="system_content">System content</label>
            <textarea class="form-control" name="system_content" id="system_content" rows="5"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label" for="user_content">User content</label>
            <textarea class="form-control" name="user_content" id="user_content" rows="5"></textarea>
        </div>

        <div class="col-12 btn_row">
            {include file="parts/button.tpl" label="Отправить"}
        </div>
    </form>

    <div id="openai_result" class="mt-3"></div>

    <script type="module">
        const open_ai_url = "{'AddonOpenAIRequest'|link}";

        {literal}
            $(function() {
                $('#openai_form').on('submit', function(e) {
                    e.preventDefault();
                    const $form = $(this);
                    const $submit_btn = $form.find('button[type="submit"]');
                    $('#openai_result').empty();
                    $.ajax({
                        url: open_ai_url,
                        type: 'POST',
                        dataType: 'json',
                        data: $form.serialize(),
                        success: function(resp) {
                            $submit_btn.removeClass('disabled').prop('disabled', false);
                            if (resp && resp.content) {
                                const $pre = $('<pre/>').text(resp.content);
                                $('#openai_result').empty().append($pre[0]);
                            } else if (resp && resp.error) {
                                const msg = resp.message ? resp.message : resp.error;
                                $('#openai_result').html('<div class="text-danger">' + msg +
                                    '</div>');
                            }
                        }
                    });
                });
            });
        {/literal}
    </script>
{/block}