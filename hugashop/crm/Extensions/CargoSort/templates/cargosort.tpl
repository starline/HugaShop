
{include file='parts/menu_import_part.tpl'}

{$meta_title='Заполнение контейнеров' scope=parent}


<script src="{$config->root_url}/goodgin/design/js/piecon/piecon.js"></script>

<script>
{if $filename}
	
{literal}

	// On document load
	$(function(){
		//do_sort();
	});
  
	function do_sort() {

		$.ajax({
 			url: "ajax/cargosort.php",
			data: {from:from},
			dataType: 'json',
			success: function(data) {
				
				for (var key in data.items) {
					html1 = '<li><span class=count>'+count+'</span> <span title='+data.items[key].status+' class="status '+data.items[key].status+'"></span>';

					if (!data.items[key].error){
						html2 = '<a target=_blank href="index.php?module=ProductAdmin&id='+data.items[key].product.id+'">'+data.items[key].product.name+'</a> '+data.items[key].variant.name;
					} else {
						html2 = data.items[key].error;
					}

					if (data.items[key].synonym){
						html2 += ' ' + data.items[key].synonym;
					}

					html3 = '</li>';

					$('ul#import_result').prepend(html1+html2+html3);
					count++;
				}
				
			},
			error: function(xhr, status, errorThrown) {
				alert(errorThrown+'\n'+xhr.responseText);
			}
		});
	}
{/literal}
{/if}
</script>


{if $message_error}
<div class="message message_error">
	<span class="text">
	{if $message_error == 'no_permission'}Установите права на запись в папку {$import_files_dir}
	{elseif $message_error == 'convert_error'}Не получилось сконвертировать файл в кодировку UTF8
	{elseif $message_error == 'locale_error'}На сервере не установлена локаль {$locale}, импорт может работать некорректно
	{else}{$message_error}{/if}
	</span>
</div>
{/if}

	
{if $filename}
	<h1 class="mb-15 fn">Импорт {$filename|escape}</h1>
	<div class="price-type">
		Размер контейнера: {$box_size} </br>
		Стоимость груза в контейнере: {$box_cost} </br>
		Цена доставки контейнера: {$box_delivery_cost}
	</div>

	<div>
		Общий вес: {$total_size} </br>
		Общая стоимость: {$total_cost} </br>
		Идеальное кол-во контейнеров по весу: {$ideal_box_count_of_size} </br>
		Идеальное кол-во контейнеров по стоимости груза: {$ideal_box_count_of_cost} </br>
		Фактическое кол-во контейнеров: {$box_count} </br>
	</div>

	<p class="price-type">
		<a class="button_green" href="/files/exports/cargosort.csv">Скачать в формате CSV</a>	
	</p>

	<div class="cargosort-result">
		<div>
			<span style="width: 5%; display: inline-block;">#</span>
			<span style="width: 25%; display: inline-block;">Размер</span>
			<span style="width: 20%; display: inline-block;">Название</span>
			<span style="width: 5%; display: inline-block;">Вес</span>
			<span style="width: 10%; display: inline-block;"> Цена</span>
			<span style="width: 10%; display: inline-block;"> Цена доставки</span>
			<span style="width: 10%; display: inline-block;"> Ср. Ц. д.</span>
			<span style="width: 5%; display: inline-block;">Очередь</span>
		</div>
		{foreach $products as $prod}
		<div>
			<span style="width: 5%; display: inline-block;">#{$prod['number_of_box']}</span>
			<span style="width: 25%; display: inline-block;">{$prod['tyre_size']}</span>
			<span style="width: 20%; display: inline-block;"> {$prod['name']}</span>
			<span style="width: 5%; display: inline-block;"> {$prod['size']}</span>
			<span style="width: 10%; display: inline-block;"> {$prod['cost']}</span>
			<span style="width: 10%; display: inline-block;"> {$prod['delivery_product_in_box_cost']}</span>
			<span style="width: 10%; display: inline-block;"> {$prod['middle_delivery_cost']}</span>
			<span style="width: 5%; display: inline-block;">{$prod['firstly']}</span>
		</div>
		{/foreach}
	</div>
{else}
	<h1 class="mb-15">Заполнение контейнеров</h1>
	<form method="post" id="product" enctype="multipart/form-data">
		<div class="block">				
			<input type="hidden" name="session_id" value="{$smarty.session.id}" />
			<input name="file" class="import_file" type="file" value="" />
			<input class="button_green" type="submit" name="" value="Загрузить" />
			<p>
				(максимальный размер файла &mdash; {if $config->max_upload_filesize>1024*1024}{$config->max_upload_filesize/1024/1024|round:'2'} МБ{else}{$config->max_upload_filesize/1024|round:'2'} КБ{/if})
			</p>
		</div>		
		
		<div class="block price-type">Хавает CSV, стоимость контейнера не должна быть ниже минимальной цены</div>

		<div class="block price-type">
			<label for="box_size">Размер контейнера:</label>
			<input name="box_size" id="box_size" type="text" value="" />
		</div>

		<div class="block price-type">
			<label for="box_cost">Стоимость груза в контейнере:</label>
			<input name="box_cost" id="box_cost" type="text" value="" />
		</div>

		<div class="block price-type">
			<label for="box_delivery_cost">Стоимость доставки контейнера:</label>
			<input name="box_delivery_cost" id="box_delivery_cost" type="text" value="" />
		</div>
	</form>
	
	<div class="block block_help">
		<p>
			В первой строке таблицы должны быть указаны названия колонок в таком формате:
			<ul>
				<li><label>Размер</label> размерность </li>
				<li><label>Модель</label> название модели</li>
				<li><label>Вес</label> вес товара</li>
				<li><label>Цена</label> цена товара</li>
				<li><label>Колличество</label> колличество товара</li>
				<li><label>Колличество в первую очередь</label> колличество товара</li>
			</ul>
		</p>
	</div>
{/if}