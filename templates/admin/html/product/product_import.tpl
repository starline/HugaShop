{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{$meta_title='Импорт товаров' scope=global}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">
				{if $message_error == 'no_permission'}
					Установите права на запись в папку {$import_files_dir}
				{elseif $message_error == 'convert_error'}
					Не получилось сконвертировать файл в кодировку UTF8
				{elseif $message_error == 'locale_error'}
					На сервере не установлена локаль {$locale}, импорт может работать некорректно
				{elseif $message_error == 'type_error'}
					Выберите тип прайса для импорта цен
				{else}
					{$message_error}
				{/if}
			</span>
		</div>
	{/if}

	{if $message_error != 'no_permission'}

		{if $filename}
			<h1 class="mb-2">Импорт {$filename}</h1>
			<div class="price-type">Тип прайса: {$price_types[$price_type]}</div>

			<div class="progress mt-2" id="progressbar">
				<div class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
					style="width: 0%"></div>
			</div>

			<ul class="list" id="import_result"></ul>
		{else}

			<h1 class="mb-2">Импорт товаров</h1>
			<form method="post" id="product" enctype="multipart/form-data">
				{getCSRFInput}

				<div class="row">
					<div class="price-type col-4">
						<select class="form-select" name="price_type">
							<option value="">Выбрать тип прайса</option>
							{foreach $price_types as $key=>$name}
								<option value="{$key}">{$name}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-6">
						<div class="input-group">
							<input class="form-control import_file" name="file" type="file" value="" />
							<input class="btn btn-primary" type="submit" name="" value="Загрузить" />
						</div>
					</div>
				</div>

				<div class="row my-3">
					<div class="col">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch" name="no_price" value="1" id="no_price"
								{if $no_price == 1}checked{/if}>
							<label class="form-check-label" for="no_price">Не импортировать розничную цену товара</label>
						</div>
					</div>
				</div>

				<p class="mt-2">
					(максимальный размер файла &mdash;
					{if $config->max_upload_filesize>1024*1024}{$config->max_upload_filesize/1024/1024|round:'2'}
					МБ{else}{$config->max_upload_filesize/1024|round:'2'} КБ
					{/if})
				</p>
			</form>

			<div class="block_help">
				<p>Создайте бекап на случай неудачного импорта.</p>
				<p>Сохраните таблицу в формате <b>CSV</b></p>
				<p>
					В первой строке таблицы должны быть указаны названия колонок в таком формате:
				</p>
				<ul>
					<li><span>Товар</span> название товара</li>
					<li><span>Категория</span> категория товара</li>
					<li><span>Бренд</span> бренд товара</li>
					<li><span>Вариант</span> название варианта</li>
					<li><span>Цена</span> цена товара</li>
					<li><span>Оптовая цена</span> оптовая цена товара</li>
					<li><span>Старая цена</span> старая цена товара</li>
					<li><span>Склад</span> количество товара на складе</li>
					<li><span>Вес</span> вес товара в кг</li>
					<li><span>Артикул</span> артикул товара</li>
					<li><span>Видим</span> отображение товара на сайте (0 или 1)</li>
					<li><span>Рекомендуемый</span> является ли товар рекомендуемым (0 или 1)</li>
					<li><span>Аннотация</span> краткое описание товара</li>
					<li><span>Адрес</span> адрес страницы товара</li>
					<li><span>Описание</span> полное описание товара</li>
					<li><span>Изображения</span> имена локальных файлов или url изображений в интернете, через запятую</li>
					<li><span>Заголовок страницы</span> заголовок страницы товара (Meta title)</li>
					<li><span>Ключевые слова</span> ключевые слова (Meta keywords)</li>
					<li><span>Описание страницы</span> описание страницы товара (Meta description)</li>
				</ul>
				<p>Любое другое название колонки трактуется как название свойства товара</p>
				<p>
					<a href="{$config->root_url}/files/imports/example.csv"><b>Скачать пример файла</b></a>
				</p>
			</div>
		{/if}
	{/if}
{/block}


{block name=imported_products}
	{foreach $items as $item}
		<li>
			{if !$item->error}
				<span class="count">{$num--}</span>
				<span title="{$item->status}" class="status {$item->status}"></span>

				<span class="badge text-bg-round mx-2 copy_field" value="{$item->variant->sku}">{$item->variant->sku}
					<div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
						<i class="material-icons">content_copy</i>
					</div>
				</span>

<a target="_blank" href="{'ProductPriceAdmin'|link:[id => $item->product->id]}">{$item->product->name}</a>
				{if $item->variant->name}
					- {$item->variant->name} -
				{/if}

				<span class="new_price ms-2">{$item->variant->price|price_html|raw}
					{if $item->variant->price != $item->prev_variant->price}
						<span class="old_price">{$item->prev_variant->price|price_html|raw}</span>
					{/if}
				</span>

				<span class="wholesale_price ms-2">Опт: {$item->variant->cost_price|price_html|raw}
					{if $item->variant->cost_price != $item->prev_variant->cost_price}
						<span class="old_price">{$item->prev_variant->cost_price|price_html|raw}</span>
					{/if}
				</span>

				{if $item->variant->weight != $item->prev_variant->weight}
					<span class="badge text-bg-round ms-2">Вес: {$item->variant->weight} кг</span>
				{/if}

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

			const price_type = "{$price_type}";
			const no_price = {$no_price ?? 0};

			let in_process = false;
			let file_rows = 0;
			let file_size = 0;
			let num = 0;

			{literal}

				// On document load
				$(function() {
					Piecon.setOptions({fallback: 'force'});
					Piecon.setProgress(0);

					in_process = true;
					do_import();

					// Порционный импорт товаров
					function do_import(from) {
						from = typeof(from) != 'undefined' ? from : 0;
						$.ajax({
							url: "/admin/ajax/import/" + price_type,
							data: {
								from: from,
								no_price: no_price,
								num: num
							},
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
									$("#progressbar").hide('fast');
									in_process = false;
								}
							},
							error: function(xhr, status, errorThrown) {
								alert(errorThrown + '\n' + xhr.responseText);
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
			clear: left;
			padding-top: 10px;
		}

		ul#import_result li {
			margin-bottom: 5px;
		}

		ul#import_result li .count {
			width: 30px;
			display: block;
			float: left;
		}

		ul#import_result li .status {
			padding: 0 16px 0 0;
			background-image: url({'images/exclamation.png'|asset});
			background-repeat: no-repeat;

		}

		ul#import_result li .new_price .price_html {
			font-weight: 600;
			color: var(--lightin-color);
		}

		ul#import_result li .wholesale_price,
		ul#import_result li .wholesale_price .price_html,
		ul#import_result li .old_price .price_html {
			color: #949494;
		}

		ul#import_result li .old_price {
			color: #949494;
			text-decoration: line-through;
			margin-left: 5px;
		}

		ul#import_result li .added {
			background-image: url({'images/accept.png'|asset});
		}

		ul#import_result li .updated {
			background-image: url({'images/update.png'|asset});
		}
	</style>
{/block}