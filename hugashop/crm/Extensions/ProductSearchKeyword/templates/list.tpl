{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='Поисковые запросы'}

{block name=content}
    <div class="header_top">
        <h1>{$meta_title}</h1>
    </div>
    <div id="main_list">
        {if $keywords}
            {include file='parts/pagination.tpl'}
            <div class="list">
                {foreach $keywords as $k}
                    <div class="list_row">
                        <div class="col-8 text-break">{$k->name}</div>
                        <div class="col-4 text-end">{$k->created_at|date} {$k->created_at|time}</div>
                    </div>
                {/foreach}
            </div>
            {include file='parts/pagination.tpl'}
        {/if}
    </div>
{/block}
