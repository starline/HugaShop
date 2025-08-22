{extends file='wrapper/main.tpl'}
{include file='addon/parts/menu_part.tpl'}

{$meta_title='Модули дополнения'}

{block name=content}

    <!-- Заголовок -->
    <div class="header_top">
        <h1>{$meta_title}</h1>

        <form method="get" id="search">
            <div class="input-group">
                <input class="search form-control" type="text" name="keyword" placeholder="Название модуля"
                    value="{$keyword}" />
                <input class="input-group-text search_button" type="submit" value="" />
            </div>
        </form>
    </div>

    <div id="main_list">
        {if $addon_modules}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div id="addons" class="list">
                    {foreach $addon_modules as $ext_module}
                        <div class="list_row">
                            <div class="col">
                                <a href="{'AddonAdmin'|link:[name => $ext_module->module]}">{$ext_module->name}</a>
                                <div class="notice">{$ext_module->description}</div>
                            </div>
                            <div class="col-2">
                                {if $ext_module->version}
                                    <span class="badge text-bg-round">Версия {$ext_module->version}</span>
                                {/if}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </form>
        {else}
            {if $keyword}
                Модули не найдены
            {else}
                Здесь еще нет модулей расширения
            {/if}
        {/if}
    </div>

{/block}