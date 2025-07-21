{extends file='wrapper/main.tpl'}
{include file='extension/parts/menu_part.tpl'}

{if $mailing->id}
    {$meta_title = $mailing->name}
{else}
    {$meta_title = 'Новый способ оповещения'}
{/if}

{block name=content}

    <!-- Основная форма -->
    <form method="post" class="list_form" enctype="multipart/form-data">
        <input name="id" type="hidden" value="{$mailing->id}" />
        {getCSRFInput}

        <div class="row gx-5">

            <div class="col-lg-12">
                <div class="name_row">
                    <input class="form-control form-control-lg name" name="name" type="text" value="{$mailing->name}"
                        autocomplete="off" />
                </div>
            </div>

            <div class="col-lg-6 layer">
                <h2>Настройки рассылки</h2>
                <ul class="property_block">
                    <li>
                        <label for="landing_url" class="col-form-label">Посадочный URL</label>
                        <input class="form-control" name="landing_url" id="landing_url" type="text"
                            value="{$mailing->landing_url}" />
                    </li>

                    <li>
                        <label for="modules">Способы оповещения</label>
                        <select class="form-select" name="notifier_id" id="modules">
                            <option value="">Не установлен</option>
                            {foreach $notifiers as $notifier}
                                <option value="{$notifier->id}" {if $mailing->notifier_id == $notifier->id}selected{/if}>
                                    {$notifier->name}</option>
                            {/foreach}
                        </select>
                    </li>

                    <li>
                        <label for="template_id" class="col-form-label">Шаблон сообщения</label>
                        <input class="form-control" id="template_id" name="template_id" type="text"
                            value="{$mailing->template_id}" />
                    </li>

                    <li>
                        <label for="template" class="col-form-label">Шаблон сообщения</label>
                        {if !$mailing->template->content|empty}
                            <textarea class="form-control" id="template" name="template"
                                disabled>{$mailing->template->content}</textarea>
                        {/if}
                    </li>

                    <li>
                        <label for="product_list" class="col-form-label">Список товаров</label>
                        <textarea class="form-control" id="product_list"
                            name="product_list">{$mailing->product_list|join:"\n"}</textarea>
                    </li>

                    <li>
                        <label for="category_list" class="col-form-label">Список категорий</label>
                        <textarea class="form-control" id="category_list"
                            name="category_list">{$mailing->category_list|join:"\n"}</textarea>
                    </li>
                </ul>
            </div>

            <div class="col-lg-6 layer">
                <h2>Список рассылки</h2>
                <ul class="property_block">
                    <li>
                        <label for="count" class="col-form-label">Кол-во переходов по ссылке</label>
                        <input class="form-control" id="count" type="text" disabled value="{$mailing->count}" />
                    </li>
                    <li>
                        <label for="user_list" class="col-form-label">Телефоны</label>
                        <textarea class="form-control" id="user_list"
                            name="user_list">{$mailing->user_list|join:"\n"}</textarea>
                    </li>
                    <li>
                        <div class="form-check">
                            <input class="form-check-input" id="sending" type="checkbox" name="sending" value="1" />
                            <label class="form-check-label" for="sending" class="col-form-label">Разослать</label>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-12 btn_row">
                {include file="parts/button.tpl"}
            </div>
        </div>

        <div class="list mt-5">
            {if !$mailing_list|empty}
                {include file='parts/pagination.tpl'}

                {foreach $mailing_list as $mailing}
                    {include file='user/parts/mail_item_part.tpl'}
                {/foreach}

                {include file='parts/pagination.tpl'}
            {/if}
        </div>
    </form>

{/block}