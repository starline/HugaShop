<div id="images" class="images">
    <ul>
        {foreach $images as $image}
            <li class="{if !$image->visible}visible_off{/if}">
                <div class="image_icons">
                    <i class="enable material-icons visibility" title="Показать"></i>
                    <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
                </div>
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
</div>