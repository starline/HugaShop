{extends file='wrapper/main.tpl'}
{include file='extension/parts/menu_part.tpl'}

{$meta_title='Лиды'}

{block name=content}
    <div class="header_top">
        {if $leads_count}
            <h1>{$leads_count} {$leads_count|plural:'лид':'лидов':'лида'}</h1>
        {else}
            <h1>Нет лидов</h1>
        {/if}
    </div>

    <div id="main_list">
        {if $leads->isNotEmpty()}
            <table class="table">
                <thead>
                    <tr>
                        <th>Телефон</th>
                        <th>Клиент</th>
                        <th>Последний звонок</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $leads as $lead}
                        <tr>
                            <td>{$lead->phone}</td>
                            <td>{if $lead->client}{$lead->client->name}{/if}</td>
                            <td>{if $lead->last_call_at}{$lead->last_call_at|date_format:'%d.%m.%Y %H:%M'}{/if}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            {include file='parts/pagination.tpl'}
        {else}
            Нет лидов
        {/if}
    </div>
{/block}
