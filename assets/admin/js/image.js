/**
 * Images upload component
 * Handles image sorting, visibility and uploading
 *
 * @author Andri Huga
 * @version 1.1
 */

export function initImagesUpload() {
    $(".images ul").sortable({
        tolerance: "pointer",
        opacity: 0.90
    });

    $(".images i.delete").off('click.delete').on('click.delete', function () {
        $(this).closest(".images").find("input[name='delete_image']").val('1');
        $(this).closest("li").fadeOut(200, function () {
            $(this).remove();
        });
        return false;
    });

    $(".images i.enable.visibility").off('click.image').on('click.image', function () {
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


    $('.upload_image').off('click.upload').on('click.upload', function () {
        let block = $(this).closest('.images');
        let name = block.attr('id');
        $("<input class='form-control upload_image dropInput' name='dropped_" + name +
            "[]' type='file' multiple accept='image/jpeg,image/png,image/gif,image/webp'>")
            .appendTo('#' + name + ' .add_image').click();
    });


    $('.add_image_url').off('click.add_image').on('click.add_image', function () {
        let name = $(this).closest('.images').attr('id');
        $("<input class='remote_image' name=" + name + "_urls[] type=text value='http://'>")
            .appendTo('#' + name + ' .add_image').focus().select();
    });


    if (window.File && window.FileReader && window.FileList) {
        $(document).on('dragenter', function () {
            $(".dropZone").css('border', '1px dotted var(--lightin-color)');
        });

        $(".dropZone").on('dragover', function () {
            $(this).css('border', '1px dotted var(--lightin-color)').css('background-color',
                'var(--lightin-dim-color)');
        });

        $(".dropZone").on('dragleave', function () {
            $(".dropZone").css('border', '1px solid var(--border-color)').css('background-color',
                'var(--background-inside-color)'
            );
        });

        $('.images').off("change.image").on("change.image", '.dropInput', handleFileSelect);
    }


    function handleFileSelect(evt) {
        $(".dropZone").css('border', '').css('background-color', '');

        let files = evt.target.files;
        let dropInput = $(evt.target);
        let name = dropInput.closest('.images').attr('id');

        for (let i = 0, file; file = files[i]; i++) {
            if (!file.type.match('image.*')) {
                continue;
            }

            let reader = new FileReader();

            reader.onload = (function (theFile) {
                return function (e) {
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
                };
            })(file);

            reader.readAsDataURL(file);
        }

        // Hide current input but keep it for submitting files
        dropInput.hide();

        // Add new empty input for next selection
        const newInput = dropInput.clone().val('');
        dropInput.closest('.dropZone').prepend(newInput);
    }
}
