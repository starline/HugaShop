{extends file='wrapper/main.tpl'}
{include file='addon/parts/menu_part.tpl'}

{$meta_title='Заявки на шиномонтаж'}

{block name=content}
    <div class="header_top">
        {if $bookings_count}
            <h1>{$bookings_count} {$bookings_count|plural:'заявка':'заявок':'заявки'}</h1>
        {else}
            <h1>Нет заявок</h1>
        {/if}
    </div>

    <div id="main_list">
        {if $bookings->isNotEmpty()}
            {include file='parts/pagination.tpl'}
            <form method="post" class="list_form">
                {getCSRFInput}
                <div class="list">
                    {foreach $bookings as $booking}
                        <div class="list_row">
                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$booking->id}" />
                            </div>
                            <div class='col'>
                                <div>
                                    <span class="badge text-bg-round">{$booking->date} {$booking->time}</span>
                                </div>
                                <div>{$booking->name} <span class="badge text-bg-round ms-2">{$booking->phone}</span></div>
                                {if $booking->comment}
                                    <div class="notice">{$booking->comment|strip_tags|nl2br|raw}</div>
                                {/if}
                                <div class="badge text-bg-round my-2">{$booking->created_at|date:m} {$booking->created_at|time}
                                </div>
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
                    {include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
                </div>
            </form>

            {include file='parts/pagination.tpl'}
        {else}
            Нет заявок
        {/if}
    </div>
{/block}

{block name=body_script append}
    <script type="module">
        import { ajaxEntityUpdateIcon } from '{"js/common.js"|asset}';
        {literal}
            $(function() {
                $("i.enable").click(function() {
                    ajaxEntityUpdateIcon($(this), 'booking_shinomontag', 'visible', csrf);
                    return false;
                });
            });
        {/literal}
    </script>
{/block}