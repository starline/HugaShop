{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}
{include 'product/parts/submenu_part.tpl'}

{if $product->id}
	{$meta_title = $product->name}
{else}
	{$meta_title = 'Новый товар'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl' entity='product'}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$product->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line"></div>
					<div class="link_line">
{if $product->category_id}
<a class="out_link" href="{'ProductListAdmin'|link:[category_id => $product->category_id]}">Перейти к товарам
категории в админке</a>
{/if}
{if $product->id}
<a class="out_link" target="_self" href="{'Product'|linkLang:[url => $product->url]}">Открыть товар
на сайте</a>
{/if}
					</div>
				</div>
				<div class="name_row">
					<div class="col">
						<input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}"
							name="name" type="text" value="{$product->name}" autocomplete="off"
							placeholder="Название товара" />
						<div class="invalid-feedback">Введите название товара</div>
					</div>
				</div>
			</div>


			<!-- Категория -->
			<div class="col-lg-6 layer">
				<div class="select" {if !$categories}style="display:none;" {/if}>
					<label class="form-label" for="category_id">Категория</label>
					<select id="category_id" name="category_id" class="form-select chosen_select">
						{function name=category_select level=0}
							{foreach $categories as $category}
								<option value="{$category->id}" {if $category->id == $selected_id}selected{/if}
									category_name="{$category->name}">{section name=sp loop=$level} &nbsp; &nbsp; &nbsp;
										&nbsp;
									{/section}{$category->name}</option>
								{category_select categories=$category->subcategories selected_id=$selected_id  level=$level+1}
							{/foreach}
						{/function}
						{category_select categories=$categories selected_id=$product->category_id}
					</select>
				</div>
			</div>


			<!-- Бренд -->
			<div class="col-lg-6 layer">
				<div class="select" {if !$brands}style="display:none;" {/if}>
					<label class="form-label" for="brand_id">Бренд</label>
					<select class="form-select chosen_select" id="brand_id" name="brand_id">
						<option value="" {if !$product->brand_id}selected{/if} brand_name="">Не указан</option>
						{foreach $brands as $brand}
							<option value="{$brand->id}" {if $product->brand_id == $brand->id}selected{/if}
								brand_name="{$brand->name}">{$brand->name}</option>
						{/foreach}
					</select>
				</div>
			</div>


			<!-- Параметры страницы -->
			<div class="col-lg-6 layer">
				<h2>Параметры страницы (мета-теги)</h2>
				<ul class="property_block">
					<li>
						<label for="url" class="col-form-label">Адрес (url)</label>
						<div class="input-group">
							<span class="input-group-text">tovar-</span>
							<input class="form-control" id="url" name="url" type="text" value="{$product->url}" />
						</div>
					</li>
					<li>
						<label for="meta_title" class="col-form-label">Заголовок (MetaTitle)</label>
						<input class="form-control" id="meta_title" name="meta_title" type="text"
							value="{$product->meta_title}" />
					</li>
					<li>
						<label for="meta_description" class="col-form-label">Описание (MetaDescription)
							<div class="emojis">{$settings->emojis}</div>
						</label>
						<textarea class="form-control" id="meta_description"
							name="meta_description">{$product->meta_description}</textarea>
					</li>
					<li>
						<label for="annotation" class="col-form-label">Краткое описание</label>
						<textarea class="form-control" id="annotation" name="annotation">{$product->annotation}</textarea>
					</li>
				</ul>
			</div>


			<!-- Изображения товара -->
			<div class="col-lg-6 layer">
				<h2>Изображения товара</h2>
				{include file='parts\image_upload_part.tpl' images=$product->images can_edit=true}
			</div>


			<!-- SEO -->
			<div class="col-lg-6 layer">
				<h2>SEO настройки</h2>
				<ul class="property_block">
					<li>
						<label for="seo" class="col-form-label">Ключевые слова (SEO)</label>
						<textarea class="form-control" id="seo" name="seo_keywords">{$seo_keywords|join:"\n"}</textarea>
					</li>
				</ul>
			</div>


			<!-- Характеристики товара -->
			<div class="col-lg-6 layer" {if !$categories}style="display:none;" {/if}>
				<h2>Характеристики товара</h2>

				<ul class="property_block features">
					{foreach $features as $feature}
						<li feature_id="{$feature->id}">
							<label for="feature_{$feature->id}" class="col-form-label">
								<a href="{'FeatureAdmin'|link:[id => $feature->id]}">{$feature->name}</a>
							</label>
							<input class="form-control" id="feature_{$feature->id}" type="text"
								name="options[{$feature->id}][{$options.{$feature->id}->id}]"
								value="{$options.{$feature->id}->value}" />
						</li>
					{/foreach}
				</ul>

				<!-- Новые свойства -->
				<li id="new_feature" feature_id="" style="display:none;">
					<input class="form-control" type="text" name="new_features_names[]">
					<input class="form-control" type="text" name="new_features_values[]" />
				</li>

				<div class="btn_row_add">
					<span class="add">
						<i class="dash_link" id="add_new_feature">Добавить новые характеристики</i>
					</span>
				</div>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>


			<!-- Полное описание -->
			<div class="col-12 layer">
				<h2>Полное описание</h2>
				<textarea id="body" name="body" class="html_editor editor_large">{$product->body}</textarea>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>

		</div>
	</form>
{/block}


{block name=head_css append}
	<link rel="stylesheet" href="{'js/jquery/chosen/chosen.css'|asset}" />
{/block}

{block name=body_script append}

	{* Подключаем Tiny MCE *}
	{include file='parts/tinymce_init.tpl'}

	<script type="module">
		import '{"js/jquery/chosen/chosen.jquery.js"|asset}';

		let lang_code = "{$current_language->code}";
		let product_id = "{$product->id}";

		{literal}
			$(function() {

				// Useful select
				$(".chosen_select").chosen();

				// Изменение набора свойств при изменении категории
				$('select[name="category_id"]:first').change(function() {
					show_category_features($("option:selected", this).val());
				});

				function show_category_features(category_id) {

					$('ul.features li').each(function(index) {
						let feature = $(this).find('input') || $(this).find('select');
						if (!feature.val()) {
							$(this).remove();
						}
					});

					$.ajax({
						url: "/admin/ajax/product/get_feature",
						data: {
							category_id: category_id,
							product_id: $("input[name=id]").val(),
							lang: lang_code,
							csrf: window.csrf
						},
						dataType: 'json',
						success: function(data) {
							const line = $(
								"<li><label class='col-form-label'></label><input class='form-control' name='' type='text'/></li>"
							);

							for (let i = 0; i < data.length; i++) {
								let feature = data[i];
								let new_line = line.clone(true);

								new_line.attr('feature_id', feature.id);
								new_line.find("label.col-form-label").text(feature.name);
								new_line.find("input").attr('name', "options[" + feature.id + "]").
								val(feature.value);

								new_line.appendTo('ul.features').find("input")
									.autocomplete({
										serviceUrl: '/admin/ajax/product/get_option',
										type: 'POST',
										minChars: 0,
										params: {
											feature_id: feature.id,
											lang: lang_code,
											csrf: window.csrf
										},
										noCache: false,
										onSelect: function(suggestion) {
											$(this).attr('name', 'options[' + suggestion.feature_id +
												'][' + suggestion.id + ']');
										}
									});
							}
						}
					});
					return false;
				}


				// Автодополнение свойств
				$('.features input[name*=options]').each(function(index) {
					let $input = $(this);
					let feature_id = $input.closest('li').attr('feature_id');
					$input.autocomplete({
						serviceUrl: '/admin/ajax/product/get_option',
						type: 'POST',
						minChars: 0,
						params: {
							feature_id: feature_id,
							lang: lang_code,
							csrf: window.csrf
						},
						noCache: false,
						onSelect: function(suggestion) {
							$input.attr('name', 'options[' + suggestion.feature_id + '][' +
								suggestion.id + ']');
						}
					});
				});


				// Автозаполнение названия характеристик
				$('.features').on('focus', 'input[name*=new_features_names]', function(index) {
					let $input = $(this);
					$input.autocomplete({
						serviceUrl: '/admin/ajax/product/get_feature_name',
						type: 'POST',
						minChars: 0,
						params: {
							product_id: product_id,
							lang: lang_code,
							csrf: window.csrf
						},
						noCache: false,
						onSelect: function(suggestion) {
							let $line = $input.closest('li');
							$line.attr('feature_id', suggestion.data);
							$line.find('input[name*=new_features_values]').autocomplete({
								serviceUrl: '/admin/ajax/product/get_option',
								type: 'POST',
								minChars: 0,
								params: {
									feature_id: suggestion.data,
									lang: lang_code,
									csrf: window.csrf
								},
								noCache: false,
								onSelect: function(suggestion) {
									$input.attr('name', 'options[' + suggestion
										.feature_id + '][' + suggestion.id + ']');
								}
							});
						}
					});
				});


				// Добавление нового свойства товара
				const new_feature = $('#new_feature').clone(true).removeAttr('id');
				$('#new_feature').removeAttr('id').remove();

				$('#add_new_feature').click(function() {
					$(new_feature).clone(true).appendTo('.features').show()
						.find("input[name*=new_features_names]").focus();
					return false;
				});
			});
		{/literal}
	</script>
{/block}