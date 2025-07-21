{extends file='wrapper/main.tpl'}
{include file='finance/parts/menu_part.tpl'}

{if $category->id}
	{$meta_title = "Категория платежей"}
{else}
	{$meta_title = 'Новая категория  платежей'}
{/if}

{block name=content}

	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$category->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12 name_row">
				<input class="form-control form-control-lg" name="name" type="text" value="{$category->name}"
					autocomplete="none" />
			</div>

			<div class="col-lg-6">
				<ul class="property_block">
					<li>
						<label for="type" class="col-form-label">Вид платежа</label>
						<select id="type" class="form-select" name="type">
							<option value='0' {if $category->type == 0}selected{/if}>Расход</option>
							<option value='1' {if $category->type == 1}selected{/if}>Приход</option>
						</select>
					</li>
					<li>
						<label for="comment" class="col-form-label">Заметки</label>
						<textarea class="form-control" id="comment" name="comment">{$category->comment}</textarea>
					</li>
				</ul>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>
		</div>

	</form>

{/block}