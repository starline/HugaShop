{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Подписки на новости'}

{block name=content}
    <div class="header_top">
        {if $requests_count}
            <h1>{$requests_count} {$requests_count|plural:'подписка':'подписок':'подписки'}</h1>
        {else}
            <h1>Нет подписок</h1>
        {/if}
    </div>
    <div id="main_list">
        {if $requests->isNotEmpty()}
            {include file='parts/pagination.tpl'}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div class="list">
                    {foreach $requests as $r}
                        <div class="list_row">
                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$r->id}" />
                            </div>

                            <div class="col">
                                <a href="{'ExtSubscribeOfferItem'|link:[id => $r->id]}">{$r->email}</a>
                                <span class="badge text-bg-round">{$r->created_at|date} {$r->created_at|time}</span>
                            </div>

                            <div class="icons">
                                <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
                            </div>
                        </div>
                    {/foreach}
                </div>

                <div id="action">
                    <span id='check_all' class='dash_link'>Выбрать все</span>
                    <span id=select>
                        <select class="form-select" name="action">
                            <option value="">Выбрать действие</option>
                            <option value="delete">Удалить</option>
                        </select>
                    </span>
                    <button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
                </div>
            </form>
            {include file='parts/pagination.tpl'}
        {else}
            Нет подписок
        {/if}
    </div>
{/block}