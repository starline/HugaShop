{extends 'wrapper/main.tpl'}
{include 'product/parts/menu_part.tpl'}

{$meta_title='Бренды'}

{block name=content}

	{* Заголовок *}
	<div class=header_top>
		<h1>{$meta_title}</h1>
		<a class="add" href="{'BrandNewAdmin'|link}">Добавить бренд</a>
	</div>


	<div id="main_list">

		{if $brands}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list">
					{foreach $brands as $brand}
						<div class="list_row" item_id="{$brand->id}">

							{if 'product_brand_delete'|user_access}
								<div class="checkbox">
									<input class="form-check-input" type="checkbox" name="check[]" value="{$brand->id}" />
								</div>
							{/if}

							<div class="col">
								<a href="{'BrandAdmin'|link:[id => $brand->id]}">{$brand->name}</a>
							</div>

							{if $brand->image->filename}
								<div class="brand_image">
									<img src="{$brand->image->filename|resize:120:60}" />
								</div>
							{/if}

							{if 'product_brand_delete'|user_access}
								<div class="icons">
									<i class="delete material-icons" title="Удалить">cancel</i>
								</div>
							{/if}
						</div>
					{/foreach}
				</div>

				{if 'product_brand_delete'|user_access}
					<div id="action">
						<span id="check_all" class="dash_link">Выбрать все</span>
						<span id="select">
							<select class="form-select" name="action">
								<option value="">Выбрать действие</option>
								<option value="delete">Удалить</option>
							</select>
						</span>
						{include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
					</div>
				{/if}

			</form>
		{else}
			Нет брендов
		{/if}
	</div>

{/block}