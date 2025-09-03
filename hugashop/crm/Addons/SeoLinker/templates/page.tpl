{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title=$page->url}

{block name=content}
    <div class="header_top">
        <h1>{$page->url}</h1>
    </div>
    <div id="main_list">
        <div class="layer mb-4">
            <h2>Метаданные</h2>
            <div class="mb-2"><strong>Title:</strong> {$page->meta_title}</div>
            <div class="mb-2"><strong>Description:</strong> {$page->meta_description}</div>
            <div><strong>H1:</strong> {$page->h1}</div>
        </div>
        <div class="row gx-5">
            <div class="col-lg-6 layer">
                <h2>Исходящие ссылки</h2>

                {if $links}
                    <div class="list">
                        {foreach $links as $ln}
                            <div class="list_row">
                                <div class="row col">
                                    <div class="col-12 col-lg-8 text-break">
                                        <a href="{$ln->to_url}" target="_blank">{$ln->to_url}</a>
                                    </div>
                                    <div class="col-12 col-lg-4 text-end">
                                        <span class="badge text-bg-round">
                                            {$ln->type}{if $ln->nofollow} / nofollow{/if}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                {else}
                    Нет ссылок
                {/if}

            </div>
            <div class="col-lg-6 layer">
                <h2>Входящие внутренние ссылки</h2>
                {if $links_in}
                    <div class="list">
                        {foreach $links_in as $ln}
                            <div class="list_row">
                                <div class="row col">
                                    <div class="col-12 col-lg-8 text-break">
                                        {if $ln->from_id}
                                            <a href="{'AddonSeoLinkerPage'|link:[id => $ln->from_id]}">
                                                {$ln->from_url}
                                            </a>
                                        {else}
                                            {$ln->from_url}
                                        {/if}
                                    </div>
                                    <div class="col-12 col-lg-4 text-end">
                                        {if $ln->nofollow}
                                            <span class="badge text-bg-round">nofollow</span>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                {else}
                    Нет ссылок
                {/if}
            </div>
        </div>
    </div>
{/block}
