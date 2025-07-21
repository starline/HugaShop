{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}

{if $language->id}
    {$meta_title = $language->name}
{else}
    {$meta_title = 'Новый язык'}
{/if}

{block name=content}
    <form method="post">
        <input name="id" type="hidden" value="{$language->id}" />
        {getCSRFInput}

        <div class="row gx-5">
            <div class="col-12">
                <div class="name_row">
                    <div class="col">
                        <input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}"
                            name="name" type="text" value="{$language->name}" placeholder="Название языка" />
                        <div class="invalid-feedback">Введите название языка</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 layer">
                <ul class="property_block">
                    <li class="row_sm">
                        <label class="col-form-label" for="code">Код языка</label>
                        <select class="form-select" id="code" name="code">
                            {foreach $language_codes as $lc}
                                <option value="{$lc}" {if $language->code == $lc}selected{/if}>{$lc}</option>
                            {/foreach}
                        </select>
                    </li>
                    <li class="row_sm">
                        <label class="col-form-label" for="country_code">Код страны</label>
                        <select class="form-select" id="country_code" name="country_code">
                            {foreach $country_codes as $cc}
                                <option value="{$cc}" {if $language->country_code == $cc}selected{/if}>{$cc}</option>
                            {/foreach}
                        </select>
                    </li>
                    <li class="row_sm">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="main" name="main" value="1"
                                {if $language->main}checked{/if} />
                            <label class="form-check-label" for="main">Основной язык</label>
                        </div>
                    </li>
                </ul>
                <div class="btn_row">
                    {include file="parts/button.tpl"}
                </div>
            </div>
        </div>
    </form>
{/block}