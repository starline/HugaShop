{extends file='wrapper/main.tpl'}
{include file='addon/parts/menu_part.tpl'}

{$meta_title='Прайсы Google'}

{block name=content}

    <!-- Заголовок -->
    <div class="header_top">
        <h1>{$meta_title}</h1>
        <a class="add" href="{'ExtGoogleMerchantNew'|link}">Добавить прайс</a>
    </div>

    <div id="main_list">
        {if $pricefeeds}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div class="list sortable_on">
                    {foreach $pricefeeds as $pricefeed}
                        <div class="list_row" item_id="{$pricefeed->id}">

                            <div class="move">
                                <div class="move_zone"></div>
                                <input type="hidden" name="positions[{$pricefeed->id}]" value="{$pricefeed->position}" />
                            </div>

                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$pricefeed->id}" />
                            </div>

                            <div class="col">
                                <a href="{'ExtGoogleMerchant'|link:[id => $pricefeed->id]}">{$pricefeed->name}</a>

                                <div class="badge text-bg-round copy_field"
                                    value="{$config->root_url}{'ExtGoogleMerchantFeed'|link:[id => $pricefeed->id, token => $pricefeed->token]}">
                                    {$config->root_url}{'ExtGoogleMerchantFeed'|link:[id => $pricefeed->id, token => $pricefeed->token]}
                                    <div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
                                        <i class="material-icons">content_copy</i>
                                    </div>
                                </div>

                                {if !$pricefeed->comment|empty}
                                    <div class="notice">{$pricefeed->comment|strip_tags|nl2br|raw}</div>
                                {/if}

                            </div>

                            {if $pricefeed->label}
                                <div>
                                    <span class="badge text-bg-round">{$pricefeed->label}</span>
                                </div>
                            {/if}

                            <div class="icons">
                                <a class="material-icons launch" data-bs-toggle="tooltip" title="Открыть прайс"
                                    href="{'ExtGoogleMerchantFeed'|link:[id => $pricefeed->id, token => $pricefeed->token]}"
                                    target="_blank"></a>
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
                            <option value="delete">Удалить</option>
                        </select>
                    </span>
                    <button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
                </div>
            </form>
        {/if}
    </div>

{/block}