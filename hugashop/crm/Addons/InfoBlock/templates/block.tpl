{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{if $block->id}
	{$meta_title = $block->name}
{else}
	{$meta_title = 'Новая страница'}
{/if}

{block name=content}

	{include 'parts/translation_btn_part.tpl' entity='info_block'}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$block->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check form-switch">
							<input type="hidden" name="enabled" value="0">
							<input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch"
								id="enabled" {if $block->enabled}checked{/if} />
							<label class="form-check-label" for="enabled">Активна</label>
						</div>
					</div>
				</div>

				<div class="name_row">
					<div class="col">
						<div class="input-group has-validation">
							<span class="input-group-text item_id">#{$block->id}</span>
							<input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}"
								name="name" type="text" value="{$block->name}" autocomplete="off" />
							<div class="invalid-feedback">Введите название</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 layer">
				<h2>Текст страницы</h2>
				<textarea id="body" name="body" class="html_editor editor_large form-control">{$block->body}</textarea>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>
		</div>
	</form>

	{include file='parts/tinymce_init.tpl'}

	<link rel="stylesheet" href="{'js/fancybox/jquery.fancybox.min.css'|asset}" />

	<script type="module">
		import '{"js/fancybox/jquery.fancybox.min.js"|asset}';
	</script>
{/block}