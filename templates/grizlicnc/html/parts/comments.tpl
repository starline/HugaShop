<!-- Комментарии -->
<div id="comments">

    <h2>{'Отзывы и Комментарии'|trans}</h2>

    {if $comments}
        <ul class="comment_list">
            {foreach $comments as $comment}
                <li id="comment_{$comment->id}">
                    <div class="comment_header">
                        {$comment->name} <i>{$comment->date|date} в {$comment->date|time}</i>
                        {if !$comment->approved}<span class="await_approval">{'ожидает модерации'|trans}</span>{/if}
                    </div>

                    <div class="comment_body">
                        {$comment->text|strip_tags|nl2br|raw}
                    </div>

                    {if $comment->images}
                        <div class="comment_images row g-2 my-2">
                            {foreach $comment->images as $image}
                                <div class="col-auto">
                                    <a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="comment{$comment->id}">
                                        <img src="{$image->filename|resize:120:120:c}" width="120" height="120" />
                                    </a>
                                </div>
                            {/foreach}
                        </div>
                    {/if}

                    <span class="link add_answer" data-id="{$comment->id}">Ответить</span>
                </li>

                {if isset($comment->answer)}
                    {foreach $comment->answer as $answer}
                        <li class="answer" id="comment_{$answer->id}">
                            <div class="comment_header">
                                {$answer->name} <i>{$answer->date|date} в {$answer->date|time}</i>
                                {if !$answer->approved}<span class="await_approval">{'ожидает модерации'|trans}</span>{/if}
                            </div>
                            {$answer->text|strip_tags|nl2br|raw}
                        </li>
                    {/foreach}
                {/if}

            {/foreach}
        </ul>
    {else}
        <p>
            {'Ваш комментарий будет первым!'|trans}
        </p>
    {/if}


    <!-- Форма отправления комментария -->
    <form id="comment_form" class="comment_form  needs-validation" method="post" action="#comments"
        enctype="multipart/form-data">
        <div class="row g-4">
            <input type="hidden" id="comment_related_id" name="comment_related_id" value="" />
            {getCSRFInput}

            <h4>{'Написать комментарий'|trans}</h4>

            {if $error}
                <div class="col-12">
                    <div class="alert alert-danger">
                        {if $error=='captcha'}
                            {'Подтвердите что вы не робот'|trans}
                        {/if}
                    </div>
                </div>
            {/if}

            <div class="col-lg-4">
                <label class="form-label" for="comment_name">Имя</label>
                <input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" type="text"
                    id="comment_name" name="comment_name" value="{$comment_name}" placeholder="{'Укажите имя'|trans}"
                    autocomplete="name" />
                <div class="invalid-feedback">{'Укажите имя'|trans}</div>
            </div>

            <div class="col-lg-4">
                <input class="comment_email {if email|in_array:$form_invalid}is-invalid{/if}" type="email"
                    id="comment_email" name="comment_email" value="" placeholder="{'Укажите email'|trans}">
                <div class="invalid-feedback">{'Укажите email'|trans}</div>
            </div>

            <div class="col-12">
                <textarea class="form-control comment_textarea {if text|in_array:$form_invalid}is-invalid{/if}"
                    id="comment_text" name="comment_text"
                    placeholder="{'Ваш комментарий'|trans}">{$comment_text}</textarea>
                <div class="invalid-feedback">{'Укажите сообщение'|trans}</div>
            </div>

            <div class="col-12">
                <div class="col-lg-3">
                    <input class="form-control" type="file" name="comment_images[]" accept="image/*" multiple>
                </div>
                <div id="comment_images_preview" class="comment_images row g-2 mt-2"></div>
            </div>

            <div class="col-12">
                <div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
            </div>


            <div class="col-12">
                {include file="parts/button.tpl" label="{'Отправить'|trans}" class="btn-light" type="submit"
                extra_attrs='name=comment value=true'}
            </div>
        </div>
    </form>
</div>


<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script type="module">
    {literal}
        $('.add_answer').click(function() {
            let id = $(this).data('id');
            $('form.comment_form').find('#comment_related_id').val(id);
            $('#comment_' + id).append($('form.comment_form'));
        });

        const previewContainer = $('#comment_images_preview');
        const fileInput = $('input[name="comment_images[]"]');

        fileInput.on('change', function(e) {
            previewContainer.empty();
            const files = e.target.files;
            const limit = 6;
            for (let i = 0; i < files.length && i < limit; i++) {
                const file = files[i];
                if (!file.type.match('image.*')) {
                    continue;
                }
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const col = $('<div class="col-auto"></div>');
                    const link = $('<a class="zoom" data-fancybox="preview"></a>')
                        .attr('href', evt.target.result);
                    $('<img class="img-thumbnail" width="220" height="220" />')
                        .attr('src', evt.target.result)
                        .appendTo(link);
                    col.append(link);
                    previewContainer.append(col);
                    link.fancybox({
                        buttons: ['close'],
                        image: { preload: true },
                        closeExisting: true,
                        defaultType: 'image'
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    {/literal}
</script>