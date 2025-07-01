<script type="module">
    import { initFancybox } from '{"js/common.js"|asset}';

    {literal}
        $(function() {

            // Сортировка изображений
            $(".images ul").sortable({
                tolerance: "pointer",
                opacity: 0.90
            });

            // Удаление изображений
            $(".images").on('click', 'i.delete', function() {
                $(this).closest(".images").find("input[name='delete_image']").val('1'); // for alone photo
                $(this).closest("li").fadeOut(200, function() {
                    $(this).remove();
                });
                return false;
            });

            // Показать/скрыть изображение
            $(".images").on('click', 'i.enable.visibility', function() {
                let li = $(this).closest('li');
                let input = li.find("input[name*='_visible']");
                let state = input.val() == '1' ? '0' : '1';
                input.val(state);
                if (state == '0') {
                    li.addClass('visible_off');
                } else {
                    li.removeClass('visible_off');
                }
                return false;
            });

            // Загрузить изображение с компьютера
            $('.upload_image').click(function() {
                let name = $(this).closest('.images').attr('id');
                $("<input class='form-control upload_image' name=" + name +
                        "[] type=file multiple  accept='image/jpeg,image/png,image/gif,image/webp' />")
                    .appendTo('#' + name + ' .add_image').focus().click();
            });

            // Или с URL
            $('.add_image_url').click(function() {
                let name = $(this).closest('.images').attr('id');
                $("<input class='remote_image' name=" + name + "_urls[] type=text value='http://'>")
                    .appendTo('#' + name + ' .add_image').focus().select();
            });

            // Или перетаскиванием
            if (window.File && window.FileReader && window.FileList) {
                $(document).on('dragenter', function(e) {
                    $(".dropZone").css('border', '1px dotted var(--lightin-color)');
                });

                $(".dropZone").on('dragover', function(e) {
                    $(this).css('border', '1px dotted var(--lightin-color)').css('background-color',
                        'var(--lightin-dim-color)');
                });

                $(".dropZone").on('dragleave', function(e) {
                    $(".dropZone").css('border', '1px solid var(--border-color)').css('background-color',
                        'var(--background-inside-color)'
                    );
                });

                $('.images').off("change.image").on("change.image", '.dropInput', handleFileSelect);
            }


            // Add image
            function handleFileSelect(evt) {

                $(".dropZone").css('border', '').css('background-color', '');

                let files       = evt.target.files; // FileList object
                let dropInput   = $(evt.target).first();
                let name        = $(evt.target).closest('.images').attr('id');

                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, file; file = files[i]; i++) {

                    // Only process image files.
                    if (!file.type.match('image.*')) {
                        continue;
                    }

                    let reader = new FileReader();

                    // Closure to capture the file information.
                    reader.onload = (function(theFile) {
                        return function(e) {

                            // Render thumbnail.
                            $("<li class='wizard'>" +
                                "<div class='image_icons'>" +
                                "<i class='enable material-icons visibility' title='Показать'></i>" +
                                "<i class='delete material-icons' title='Удалить'>cancel</i></div>" +
                                "<a href='" + e.target.result +
                                "' class='zoom' data-fancybox='images_content'>" +
                                "<img class='img-thumbnail img-fluid' onerror='$(this).closest(\"li\").remove();' src='" +
                                e.target.result + "' />" +
                                "<input name=" + name + "_urls[] type='hidden' value='" +
                                theFile.name + "'/><input name=" + name +
                                "_urls_visible[] type='hidden' value='1'/></a></li>").appendTo('#' +
                                name + ' ul');

                            let temp_input = dropInput.clone().show();
                            let block = $('#' + name);

                            block.find('.dropInput').remove();
                            block.find('.dropZone').prepend(temp_input);

                            initFancybox();
                        }
                    })(file);

                    // Read in the image file as a data URL.
                    reader.readAsDataURL(file);
                }
            }
        });
    {/literal}
</script>