{extends 'wrapper/main.tpl'}
{include 'warehouse/parts/menu_part.tpl'}

{if $place->id}
    {$meta_title = $place->name}
{else}
    {$meta_title = 'Новый склад'}
{/if}

{block name=content}

    <!-- Основная форма -->
    <form method="post" enctype="multipart/form-data">
        <input name="id" type="hidden" value="{$place->id}" />
        {getCSRFInput}

        <div class="row gx-5">

            <div class="col-12">
                <div class="over_name">
                    <div class="checkbox_line">
                        <div class="form-check form-switch">
                            <input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch" id="enabled_checkbox"
                                {if $place->enabled}checked{/if} />
                            <label class="form-check-label" for="enabled_checkbox">Активный</label>
                        </div>
                    </div>
                </div>

                <div class="name_row">
                    <input class="form-control form-control-lg" name="name" type="text" value="{$place->name}" />
                </div>
            </div>

            <div class="col-lg-6">
                <ul class="property_block">
                    <li>
                        <label for="comment" class="col-form-label">Заметки</label>
                        <textarea class="form-control" name="comment" id="comment">{$place->comment}</textarea>
                    </li>
                </ul>

            </div>

            <div class="col-12 btn_row">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </div>
    </form>

{/block}