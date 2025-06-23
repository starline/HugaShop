{extends 'wrapper/main.tpl'}
{include 'warehouse/parts/menu_part.tpl'}

{$meta_title='Склады'}

{block name=content}

    {* Заголовок *}
    <div class="header_top">
        <h1>Склады</h1>
        <a class="add" href="/admin/warehouse/place">Добавить склад</a>
    </div>


    <div id="main_list">

        {if $places}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div class="list sortable_on">
                    {foreach $places as $place}
                        <div class="list_row {if !$place->enabled}enabled_off{/if}" item_id="{$place->id}">

                            <div class="move">
                                <div class="move_zone"></div>
                                <input type="hidden" name="positions[{$place->id}]" value="{$place->position}">
                            </div>

                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$place->id}" />
                            </div>

                            <div class="col">
                                <a href="{'PlaceAdmin'|urll:[id=>$place->id]}">{$place->name}</a>
                                <div class="notice">{$place->comment}</div>
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
                            <option value="enable">Включить</option>
                            <option value="disable">Выключить</option>
                            <option value="delete">Удалить</option>
                        </select>
                    </span>
                    <button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
                </div>

            </form>
        {else}
            Еще нет складов
        {/if}
    </div>
{/block}


{block name=body_script append}
    <script type="module">
        import { ajax_icon } from '{"js/common.js"|asset}';

        {literal}
            $(function() {

                // Скрыт/Видим
                $("i.enable").on('click', function() {
                    ajax_icon($(this), 'place', 'enabled', csrf);
                    return false;
                });

            });
        {/literal}
    </script>
{/block}