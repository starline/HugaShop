{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{if $mail_template->name}
	{$meta_title = $mail_template->name}
{else}
	{$meta_title = 'Новый шаблон'}
{/if}

{block name=content}

	{if $message_error}
		<div class="message message_error">
			<span class="text">{if $message_error == 'code_exists'}Купон с таким кодом уже существует{/if}</span>
		</div>
	{/if}


	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" class="name" type="hidden" value="{$mail_template->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="name_row">
					<input class="form-control form-control-lg" name="name" type="text" value="{$mail_template->name}" />
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Настройки шаблона</h2>
				<ul class="property_block">
					<li>
						<label for="modules">Способы оповещения</label>
						<select class="form-select" name="type" id="modules">
							<option value="">Не установлен</option>
							{foreach $notifier_types as $type => $contact}
								<option value="{$type}" {if $mail_template->type == $type}selected{/if}>{$type}</option>
							{/foreach}
						</select>
					</li>
				</ul>
			</div>

			<div class="col-12 layer">
				<h2>Сообщение</h2>
				{if $mail_template->type|in_array:[sms, telegram]}
					<textarea name="content" class="form-control sms_editor" id="content">{$mail_template->content}</textarea>
				{else}
					<textarea id="content" name="content" class="html_editor editor_large">{$mail_template->content}</textarea>
				{/if}
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>

	{include file='parts/tinymce_init.tpl'}

{/block}