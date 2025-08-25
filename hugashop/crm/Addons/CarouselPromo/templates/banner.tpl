{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{if $banner->id}
    {$meta_title = $banner->name}
{else}
    {$meta_title = 'Новый баннер'}
{/if}

{block name=content}

    <!-- Основная форма -->
    <form method="post" enctype="multipart/form-data">
        <input name="id" type="hidden" value="{$banner->id}" />
        {getCSRFInput}

        <div class="row gx-5">

            <div class="col-12">
                <div class="over_name">
                    <div class="checkbox_line">
                        <div class="form-check form-switch">
                            <input type="hidden" name="enabled" value="0">
                            <input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch"
                                id="enabled" {if $banner->enabled}checked{/if} />
                            <label class="form-check-label" for="enabled">Активен</label>
                        </div>
                    </div>
                </div>

                <div class="name_row">
                    <span class="col-form-label item_id">#{$banner->id}</span>
                    <input class="form-control form-control-lg" name="name" type="text" value="{$banner->name}"
                        placeholder="Название баннера" />
                </div>
            </div>

            <div class="col-12 layer">
                <h2>Свойства</h2>
                <ul class="property_block">
                    <li>
                        <label for="link" class="col-form-label">Ссылка</label>
                        <input class="form-control" id="link" name="link" type="text" value="{$banner->link}" />
                    </li>
                </ul>
            </div>

            <div class="col-12 layer">
                <h2>Изображение</h2>
                {if !$banner->image|empty}
                    <ul>
                        <li>
                            <i class="delete material-icons" title="Удалить">cancel</i>
                            <a href="{$banner->image->filename|resize:1920:600}" class="zoom" data-fancybox="product_images"
                                data-caption="{$banner->name}">
                                <img src="{$banner->image->filename|resize:1920:600}" />
                            </a>
                        </li>
                    </ul>
                {/if}

                <div class="input-group">
                    <input class="form-control" name="image" type="file">
                    <input class="btn btn-primary" type="submit" value="Загрузить">
                </div>

                <p class="mt-2">максимальный размер файла &mdash; {$config->max_upload_filesize|byte_convert}</p>
                <div class="alert alert-info">Хавает CSV, стоимость контейнера не должна быть ниже минимальной цены</div>

            </div>

        </div>
    </form>
{/block}