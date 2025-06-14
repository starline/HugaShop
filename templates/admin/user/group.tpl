{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{if $group->id}
	{$meta_title = $group->name}
{else}
	{$meta_title = 'Новая группа'}
{/if}

{block name=content}
	<form method=post enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$group->id}" />
		{getCSRFInput}

		<div class="row gx-5">
			<div class="name_row">
				<div class="col">
					<input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}" name="name"
						type="text" value="{$group->name}" autocomplete="off" placeholder="Название группы" />
					<div class="invalid-feedback">Введите название группы</div>
				</div>
			</div>

			<div class="col-lg-6">
				<ul class="property_block">
					<li class="row_sm">
						<label class="col-form-label" for="discount">Скидка</label>
						<div class="input-group">
							<input class="form-control" name="discount" id="discount" type="text"
								value="{$group->discount}" />
							<span class="input-group-text">%</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>
{/block}