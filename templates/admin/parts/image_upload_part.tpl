<div id="images" class="images">
    <ul class="sortable">
        {foreach $images as $image}
            <li class="{if !$image->visible}visible_off{/if}">
                {if $can_edit}
                    <div class="image_icons">
                        <i class="enable material-icons visibility" data-bs-toggle="tooltip" title="Показать"></i>
                        <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
                    </div>
                {/if}
                <a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images">
                    <img class="img-thumbnail" src="{$image->filename|resize:220:220:c}" />
                </a>
                <input type="hidden" name="images[]" value="{$image->id}" />
                <input type="hidden" name="images_visible[{$image->id}]" value="{$image->visible}" />
            </li>
        {/foreach}
    </ul>

    <div class="dropZone">
        <input type="file" name="dropped_images[]" multiple class="dropInput" />
        <div class="dropMessage">Перетащите файлы сюда</div>
    </div>

    {if $can_edit}
        <div class="col-12 btn_row">
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    {/if}

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
</div>