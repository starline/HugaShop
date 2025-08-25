/**
 * Images upload component
 * Handles image sorting, visibility and uploading
 *
 * @author Andri Huga
 * @version 1.5
 */

export function initImagesUpload() {

    $(".images ul").sortable({
        tolerance: "pointer",
        opacity: 0.90
    });

    $(".images").each(function () {
        const container = $(this);
        const maxImages = parseInt(container.data('max-images')) || 0;
        if (maxImages && container.find('ul li').length >= maxImages) {
            container.find('.dropZone').hide();
        }
    });

    $(".images").on('click.delete', ' i.delete', function () {
        const imagesBlock = $(this).closest(".images");
        imagesBlock.find("input[name='delete_image']").val('1');
        $(this).closest("li").fadeOut(200, function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                const tooltip = bootstrap.Tooltip.getInstance(el);
                if (tooltip) {
                    tooltip.hide();
                }
            });
            $(this).remove();
            const maxImages = parseInt(imagesBlock.data('max-images')) || 0;
            if (maxImages && imagesBlock.find('ul li').length < maxImages) {
                imagesBlock.find('.dropZone').show();
            }
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
        let container = dropInput.closest('.images');
        let name = container.attr('id');
        let maxImages = parseInt(container.data('max-images')) || 0;
        let currentCount = container.find('ul li').length;

        for (let i = 0, file; file = files[i]; i++) {
            if (!file.type.match('image.*')) {
                continue;
            }

            if (maxImages && currentCount >= maxImages) {
                break;
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
            currentCount++;
        }

        if (maxImages && currentCount >= maxImages) {
            container.find('.dropZone').hide();
        }

        // Add new empty input for next selection
        const newInput = dropInput.clone().val('');
        dropInput.closest('.dropZone').prepend(newInput);
    }
}
