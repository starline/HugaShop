{extends file='wrapper/main.tpl'}
{include file='content/parts/menu_part.tpl'}

{if $comment->id}
    {if $comment->type == 'product'}
        {$meta_title = 'Комментарий к товару $comment->entity->name'}
    {elseif $comment->type == 'blog'}
        {$meta_title = 'Комментарий к статье $comment->entity->name'}
    {/if}
{/if}

{block name=content}

    <form method="post" enctype="multipart/form-data">
        <input name="id" type="hidden" value="{$comment->id}" />
        <input name="time" type="hidden" name="time" value="{$comment->date|time}">
        {getCSRFInput}

        <div class="row gx-5">

            <div class="col-12">
                <div class="over_name">
                    {if $comment->type == 'product'}
                        <a class="out_link" target="_bkank" href="{$config->root_url}/product/{$comment->entity->id}">Открыть
                            товар на сайте</a>
                    {elseif $comment->type == 'blog'}
                        <a class="out_link" target="_bkank" href="{$config->root_url}/blog/{$comment->entity->url}">Открыть
                            статью на
                            сайте</a>
                    {/if}
                </div>
                <div class="name_row">
                    <h1>{$comment->entity->name}</h1>
                </div>
            </div>

            <div class="col-lg-6">
                <ul class="property_block">
                    <li class="row_sm">
                        <label for="date" class="col-form-label">Дата комментария</label>
                        <div class="input-group with_unit">
                            <input class="form-control" type="text" name="date" id="date" value="{$comment->date|date}" />
                            <span class="input-group-text">в {$comment->date|time}</span>
                        </div>
                    </li>

                    {if !empty($comment->user)}
                        <li>
                            <div class="col-form-label">Пользователь</div>
                            <div><a href="/admin/user/{$comment->user->id}">{$comment->user->name}</a></div>
                        </li>
                    {/if}

                    <li>
                        <label for="name" class="col-form-label">Имя</label>
                        <input class="form-control" id="name" name="name" type="text" autocomplete="given-name"
                            value="{$comment->name}" />
                    </li>
                    <li>
                        <label for="text" class="col-form-label">Комментарий</label>
                        <textarea class="form-control" id="text" name="text">{$comment->text|raw}</textarea>
                    </li>
                </ul>

                <div class="col-12 btn_row">
                    <button class="btn btn-primary" type="submit">Сохранить</button>
                </div>
            </div>

            <div class="col-lg-6 layer">
                <h2>Фотографии</h2>
                {include file='parts\\image_upload_part.tpl' images=$comment->images can_edit=true}
            </div>

        </div>
    </form>

    <script type="module">
        import '{"js/jquery/datepicker/jquery.ui.datepicker-ru.js"|asset}';

        {literal}
            $(function() {
                $('input[name="date"]').datepicker({
                    regional: 'ru'
                });
            });
        {/literal}
    </script>

{/block}