{extends file='wrapper/main.tpl'}
{include file='extension/parts/menu_part.tpl'}

{$meta_title = $extension->name}

{block name=content}

    <!-- Основная форма -->
    <form method="post" enctype="multipart/form-data">
        <input name="module" type="hidden" value="{$extension->module}" />
        {getCSRFInput}

        <div class="row gx-5">

            <div class="col-lg-6">
                {$modules[$extension->module] = $extension}
                {$module_type = 'extension'}
                {include file='parts/module_settings_part.tpl'}
            </div>

            <div class="col-lg-6 layer">
                <ul class="property_block">
                    <li>
                        <div class="col-form-label">Описание</div>
                        <div class="col-form-label">{$extension->description}</div>
                    </li>
                </ul>
            </div>

            <div class="col-12 btn_row">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </div>
    </form>

{/block}