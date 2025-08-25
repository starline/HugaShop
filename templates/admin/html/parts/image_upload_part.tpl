{assign var="max_images" value=$max_images|default:0}
{if $images && !$images|@is_array}
    {assign var="images" value=[$images]}
{/if}
<div id="images" class="images" {if $max_images}data-max-images="{$max_images}" {/if}>
    <ul class="image_list">
        {foreach $images as $image}
            {if !$image|empty}
                <li class="{if !$image->visible}visible_off{/if}">
                    {if $can_edit}
                        <div class="image_icons">
                            <i class="link material-icons copy_field" value="{$image->filename|resize:1080:1080:w}"
                                data-bs-toggle="tooltip" title="Копировать ссылку">link</i>
                            <i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Показать"></i>
                            <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
                        </div>
                    {/if}
                    <a href="{$image->filename|resize:1080:1080:w}" class="zoom img-thumbnail" data-fancybox="images">
                        <div class="image_preview" style="background-image:url('{$image->filename|resize:120:120:c}');"></div>
                    </a>
                    <input type="hidden" name="images[]" value="{$image->id}" />
                    <input type="hidden" name="images_visible[{$image->id}]" value="{$image->visible}" />
                </li>
            {/if}
        {/foreach}
    </ul>

    {if !$max_images || $images|count < $max_images}
        <div class="dropZone">
            <input type="file" name="dropped_images[]" {if !$max_images || $max_images > 1}multiple{/if}
                class="dropInput" />
            <div class="dropMessage">Перетащите файлы сюда{if $max_images} (максимум {$max_images}){/if}</div>
        </div>
    {/if}

    {if $can_edit}
        <div class="col-12 btn_row">
            {include file="parts/button.tpl"}
        </div>
    {/if}
</div>

<link rel="stylesheet" href="{'js/fancybox/jquery.fancybox.min.css'|asset}" />
<script type="module">
    import '{"js/fancybox/jquery.fancybox.min.js"|asset}';
    import { initImagesUpload } from '{"js/image.js"|asset}';
    import { initFancybox } from '{"js/common.js"|asset}';

    {literal}
        $(function() {

            // Image uploads
            initImagesUpload();

            // Image Zoom init
            initFancybox();
        });
    {/literal}
</script>