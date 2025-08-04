{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{if $feature->id}
	{$meta_title = $feature->name}
{else}
	{$meta_title = 'Новое свойство'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl' entity='feature'}

	<!-- Основная форма -->
	<form method="post">
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check form-switch">
							<input type="hidden" name="in_filter" value="0" />
							<input class="form-check-input" name="in_filter" value="1" type="checkbox" role="switch"
								id="in_filter" {if $feature->in_filter}checked{/if} />
							<label class="form-check-label" for="in_filter">Использовать в фильтре</label>
						</div>
						<div class="form-check form-switch">
							<input type="hidden" name="index" value="0" />
							<input class="form-check-input" name="index" value="1" type="checkbox" role="switch" id="index"
								{if $feature->index}checked{/if} />
							<label class="form-check-label" for="index">Индексировать фильтр</label>
						</div>
					</div>
				</div>
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
				<h2>Параметры страницы</h2>
				<ul class="property_block">
					<li>
						<label for="url" class="col-form-label">URL</label>
						<input class="form-control" id="url" name="url" type="text" value="{$feature->url}" />
					</li>
					<div class="btn_row">
						{include file="parts/button.tpl"}
					</div>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				<h2 class="mt-4">Варианты свойства</h2>
				<ul class="list mini_list options">
					{foreach $options as $option}
						<li class="list_row">
							<input type="hidden" name="options[{$option->position}][id]" value="{$options->id}" />
							<div class="move">
								<div class="move_zone"></div>
							</div>
							<div class="col">
								<div class="row gy-3">
									<div class="col-12 col-md-8">
										<input class="form-control" name="options[{$option->position}][value]" type="text"
											value="{$option->value}" placeholder="Значение опции" />
									</div>
									<div class="col-12 col-md-4">
										<input class="form-control" name="options[{$option->position}][url]" type="text"
											value="{$option->url}" placeholder="url опции" />
									</div>
								</div>
							</div>
							<div class="icons">
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							</div>
						</li>
					{/foreach}

					<li class="list_row" id="new" style="display:none;">
						<input type="hidden" name="options[INDEX][id]" value="">
						<div class="move">
							<div class="move_zone"></div>
						</div>
						<div class="col">
							<div class="row gy-3">
								<div class="col-12 col-md-8">
									<input class="form-control" name="options[INDEX][value]" type="text" value=""
										placeholder="Значение характеристики" />
								</div>
								<div class="col-12 col-md-4">
									<input class="form-control" name="options[INDEX][url]" type="text" value=""
										placeholder="url опции" />
								</div>
							</div>
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
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>
		</div>

	</form>
{/block}


{block name=body_script append}
	<script type="module">
		{literal}
			$(function() {

				// Добавление варианта
				const new_option = $('.options #new').clone(true).removeAttr('id');
				$('.options #new').removeAttr('id').remove();

				$('.add').click(function() {
					new_option.clone().appendTo('.options').show()
						.find('input[name="options[]"]').focus();
					return false;
				});

				// Удаление варианта
				$(".options").on('click', '.delete', function() {
					$(this).closest(".list_row").fadeOut(200, function() {
						$(this).remove();
					});
					return false;
				});
			});
		{/literal}
	</script>

{/block}