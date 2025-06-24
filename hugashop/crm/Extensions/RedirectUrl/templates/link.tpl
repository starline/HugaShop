{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{if $link->id}
        {$meta_title = $link->url}
{else}
        {$meta_title = 'Новая ссылка'}
{/if}

{block name=content}

        <form method="post" enctype="multipart/form-data">
                <input name="id" type="hidden" value="{$link->id}" />
                {getCSRFInput}

                <div class="row gx-5">
                        <div class="col-12">
                                <div class="over_name">
                                        <div class="checkbox_line">
                                                <div class="form-check">
                                                        <input class="form-check-input" name="enabled" value="1" type="checkbox" id="enabled" {if $link->enabled}checked{/if} />
                                                        <label class="form-check-label" for="enabled">Активна</label>
                                                </div>
                                        </div>
                                </div>

                                <div class="name_row">
                                        <span class="item_id">#{$link->id}</span>
                                </div>
                        </div>

                        <div class="col-lg-6 layer">
                                <h2>Источник</h2>
                                <ul class="property_block">
                                        <li>
                                                <label class="col-form-label" for="url">URL ссылки (regexp)</label>
                                                <input class="form-control" id="url" name="url" type="text" value="{$link->url}" />
                                        </li>
                                </ul>
                        </div>

                        <div class="col-lg-6 layer">
                                <h2>Редирект</h2>
                                <ul class="property_block">
                                        <li>
                                                <label class="col-form-label" for="redirect">URL редиректа</label>
                                                <input class="form-control" id="redirect" name="redirect" type="text" value="{$link->redirect}" />
                                        </li>
                                        <li>
                                                <label class="col-form-label">Переходов</label>
                                                <input class="form-control" type="text" value="{$link->transitions}" disabled />
                                        </li>
                                </ul>
                        </div>

                        <div class="col-12 btn_row">
                                <button class="btn btn-primary" type="submit">Сохранить</button>
                        </div>
                </div>
        </form>
{/block}
