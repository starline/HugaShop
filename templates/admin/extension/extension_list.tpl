{extends file='wrapper/main.tpl'}
{include file='extension/parts/menu_part.tpl'}

{$meta_title='Модули расширения'}

{block name=content}

    <!-- Заголовок -->
    <div class="header_top">
        <h1>{$meta_title}</h1>
        
        <form method="get" id="search">
            {getCSRFInput}
            <div class="input-group">
                <input class="search form-control" type="text" name="keyword" value="{$keyword}" />
                <input class="input-group-text search_button" type="submit" value="" />
            </div>
        </form>
    </div>

    <div id="main_list">
        {if $extension_modules}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div id="extensions" class="list">
                    {foreach $extension_modules as $ext_module}
                        <div class="list_row">
                            <div class="col">
                                <a href="{'ExtensionAdmin'|link:[name => $ext_module->module]}">{$ext_module->name}</a>
                                <div class="notice">{$ext_module->description}</div>
                            </div>
                            <div class="col-2">
                                {if $ext_module->version}
                                    <span class="badge text-bg-round">v {$ext_module->version}</span>
                                {/if}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </form>
        {else}
            Здесь еще нет модулей расширения
        {/if}
    </div>

{/block}