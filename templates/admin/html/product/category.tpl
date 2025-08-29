{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{if $category->id}
	{$meta_title = $category->name}
{else}
	{$meta_title = 'Новая категория'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl' entity='category'}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check form-switch">
							<input type="hidden" name="visible" value="0">
							<input class="form-check-input" name="visible" value="1" type="checkbox" role="switch"
								id="visible_checkbox" {if $category->visible}checked{/if} />
							<label class="form-check-label" for="visible_checkbox">Активна</label>
						</div>
						<div class="form-check form-switch">
							<input type="hidden" name="main" value="0">
							<input class="form-check-input" name="main" value="1" type="checkbox" role="switch"
								id="main_checkbox" {if $category->main}checked{/if} />
							<label class="form-check-label" for="main_checkbox">Показыват на главной</label>
						</div>
					</div>

					{if $category->url}
						<a class="out_link" target="_self" href="{'Products'|linkLang:[url => $category->url]}">Открыть
							категорию на сайте</a>
					{/if}
				</div>

				<div class="name_row">
					<span class="item_id">#{$category->id}</span>
					<input class="form-control form-control-lg" name="name" type="text" value="{$category->name}"
						autocomplete="off" />
					<input name="id" type="hidden" value="{$category->id}" />
				</div>
			</div>


			<div class="col-lg-6">
				<div class="layer">
					<h2>Позиция в дереве категорий</h2>
					<div class="select">
						<select name="parent_id" class="form-select chosen_select">
							<option value="0">Корневая категория</option>
							{function name=category_select level=0}
								{foreach $cats as $cat}
									{if $category->id != $cat->id}
										<option value='{$cat->id}' data="{$cat->name}"
											{if $category->parent_id == $cat->id}selected{/if}>
											{section name=sp loop=$level} &nbsp; &nbsp; {/section}{$cat->name}</option>
										{category_select cats=$cat->subcategories level=$level+1}
									{/if}
								{/foreach}
							{/function}
							{category_select cats=$categories}
						</select>

						{if $category->id}
							<a class="out_link mt-3" href="{'ProductListAdmin'|link:[category_id => $category->id]}">
								Перейти к товарам категории в админке
							</a>
						{/if}
					</div>
				</div>

				<div class="layer">
					<h2>Параметры страницы</h2>
					<ul class="property_block">
						<li>
							<label class="col-form-label" for="url">Адрес (url)</label>
							<input class="form-control" name="url" id="url" type="text" value="{$category->url}" />
						</li>
						<li>
							<label class="col-form-label" for="meta_title">Заголовок (MetaTitle)</label>
							<div class="worlds_count">
								<input class="form-control" name="meta_title" id="meta_title" type="text" maxlength="60"
									value="{$category->meta_title}" />
								<div class="worlds_counter">
									<span class="worlds_fill"></span>
									<span class="worlds_max"></span>
								</div>
							</div>
						</li>
						<li>
							<label class="col-form-label" for="meta_description">Описание (MetaDescription)
								<div class="emojis">{$settings->emojis}</div>
							</label>
							<div class="worlds_count">
								<textarea class="form-control" name="meta_description" maxlength="160"
									id="meta_description">{$category->meta_description}</textarea>
								<div class="worlds_counter">
									<span class="worlds_fill"></span>
									<span class="worlds_max"></span>
								</div>
							</div>
						</li>
						<li>
							<label class="col-form-label" for="h1">Заголовок (H1)</label>
							<input class="form-control" name="h1" id="h1" type="text" value="{$category->h1}" />
						</li>
					</ul>
				</div>

				<div class="layer">
					<h2>SEO параметры</h2>
					<ul class="property_block">
						<li>
							<label class="col-form-label" for=seo_keywords>Ключевые слова (keywords)</label>
							<textarea class="form-control" name="seo_keywords" id="seo_keywords">{$seo_keywords}</textarea>
						</li>
						<li>
							<label class="col-form-label" for=seo_faqs>Поисковые подсказки (FAQ)</label>
							<textarea class="form-control" name="seo_faqs" id="seo_faqs">{$seo_faqs}</textarea>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-lg-6">

				<!-- Изображение категории -->
				<div id="images" class="layer images">
					<h2>Изображения категории</h2>
					{include file='parts/image_upload_part.tpl' images=$category->images can_edit=true}
				</div>


				<!-- Cинонимы категории -->
				<div class="layer">
					<h2>Синонимы категрии</h2>
					<ul class="list mini_list features_variants sortable_on">
						{foreach $synonyms as $synonym}
							<li class="list_row">
								<div class="move">
									<div class="move_zone"></div>
								</div>
								<div class="col">
									<input class="form-control" name="synonyms[]" type="text" value="{$synonym->name}" />
								</div>
								<div class="icons">
									<i class="delete material-icons" title="Удалить">cancel</i>
								</div>
							</li>
						{/foreach}

						<li id="new" class="list_row" style="display:none;">
							<div class="move">
								<div class="move_zone"></div>
							</div>
							<div class="col">
								<input class="form-control" name="synonyms[]" type="text" value="" />
							</div>
							<div class="icons">
								<i class="delete material-icons" title="Удалить">cancel</i>
							</div>
						</li>
					</ul>

					<div class="btn_row_add">
						<div class="add mt-3">
							<i class="dash_link">Добавить вариант</i>
						</div>
					</div>
				</div>
			</div>


			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>


			<div class="col-12 layer">
				<h2>Краткое описание</h2>
				<textarea id="annotation" name="annotation"
					class="html_editor editor_small">{$category->annotation}</textarea>
			</div>


			<div class="col-12 layer">
				<h2>Описание</h2>
				<textarea id="description" name="description"
					class="html_editor editor_large">{$category->description}</textarea>
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
	<link rel="stylesheet" href="{'js/jquery/chosen/chosen.css'|asset}" />
	<script type="module">
		import '{"js/fancybox/jquery.fancybox.min.js"|asset}';
		import '{"js/jquery/chosen/chosen.jquery.js"|asset}';
		import { autofillMeta, worldsCount } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				worldsCount();
				autofillMeta();

				// Useful select
				$(".chosen_select").chosen();

				// Добавление синонима
				const s_variant = $('.features_variants #new').clone(true).removeAttr('id');
				$('.features_variants #new').removeAttr('id').remove();

				$('.add').click(function() {
					s_variant.clone().appendTo('.features_variants').show()
						.find('input[name="synonyms[]"]').focus();
					return false;
				});

				// Удаление синонима
				$(".features_variants").on('click', '.delete', function() {
					$(this).closest(".list_row").fadeOut(200, function() {
						$(this).remove();
					});
					return false;
				});
			});
		{/literal}
	</script>
{/block}