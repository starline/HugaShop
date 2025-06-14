{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='SMS Рассылки'}

{block name=content}

    <!-- Заголовок -->
    <div class="header_top">
        <h1>{$meta_title}</h1>
        <a class="add" href="/admin/extension/{$extension->module}/mailing">Добавить рассылку</a>
    </div>

    <div id="main_list">
        {if $mailings}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div class="list">
                    {foreach $mailings as $mail}
                        <div class="list_row">
                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$mail->id}" />
                            </div>
                            <div class="col">
                                <a href="/admin/extension/{$extension->module}/mailing/{$mail->id}">{$mail->name}</a>
                                <div class="notice">{$mail->comment}</div>
                            </div>
                            <div class="icons">
                                <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
                            </div>
                        </div>
                    {/foreach}
                </div>

                <div id="action">
                    <span id="select">
                        <select class="form-select" name="action">
                            <option value="">Выбрать действие</option>
                            <option value="delete">Удалить</option>
                        </select>
                    </span>
                    <button class="btn btn-primary apply" type="submit">Применить</button>
                </div>
            </form>
        {/if}
    </div>

{/block}