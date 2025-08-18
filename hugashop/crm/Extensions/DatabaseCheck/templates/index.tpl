{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='Проверка базы данных'}

{block name=content}
    <div class="header_top">
        <h1>{$meta_title}</h1>
    </div>

    <div class="mb-3">
        <button class="btn btn-primary" id="checkTables">Проверить таблицы</button>
        <input type="hidden" id="token" value="{$token}">
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Модель</th>
                <th>Таблица</th>
                <th>Статус</th>
                <th>Строк</th>
                <th>Размер</th>
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

    <script>
        const models = [{foreach $models as $m}'{$m.class}',{/foreach}];
        const checkUrl = "{'ExtDatabaseCheckCheck'|link}";
        let index = 0;
        async function checkNext() {
            if (index >= models.length) {
                return;
            }
            const model = models[index];
            const token = document.getElementById('token').value;
            const form = new URLSearchParams();
            form.append('model', model);
            form.append('csrf', token);
            const res = await fetch(checkUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: form.toString()
            });
            const data = await res.json();
            const row = document.querySelector(`tr[data-model="${model}"]`);
            if (row) {
                row.querySelector('.status').textContent = data.status;
                row.querySelector('.rows').textContent = data.rows;
                row.querySelector('.size').textContent = data.size;
            }
            index++;
            checkNext();
        }

        document.getElementById('checkTables').addEventListener('click', () => {
            index = 0;
            document.querySelectorAll('tbody tr').forEach(r => {
                r.querySelector('.status').textContent = '';
                r.querySelector('.rows').textContent = '';
                r.querySelector('.size').textContent = '';
            });
            checkNext();
        });
    </script>
{/block}
