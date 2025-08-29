{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Карусель'}

{block name=content}

    <!-- Заголовок -->
    <div class="header_top">
        <h1>{$meta_title}</h1>
        <a class="add" href="{'AddonCarouselPromoBannerNew'|link}">Добавить баннер</a>
    </div>

    <div id="main_list">

        {if $banners}
            <form method="post" class="list_form">
                {getCSRFInput}

                <div class="list sortable_on">
                    {foreach $banners as $banner}
                        <div class="{if !$banner->enabled}enabled_off{/if} list_row">

                            <div class="move">
                                <div class="move_zone"></div>
                                <input type="hidden" name="positions[{$banner->id}]" value="{$banner->position}">
                            </div>

                            <div class="checkbox">
                                <input class="form-check-input" type="checkbox" name="check[]" value="{$banner->id}" />
                            </div>

                            <div class="row col">
                                <div class="col_image">
                                    <img src="{if $banner->image->filename}{$banner->image->filename|resize:120:120:c}{/if}"
                                        alt="{$banner->name}" class="img-fluid me-2" style="max-width:120px;" />
                                </div>
                                <div class="col">
                                    <a href="{'AddonCarouselPromoBanner'|link:[id => $banner->id]}">{$banner->name}</a>
                                </div>
                            </div>

                            <div class="icons">
                                <i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Активна"></i>
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
                            <option value="enable">Сделать видимыми</option>
                            <option value="disable">Сделать невидимыми</option>
                            <option value="delete">Удалить</option>
                        </select>
                    </span>
                    {include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
                </div>
            </form>

        {else}
            Нет баннеров
        {/if}
    </div>
{/block}


{block name=body_script append}
    <script type="module">
        import { ajaxEntityUpdateIcon } from '{"js/common.js"|asset}';

        {literal}
            $(function() {

                // Показать
                $("i.enable").click(function() {
                    ajaxEntityUpdateIcon($(this), 'CarouselPromoBanner', 'enabled', csrf);
                    return false;
                });

            });
        {/literal}
    </script>
{/block}