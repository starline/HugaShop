{extends file='wrapper/main.tpl'}
{include file='addon/parts/menu_part.tpl'}

{$meta_title = $addon->name}

{block name=content}

    <!-- Основная форма -->
    <form method="post" enctype="multipart/form-data">
        <input name="module" type="hidden" value="{$addon->module}" />
        {getCSRFInput}

        <div class="row gx-5">
            <div class="col-lg-6">
                {include file='parts/module_settings_part.tpl' module_type='addon' modules=$addons}
            </div>

            <div class="col-lg-6 layer">
                <ul class="property_block">
                    <li>
                        <div class="col-form-label">Описание</div>
                        <div class="col-form-label">{$addon->description}</div>
                    </li>
                </ul>
            </div>

            <div class="col-12 btn_row">
                {include file="parts/button.tpl"}
            </div>
        </div>
    </form>
{/block}