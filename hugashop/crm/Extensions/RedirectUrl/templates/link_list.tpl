{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='Redirect URL'}

{block name=content}

        <div class="header_top">
                <h1>{$meta_title}</h1>
                <a class="add" href="/admin/extension/{$extension->module}/link">Добавить ссылку</a>
        </div>

        <div id="main_list">
                {if $links}
                        <form method="post" class="list_form">
                                {getCSRFInput}
                                <div class="list">
                                        {foreach $links as $l}
                                                <div class="{if !$l->enabled}enabled_off{/if} list_row">
                                                        <div class="checkbox">
                                                                <input class="form-check-input" type="checkbox" name="check[]" value="{$l->id}" />
                                                        </div>
                                                        <div class="row col">
                                                                <div class="col-12 col-sm-8">
                                                                        <a href="/admin/extension/{$extension->module}/link/{$l->id}">{$l->url}</a>
                                                                </div>
                                                                <div class="col-12 col-sm-4 text-end">
                                                                        <span class="badge text-bg-round">{$l->transitions}</span>
                                                                </div>
                                                        </div>
                                                        <div class="icons">
                                                                <i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Активна"></i>
                                                                <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
                                                        </div>
                                                </div>
                                        {/foreach}
                                </div>
                                <div id="action">
                                        <span id="check_all" class="dash_link">Выбрать все</span>
                                        <span id="select">
                                                <select class="form-select" name="action">
                                                        <option value="">Выбрать действие</option>
                                                        <option value="enable">Сделать видимыми</option>
                                                        <option value="disable">Сделать невидимыми</option>
                                                        <option value="delete">Удалить</option>
                                                </select>
                                        </span>
                                        <button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
                                </div>
                        </form>
                {else}
                        Нет ссылок
                {/if}
        </div>
{/block}

{block name=body_script append}
        <script type="module">
                var csrf = "{setCSRF}";
                import { ajax_icon } from '{"js/common.js"|asset}';
                {literal}
                        $(function() {
                                $("i.enable").click(function() {
                                        ajax_icon($(this), 'RedirectUrl', 'enabled', csrf);
                                        return false;
                                });
                        });
                {/literal}
        </script>
{/block}
