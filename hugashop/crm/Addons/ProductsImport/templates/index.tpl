{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Импорт в склад'}

{block name=content}
    {if $message_error}
        <div class="message message_error">
            <span class="text">
                {if $message_error == 'no_permission'}
                    Установите права на запись в папку {$import_files_dir}
                {elseif $message_error == 'convert_error'}
                    Не получилось сконвертировать файл в кодировку UTF8
                {elseif $message_error == 'place_error'}
                    Не выбран склад для импорта
                {else}
                    {$message_error}
                {/if}
            </span>
        </div>
    {/if}

    {if $message_error != 'no_permission'}
        {if $filename}
            <h1 class="mb-2">Импорт {$filename}</h1>
            <div class="progress mt-2" id="progressbar">
                <div class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                    style="width: 0%"></div>
            </div>
            <ul class="list" id="import_result"></ul>
        {else}
            <h1 class="mb-2">Импорт товаров на склад</h1>
            <form method="post" enctype="multipart/form-data">
                {getCSRFInput}
                <div class="row mb-3">
                    <div class="col-4">
                        <select class="form-select" name="place_id">
                            <option value="">Выберите склад</option>
                            {foreach $places as $place}
                                <option value="{$place->id}">{$place->name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <input class="form-control import_file" name="file" type="file" />
                            <input class="btn btn-primary" type="submit" value="Загрузить" />
                        </div>
                    </div>
                </div>
                <p class="mt-2">
                    максимальный размер файла &mdash; {$config->max_upload_filesize|byte_convert}
                </p>
            </form>
        {/if}
    {/if}
{/block}

{block name=imported_products}
    {foreach $items as $item}
        <li>
            {if !$item->error}
                <span class="count">{$num--}</span>
                <span class="status added"></span>
                <span class="badge text-bg-round mx-2">{$item->product->sku}</span>
                <a target="_blank" href="/admin/product/{$item->product->id}/price">{$item->product->name}</a>
                <span class="ms-2">{$item->amount} шт.</span>
            {else}
                {$item->error}
            {/if}
        </li>
    {/foreach}
{/block}


{block name=body_script append}
    <script type="module">
        import '{"js/piecon/piecon.js"|asset}';

        {if $filename}
            const place_id = {$place_id};
            let in_process = false;
            let file_rows = 0;
            let file_size = 0;
            let num = 0;
            let ajax_url = "{'ExtProductsImportImport'|link}";

            {literal}
                $(function() {
                    Piecon.setOptions({fallback:'force'});
                    Piecon.setProgress(0);
                    in_process = true;
                    do_import();

                    function do_import(from) {
                        from = typeof(from) != 'undefined' ? from : 0;

                        $.ajax({
                            url: ajax_url,
                            data: {from: from, num: num, place_id: place_id},
                            dataType: 'json',
                            success: function(data) {
                                $('ul#import_result').prepend(data.items);
                                file_rows = data.file_rows ?? file_rows;
                                file_size = data.file_size ?? file_size;
                                num = data.num ?? num;
                                Piecon.setProgress(Math.round(100 * data.from / file_size));
                                $('.progress-bar').css('width', Math.round(100 * data.from / file_size) + '%');
                                
                                if (data != false && !data.end) {
                                    do_import(data.from);
                                } else {
                                    Piecon.setProgress(100);
                                    $('#progressbar').hide('fast');
                                    in_process = false;
                                }
                            }
                        });
                    }
                });
            {/literal}
        {/if}
    </script>

    <style>
        ul#import_result {
            display: block;
            padding-top: 10px;
        }

        ul#import_result li {
            margin-bottom: 5px;
        }

        ul#import_result li .count {
            width: 30px;
            display: inline-block;
        }

        ul#import_result li .status {
            padding: 0 16px 0 0;
            background-image:url({'images/accept.png'|asset});
            background-repeat: no-repeat;
        }
    </style>
{/block}