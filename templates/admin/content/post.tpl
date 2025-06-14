{extends file='wrapper/main.tpl'}
{include file='content/parts/menu_part.tpl'}

{if $post->id}
	{$meta_title = $post->name}
{else}
	{$meta_title = 'Новая запись в блоге'}
{/if}

{block name=content}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$post->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class=checkbox_line>
						<div class="form-check">
							<input class="form-check-input" name="visible" value="1" type="checkbox" id="active_checkbox"
								{if $post->visible}checked{/if} />
							<label class="form-check-label" for="active_checkbox">Активна</label>
						</div>
					</div>
					<a class="out_link" target="_self" href="{$config->root_url}/blog/{$post->url}">Открыть статью на
						сайте</a>
				</div>

				<div class="name_row">
					<span class="item_id">H1</span>
					<input class="form-control form-control-lg" name=name type="text" value="{$post->name}"
						autocomplete="off" />
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Параметры страницы</h2>
				<ul class="property_block">
					<li class="row_sm">
						<label for="date" class="col-form-label">Дата</label>
						<input type="text" name="date" id="date" class="form-control" value="{$post->date|date}" />
					</li>
					<li>
						<label for="url" class="col-form-label">Адрес (url)</label>
						<div class="input-group">
							<span class="input-group-text">blog/</span>
							<input class="form-control" id="url" name="url" type="text" value="{$post->url}" />
						</div>
					</li>
					<li>
						<label for="meta_title" class="col-form-label">Заголовок (MetaTitle)</label>
						<input class="form-control" id="meta_title" name="meta_title" type="text"
							value="{$post->meta_title}" />
					</li>
					<li>
						<label for=meta_description class="col-form-label">Описание (MetaDescription)
							</br>{$settings->emojis}</label>
						<textarea class="form-control" id=meta_description
							name="meta_description">{$post->meta_description}</textarea>
					</li>
					<li>
						<label for="seo_keywords" class="col-form-label">Ключевые слова (SEO)</label>
						<textarea class="form-control" id="seo_keywords"
							name="seo_keywords">{$seo_keywords|join:"\n"}</textarea>
					</li>
				</ul>

				<ul class="property_block layer">
					<li>
						<label for="annotation" class="col-form-label">Краткое описание</label>
						<textarea class="form-control" id="annotation" name="annotation">{$post->annotation}</textarea>
					</li>
				</ul>

				<div class="col-12 btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>

			<div id="images" class="col-lg-6 layer images">
				<h2>Изображения поста</h2>
				<ul>
					{foreach $post->images as $image}
						<li>
							<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>

							<a href="{$image->filename|resize:1080:1080:w}" class="zoom" data-fancybox="images">
								<img loading="lazy" src="{$image->filename|resize:220:220}" />
							</a>
							<input type="hidden" name="images[]" value="{$image->id}" />
						</li>
					{/foreach}
				</ul>

				<div class="dropZone">
					<input type="file" name="dropped_images[]" multiple class="dropInput" />
					<div class="dropMessage">Перетащите файлы сюда</div>
				</div>

				<div class="add_image"></div>
				<span class="upload_image">
					<i class="dash_link" id="upload_image">Добавить изображение</i>
				</span>
			</div>

			<div class="col-12 layer">
				<h2>Полное описание</h2>
				<textarea name="body" class="html_editor editor_large">{$post->body}</textarea>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>
{/block}


{block name=body_script append}

	{* Подключаем Tiny MCE *}
	{include file='parts/tinymce_init.tpl'}
	{include file='parts/images_upload_init.tpl'}

	<link rel="stylesheet" href="{'js/fancybox/jquery.fancybox.min.css'|asset}" />

	<script type="module">
		import '{"js/jquery/datepicker/jquery.ui.datepicker-ru.js"|asset}';
		import '{"js/fancybox/jquery.fancybox.min.js"|asset}';
		import { generate_meta_title, generate_url } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				$('input[name="date"]').datepicker({
					regional: 'ru'
				});

				// Автозаполнение мета-тегов
				let meta_title_touched = true;
				let url_touched = true;

				if ($('input[name="meta_title"]').val() == generate_meta_title() ||
					$('input[name="meta_title"]').val() == '')
					meta_title_touched = false;

				if ($('input[name="url"]').val() == generate_url() || $('input[name="url"]').val() == '')
					url_touched = false;

				$('input[name="meta_title"]').change(function() { meta_title_touched = true; });
				$('input[name="url"]').change(function() { url_touched = true; });
				$('input[name="name"]').keyup(function() { set_meta(); });

				function set_meta() {
					if (!meta_title_touched)
						$('input[name="meta_title"]').val(generate_meta_title());
					if (!url_touched)
						$('input[name="url"]').val(generate_url());
				}
			});
		{/literal}
	</script>
{/block}