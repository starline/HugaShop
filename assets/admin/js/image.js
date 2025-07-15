/**
 * Images upload component
 * Handles image sorting, visibility and uploading
 *
 * @author Andri Huga
 * @version 1.4
 */

export function initImagesUpload() {

    $(".images ul").sortable({
        tolerance: "pointer",
        opacity: 0.90
    });

    $(".images").on('click.delete', ' i.delete', function () {
        $(this).closest(".images").find("input[name='delete_image']").val('1');
        $(this).closest("li").fadeOut(200, function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                const tooltip = bootstrap.Tooltip.getInstance(el);
                if (tooltip) {
                    tooltip.hide();
                }
            });
            $(this).remove();

        });
        return false;
    });

    $(".images").on('click.image', 'i.enable.visibility', function () {
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

        $('.images .dropInput').on("change.image", handleFileSelect);
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
                        "' class='zoom img-thumbnail' data-fancybox='images_content'>" +
                        "<div class='image_preview' style='background-image:url(" + e.target.result + ");'></div>" +
                        "<input name=" + name + "_urls[] type='hidden' value='" +
                        theFile.name + "'/><input name=" + name +
                        "_urls_visible[] type='hidden' value='1'/></a></li>").appendTo('#' +
                            name + ' ul');
                };
            })(file);

            reader.readAsDataURL(file);
        }

        // Add new empty input for next selection
        const newInput = dropInput.clone().val('');
        dropInput.closest('.dropZone').prepend(newInput);
    }
}
