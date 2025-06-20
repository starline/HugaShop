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
                        <label class="col-form-label" for="code">Код</label>
                        <input class="form-control" id="code" name="code" type="text" value="{$language->code}" />
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
                    <button class="btn btn-primary" type="submit">Сохранить</button>
                </div>
            </div>
        </div>
    </form>
{/block}