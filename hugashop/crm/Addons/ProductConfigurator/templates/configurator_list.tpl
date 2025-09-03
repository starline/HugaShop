{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Конфигураторы'}

{block name=content}
    <div class="header_top">
        <h1>{$meta_title}</h1>
        <a class="add" href="{'AddonProductConfiguratorNew'|link}">Добавить конфигуратор</a>
    </div>

    <div id="main_list">
        {if $configurators}
            <form method="post" class="list_form">
                {getCSRFInput}
                <div class="list sortable_on">
                    {foreach $configurators as $conf}
                        <div class="list_row {if !$conf->enabled}enabled_off{/if}">
                            <div class="move">
                                <div class="move_zone"></div>
                                <input type="hidden" name="positions[{$conf->id}]" value="{$conf->position}">
                            </div>
                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$conf->id}" />
                            </div>
                            <div class="row col">
                                <div class="col-12 col-sm-8">
                                    <a href="{'AddonProductConfigurator'|link:[id => $conf->id]}">{$conf->name}</a>
                                </div>
                                <div class="col-12 col-sm-4 text-end">
                                    <span class="badge text-bg-round">{$conf->id}</span>
                                </div>
                            </div>
                            <div class="icons">
                                <i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Активен"></i>
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
                    {include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
                </div>
            </form>
        {else}
            Нет конфигураторов
        {/if}
    </div>
{/block}

{block name=body_script append}
    <script type="module">
        import { ajaxEntityUpdateIcon } from '{"js/common.js"|asset}';
        {literal}
            $(function() {
                $("i.enable").click(function() {
                    ajaxEntityUpdateIcon($(this), 'ProductConfigurator', 'enabled', csrf);
                    return false;
                });
            });
        {/literal}
    </script>
{/block}