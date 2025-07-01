{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{if $brand->id}
	{$meta_title = $brand->name}
{else}
	{$meta_title = 'Новый бренд'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl' entity='brand'}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$brand->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
                                                <div class="form-check form-switch">
                                                        <input class="form-check-input" name="featured" value="1" type="checkbox" role="switch" id="featured_checkbox"
                                                                {if $brand->featured}checked{/if} />
                                                        <label class="form-check-label" for="featured_checkbox">Избранный</label>
                                                </div>
					</div>
				</div>
				<div class="name_row">
					<div class="col">
						<input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}"
							name="name" type="text" value="{$brand->name}" placeholder="Название бренда" />
						<div class="invalid-feedback">Введите название</div>
					</div>
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Параметры страницы</h2>
				<ul class="property_block">
					<li>
						<label for="url" class="col-form-label">URL</label>
						<input class="form-control" id="url" name="url" type="text" value="{$brand->url}" />
					</li>
					<li>
						<label for="meta_title" class="col-form-label">Title</label>
						<input class="form-control" id="meta_title" name="meta_title" type="text"
							value="{$brand->meta_title}" />
					</li>
					<li>
						<label for="meta_description" class="col-form-label">Описание (MetaDescription)
							<div class="emojis">{$settings->emojis}</div>
						</label>
						<textarea class="form-control" id="meta_description"
							name="meta_description">{$brand->meta_description}</textarea>
					</li>
				</ul>
				<div class="btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>


			<!-- Изображения -->
			<div class="col-lg-6 layer images">
				<h2>Изображение бренда</h2>
				<input class="form-control upload_image" name="image" type="file">
				<input type="hidden" name="delete_image" value="">
				{if !$brand->image|empty}
					<ul>
						<li>
							<i class="delete material-icons" title="Удалить">cancel</i>
							<a href="{$config->root_url}/files/brands/{$brand->image}" class="zoom"
								data-fancybox="product_images" data-caption="{$product->name}">
								<img src="{$config->root_url}/files/brands/{$brand->image}" />
							</a>
						</li>
					</ul>
				{/if}
			</div>

			<div class="col-12 layer">
				<h2>Описание</h2>
				<textarea name="description" class="html_editor editor_large">{$brand->description}</textarea>

				<div class="btn_row">
					<button class="btn btn-primary" type="submit">Сохранить</button>
				</div>
			</div>

		</div>
	</form>
{/block}


{block name=body_script append}
	{* Подключаем Tiny MCE *}
	{include file='parts/tinymce_init.tpl'}
	<link rel="stylesheet" href="{'js/fancybox/jquery.fancybox.min.css'|asset}" />

	<script type="module">
		import '{"js/fancybox/jquery.fancybox.min.js"|asset}';
		import { generate_meta_title, generate_url } from '{"js/common.js"|asset}';

		{literal}

			//On document load 
			$(function() {

				// Удаление изображений
				$(".images span.delete").on('click', function() {
					$("input[name='delete_image']").val('1');
					$(this).closest("ul").fadeOut(200, function() { $(this).remove(); });
					return false;
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