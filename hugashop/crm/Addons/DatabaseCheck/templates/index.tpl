{* @version 0.2 *}
{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Проверка базы данных'}

{block name=content}
    <div class="header_top">
        <h1>{$meta_title}</h1>
    </div>

    <div class="mb-3">
        {include 'parts/button.tpl' label="Проверить таблицы" id="check_tables"}
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Модель</th>
                    <th scope="col">Таблица</th>
                    <th scope="col">Статус</th>
                    <th scope="col">Строк</th>
                    <th scope="col">Размер</th>
                </tr>
            </thead>
            <tbody>
                {foreach $models as $model}
                    <tr data-model="{$model.class}">
                        <td>{$model.name}</td>
                        <td>{$model.table}</td>
                        <td class="status"></td>
                        <td class="rows"></td>
                        <td class="size"></td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

    <script type="module">
        const check_url = "{'AddonDatabaseCheckCheck'|link}";

        {literal}
            $(function() {
                const rows = $('tbody tr');
                const models = rows.map((_, r) => $(r).data('model')).get();
                const button = $('#check_tables');

                let index = 0;

                function checkNext() {
                    if (index >= models.length) {
                        button.prop('disabled', false).removeClass('disabled');
                        return;
                    }

                    const model = models[index];

                    $.post(check_url, {
                        model: model,
                        csrf: window.csrf
                    }, function(data) {
                        const row = $('tr[data-model="' + CSS.escape(model) + '"]');
                        if (row.length) {
                            row.find('.rows').text(data.rows);
                            row.find('.size').text(data.size);
                            if (data.status === 'ok') {
                                row.find('.status').text('ok');
                            } else {
                                const icon = $('<i/>', {
                                    class: 'material-icons text-danger',
                                    text: 'info_outline',
                                    'data-bs-toggle': 'popover',
                                    'data-bs-placement': 'top',
                                    'data-bs-trigger': 'hover',
                                    'data-bs-content': data.message
                                });
                                row.find('.status').empty().append(icon);
                                bootstrap.Popover.getOrCreateInstance(icon[0]);
                            }
                        }

                        index++;
                        checkNext();
                    }, 'json');
                }

                button.on('click', function() {
                    button.prop('disabled', true).addClass('disabled');

                    index = 0;
                    $('tbody tr').each(function() {
                        $(this).find('.status, .rows, .size').text('');
                    });

                    checkNext();
                });
            });
        {/literal}
    </script>
{/block}