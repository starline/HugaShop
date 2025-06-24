{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{if $feature->id}
	{$meta_title = $feature->name}
{else}
	{$meta_title = 'Новое свойство'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl'}

	<!-- Основная форма -->
	<form method="post">
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="name_row">
					<input class="form-control form-control-lg" name="name" type="text" value="{$feature->name}" />
					<input name="id" type="hidden" value="{$feature->id}" />
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Использовать в категориях</h2>
				<select class="form-select multiple_categories" multiple name="feature_categories[]">
					{function name=category_select selected_id=$product_category level=0}
						{foreach $categories as $category}
							<option value="{$category->id}" {if in_array($category->id, $feature_categories)}selected{/if}
								category_name="{$category->single_name}">
								{section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name}</option>
							{category_select categories=$category->subcategories selected_id=$selected_id  level=$level+1}
						{/foreach}
					{/function}
					{category_select categories=$categories}
				</select>
			</div>

			<div class="col-lg-6 layer">
				<h2>Настройки свойства</h2>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="in_filter" id="in_filter"
						{if $feature->in_filter}checked{/if} value="1">
					<label class="form-check-label" for="in_filter">Использовать в фильтре</label>
				</div>

				<div class="layer">
					<h2 class="mt-4">Варианты свойства</h2>
					<ul class="list mini features_variants" id="sort">

						{foreach name=variants from=$feature_variants item=variant}
							<li class="list_row">
								<div class="move">
									<div class="move_zone"></div>
								</div>
								<div class="col">
									<input class="form-control" name="feature_variants[]" type="text" value="{$variant}" />
								</div>
								<div class="icons">
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
								</div>
							</li>
						{/foreach}

						<li class="list_row" id="new" style="display:none;">
							<div class="move">
								<div class="move_zone"></div>
							</div>
							<div class="col">
								<input class="form-control" name="feature_variants[]" type="text" value="" />
							</div>
							<div class="icons">
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						</li>
					</ul>

					<div class="btn_row_add">
						<div class="add mt-3">
							<i class="dash_link">Добавить вариант</i>
						</div>
					</div>

					<h2 class="mt-4 layer">Используемые свойства</h2>

					<div>
						{foreach $options as $option}
							<div>
								{$ido = $option->id}
								<a href="/admin/products?{$option->feature_id}={$option->value|urlencode}">{$option->value}</a>
							</div>
						{/foreach}
					</div>
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

				// Добавление варианта
				const new_feature = $('.features_variants #new').clone(true);
				$('.features_variants #new').remove().removeAttr('id');

				$('.add').click(function() {
					new_feature.clone().appendTo('.features_variants').show().find(
						'input[name="feature_variants[]"]').focus();
					return false;
				});

				// Удаление варианта
				$(".features_variants").on('click', '.delete', function() {
					$(this).closest(".list_row").fadeOut(200, function() {$(this).remove();});
					return false;
				});

				$("#sort").sortable({
					items: ".list_row:not(.sort_disabled)",
					cancel: ".sort_disabled",
					handle: ".move_zone",
					axis: 'y',
					tolerance: "pointer",
					opacity: 0.90,
				});
			});
		{/literal}
	</script>

{/block}