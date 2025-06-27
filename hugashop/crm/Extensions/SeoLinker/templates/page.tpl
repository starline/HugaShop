{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title=$page->url}

{block name=content}
    <div class="header_top">
        <h1>{$page->url}</h1>
    </div>
    <div id="main_list">
        {if $links}
            <div class="list">
                {foreach $links as $ln}
                    <div class="list_row">
                        <div class="row col">
                            <div class="col-12 col-lg-8 text-break">
                                <a href="{$ln->to_url}" target="_blank">{$ln->to_url}</a>
                            </div>
                            <div class="col-12 col-lg-4 text-end">
                                <span class="badge text-bg-round">{$ln->type}</span>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            Нет ссылок
        {/if}
    </div>
{/block}
