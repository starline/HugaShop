{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{if $page->id}
	{$meta_title = $page->name}
{else}
	{$meta_title = 'Новая страница'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl' entity='seo_page'}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$page->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check form-switch">
							<input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch"
								id="enabled" {if $page->enabled}checked{/if} />
							<label class="form-check-label" for="enabled">Активна</label>
						</div>
					</div>

					{if $page->url}
						<a class="out_link" target="_self" href="{$config->root_url}/{$page->url}">
							Открыть страницу на сайте
						</a>
					{/if}
				</div>

				<div class="name_row">
					<input class="form-control form-control-lg" name="name" type="text" value="{$page->name}" />
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Настройки меню</h2>
				<ul class="property_block">
					<li>
						<label class="col-form-label">Заголовок (Title)</label>
						<input class="form-control" name="meta_title" type="text" value="{$page->meta_title}" />
					</li>
					<li>
						<label class="col-form-label">Описание (MetaDescription)
							<div class="emojis">{$settings->emojis}</div>
						</label>
						<textarea class="form-control" name="meta_description">{$page->meta_description}</textarea>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				<h2>Параметры страницы</h2>
				<ul class="property_block">
					<li>
						<label for="url" class="col-form-label">Адрес</label>
						<div class="input-group">
							<input class="form-control" id="url" name="url" type="text" value="{$page->url}" />
						</div>
					</li>
					<li>
						<label for="h1" class="col-form-label">Заголовок (H1)</label>
						<input class="form-control" id="h1" name="h1" type="text" value="{$page->h1}">
					</li>
				</ul>
			</div>

			<div class="col-12 layer">
				<h2>Текст страницы</h2>
				<textarea id="body" name="body" class="html_editor editor_large form-control">{$page->body}</textarea>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>


	{include file='parts/tinymce_init.tpl'}

	<link rel="stylesheet" href="{'js/fancybox/jquery.fancybox.min.css'|asset}" />

	<script type="module">
		import '{"js/fancybox/jquery.fancybox.min.js"|asset}';
		import { generate_meta_title, generate_url } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Автозаполнение мета-тегов
				let meta_title_touched = true;
				let url_touched = true;

				if ($('input[name="meta_title"]').val() == generate_meta_title() ||
					$('input[name="meta_title"]').val() == '')
					meta_title_touched = false;

				if ($('input[name="url"]').val() == generate_url())
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