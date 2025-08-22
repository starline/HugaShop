{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}

{$meta_title='Языки'}

{block name=content}

    <div class="header_top">
        <h1>{$meta_title}</h1>
        <a class="add" href="{'LanguageNewAdmin'|link}">Добавить язык</a>
    </div>

    <div id="main_list">
        {if $languages}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div class="list">
                    {foreach $languages as $language}
                        <div class="list_row" item_id="{$language->id}">
                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$language->id}" />
                            </div>

                            <div class="col row">
                                <div class="col-12 col-sm-8 name">
                                    <a href="{'LanguageAdmin'|link:[id => $language->id]}">{$language->name}</a>
                                    <span class="badge text-bg-secondary ms-2">{$language->code}</span>
                                </div>
                                <div class="col-12 col-sm-4 text-end">

                                    {if $language->main}
                                        <span class="badge text-bg-round">Основной</span>
                                    {/if}
                                </div>
                            </div>

                            <div class="icons">
                                <i class="delete material-icons" title="Удалить">cancel</i>
                            </div>
                        </div>
                    {/foreach}
                </div>

                <div id="action">
                    <span id="check_all" class="dash_link">Выбрать все</span>
                    <span id="select">
                        <select class="form-select" name="action">
                            <option value="">Выбрать действие</option>
                            <option value="delete">Удалить</option>
                        </select>
                    </span>
                    {include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
                </div>
            </form>
        {else}
            Нет языков
        {/if}
    </div>

{/block}