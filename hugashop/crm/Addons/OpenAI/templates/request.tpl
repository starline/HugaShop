{include 'addon/parts/menu_part.tpl'}

{$meta_title='OpenAI'}

{block name=content}
    <div class="header_top">
        <h1>Запрос в OpenAI</h1>
    </div>

    <form id="openai_form" method="post">
        {getCSRFInput}
        <div class="mb-3">
            <label class="form-label" for="system_content">System content</label>
            <textarea class="form-control" name="system_content" id="system_content" rows="5"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label" for="user_content">User content</label>
            <textarea class="form-control" name="user_content" id="user_content" rows="5"></textarea>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Отправить</button>
        </div>
    </form>

    <div id="openai_result" class="mt-3"></div>

    <script type="module">
        {literal}
        $(function() {
            $('#openai_form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{'AddonOpenAIRequest'|link}",
                    type: 'POST',
                    data: $('#openai_form').serialize(),
                    success: function(resp) {
                        if (resp.content) {
                            $('#openai_result').html('<pre>' + resp.content + '</pre>');
                        } else if (resp.error) {
                            $('#openai_result').html('<div class="text-danger">' + resp.error + '</div>');
                        }
                    }
                });
            });
        });
        {/literal}
    </script>
{/block}
