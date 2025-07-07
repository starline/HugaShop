{extends 'wrapper/main.tpl'}
{include 'warehouse/parts/menu_part.tpl'}

{if $provider->id}
	{$meta_title = $provider->name}
{else}
	{$meta_title = 'Новый поставщик'}
{/if}

{block name=content}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$provider->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check form-switch">
							<input class="form-check-input" name="no_restore_price" value="1" type="checkbox" role="switch"
								id="no_restore_price_checkbox" {if $provider->no_restore_price}checked{/if} />
							<label class="form-check-label" for="no_restore_price_checkbox">Не обнулять склад</label>
						</div>
					</div>
				</div>

				<div class="name_row">
					<input class="form-control form-control-lg" name=name type="text" value="{$provider->name}" />
				</div>
			</div>

			<div class="col-12 layer">
				<h2>Описание</h2>
				<textarea id="description" name="description"
					class="html_editor editor_large">{$provider->description}</textarea>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>

	{include file='parts/tinymce_init.tpl'}

{/block}