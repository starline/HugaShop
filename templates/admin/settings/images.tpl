{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}

{$meta_title = "Изображения" scope=global}

{block name=content}

	{if $message_error}
		<div class="message message_error">
			<span class="text">{if $message_error == 'permissions'}Установите права на запись для папки {$images_dir}
				{elseif $message_error == 'name_exists'}Файл с таким именем уже существует
				{elseif $message_error == 'theme_locked'}Текущая тема защищена от изменений. Создайте копию темы.
				{else}{$message_error}
				{/if}</span>
		</div>
	{/if}

	<div class="header_top">
		<h1>Изображения темы {$current_theme}</h1>
	</div>

	<form method="post" enctype="multipart/form-data">
		<input type="hidden" name="delete_image" value="">
		{getCSRFInput}

		<div class="row">
			<div class="col-12 layer">
				<ul class="theme_images">
					{foreach item=image from=$images}
						<li name='{$image->name}'>
							<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							<i class="edit material-icons" data-bs-toggle="tooltip" title="Редактировать">edit</i>
							<p class="name">{$image->name|truncate:16:'...'}</p>
							<div class="theme_image">
								<a class="preview" href="{$image->name|asset:$images_url}">
									<img src="{$image->name|asset:$images_url}">
								</a>
							</div>
							<p class="size">
								{if $image->size>1024*1024}
									{($image->size/1024/1024)|round:2} МБ
								{elseif $image->size>1024}
									{($image->size/1024)|round:2} КБ
								{else}
									{$image->size} Байт
								{/if},
								{$image->width}&times;{$image->height} px</p>
						</li>
					{/foreach}
				</ul>
			</div>

			<div class="col-lg-6">
				<span id="upload_image">
					<i class="dash_link">Добавить изображение</i>
				</span>

				<div class="upload_images">
				</div>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>
{/block}


{block name=body_script append}
	<script type="module">
		{literal}
			$(function() {

				// Редактировать
				$("i.edit").click(function() {
					name = $(this).closest('li').attr('name');
					inp1 = $('<input type=hidden name="old_name[]">').val(name);
					inp2 = $('<input type=text name="new_name[]">').val(name);
					$(this).closest('li').find("p.name").html('').append(inp1).append(inp2);
					inp2.focus().select();
					return false;
				});


				// Удалить 
				$("i.delete").click(function() {
					name = $(this).closest('li').attr('name');
					$('input[name=delete_image]').val(name);
					$(this).closest("form").submit();
					return false;
				});

				// Загрузить
				$("#upload_image").click(function() {
					$(".upload_images").append($(
						'<input class="form-control mt-2" type="file" name="upload_images[]">'));
					return false;
				});

				$("form").submit(function() {
					if ($('input[name="delete_image"]').val() != '' && !confirm('Подтвердите удаление'))
						return false;
				});

			});
		{/literal}
	</script>
{/block}