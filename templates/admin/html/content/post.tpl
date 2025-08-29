{extends file='wrapper/main.tpl'}
{include file='content/parts/menu_part.tpl'}

{if $post->id}
	{$meta_title = $post->name}
{else}
	{$meta_title = 'Новая запись в блоге'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl' entity='blog'}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$post->id}" />
		{getCSRFInput}

		<div class="row gx-5">
			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check form-switch">
							<input type="hidden" name="visible" value="0">
							<input class="form-check-input" name="visible" value="1" type="checkbox" role="switch"
								id="active_checkbox" {if $post->visible}checked{/if} />
							<label class="form-check-label" for="active_checkbox">Активна</label>
						</div>
					</div>
					<a class="out_link" target="_self" href="{'Post'|linkLang:[url => $post->url]}">Открыть статью на
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
						<div class="worlds_count">
							<input class="form-control" id="meta_title" maxlength="60" name="meta_title" type="text"
								value="{$post->meta_title}" />
							<div class="worlds_counter">
								<span class="worlds_fill"></span>
								<span class="worlds_max"></span>
							</div>
						</div>
					</li>
					<li>
						<label for="meta_description" class="col-form-label">Описание (MetaDescription)
							</br>{$settings->emojis}</label>
						<div class="worlds_count">
							<textarea class="form-control" id="meta_description" name="meta_description"
								maxlength="160">{$post->meta_description}</textarea>
							<div class="worlds_counter">
								<span class="worlds_fill"></span>
								<span class="worlds_max"></span>
							</div>
						</div>
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
					{include file="parts/button.tpl"}
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Изображения поста</h2>
				{include file='parts/image_upload_part.tpl' images=$post->images can_edit=true}
			</div>

			<div class="col-12 layer">
				<h2>Полное описание</h2>
				<textarea id="body" name="body" class="html_editor editor_large">{$post->body}</textarea>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>
		</div>
	</form>
{/block}


{block name=body_script append}

	{* Подключаем Tiny MCE *}
	{include file='parts/tinymce_init.tpl'}

	<script type="module">
		import '{"js/jquery/datepicker/jquery.ui.datepicker-ru.js"|asset}';
		import { worldsCount, autofillMeta } from '{"js/common.js"|asset}';

		{literal}
			$(function() {
				worldsCount();
				autofillMeta();

				$('input[name="date"]').datepicker({
					regional: 'ru'
				});
			});
		{/literal}
	</script>
{/block}