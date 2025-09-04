{extends file='wrapper/main.tpl'}
{include file='addon/parts/menu_part.tpl'}

{if $pricefeed->id}
    {$meta_title = "Прайс"}
{else}
    {$meta_title = 'Новый прайс'}
{/if}

{block name=content}

    <form method="post" enctype="multipart/form-data">
        <input name="id" type="hidden" value="{$pricefeed->id}" />
        {getCSRFInput}

        <div class="row gx-5">

            <div class="col-12">
                <div class="name_row">
                    <span class="item_id">#{$pricefeed->id}</span>
                    <input class="form-control form-control-lg name" name="name" type="text" value="{$pricefeed->name}" />
                </div>
            </div>

            <div class="col-lg-6 layer">
                <ul class="property_block">
                    <li>
                        <label class="col-form-label" for="show_out_stock">Нет в наличии</label>
                        <select class="form-select" name="show_out_stock" id="show_out_stock">
                            {foreach [0=>'Не показывать товар', 1=>'Показывать товар'] as $val=>$name}
                                <option value="{$val}" {if $val == $pricefeed->show_out_stock} selected{/if}>{$name}</option>
                            {/foreach}
                        </select>
                    </li>
                    <li>
                        <label class="col-form-label" for="sku_id">Тип ID товара</label>
                        <select class="form-select" name="sku_id" id="sku_id">
                            {foreach [0=>'ID варианта', 1=>'SKU варианта'] as $val=>$name}
                                <option value="{$val}" {if $val == $pricefeed->sku_id} selected{/if}>{$name}</option>
                            {/foreach}
                        </select>
                    </li>
                    <li>
                        <label for="label" class="col-form-label">Feed label_0</label>
                        <input class="form-control" name="label" id="label" value="{$pricefeed->label}" />
                    </li>
                    <li>
                        <div class="col-form-label">Feed label_1</div>
                        <div class="col-form-label">url категории товара</div>
                    </li>
                    <li>
                        <label for="currency_code" class="col-form-label">Код валюты</label>
                        <input class="form-control" name="currency_code" id="currency_code"
                            value="{$pricefeed->currency_code}" />
                    </li>
                    <li>
                        <label for="comment" class="col-form-label">Заметки</label>
                        <textarea class="form-control" name="comment" id="comment">{$pricefeed->comment}</textarea>
                    </li>
                    <li>
                        <label for="price_from" class="col-form-label">Фильтр по цене</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="input-group col-6">
                                    <span class="input-group-text">от</span>
                                    <input class="form-control text-center" id="price_from" type="text" name="price_from"
                                        value="{$pricefeed->price_from}" autocomplete="off">
                                    <span class="input-group-text">грн</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group col-6">
                                    <span class="input-group-text">до</span>
                                    <input class="form-control text-center" name="price_to" type="text"
                                        value="{$pricefeed->price_to}">
                                    <span class="input-group-text">грн</span>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-lg-6 layer">
                <h2>Использовать в категориях <span class="sum_total">{$products_count}
                        {$products_count|plural:'товар':'товаров':'товара'}</span></h2>
                <select class="form-select multiple_categories" multiple name="pricefeed_categories[]">
                    {function name=category_select selected_id=$product_category level=0}
                        {foreach $categories as $category}
                            <option value="{$category->id}" {if !$category->visible}class="disabled" {/if}
                                {if in_array($category->id, $pricefeed_categories)}selected{/if}
                                category_name="{$category->single_name}">
                                {section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name}</option>
                            {category_select categories=$category->subcategories selected_id=$selected_id  level=$level+1}
                        {/foreach}
                    {/function}
                    {category_select categories=$categories}
                </select>
            </div>

            <div class="col-12 btn_row">
                {include file="parts/button.tpl"}
            </div>
        </div>
    </form>

{/block}