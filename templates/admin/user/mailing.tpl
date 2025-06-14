{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{if $mailing->code}
	{$meta_title = $mailing->code}
{else}
	{$meta_title = 'Новое сообщение'}
{/if}

{block name=content}

	{if $message_error}
		<div class="message message_error">
			<span class="text">{if $message_error == 'code_exists'}Купон с таким кодом уже существует{/if}</span>
		</div>
	{/if}


	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" class="name" type="hidden" value="{$mailing->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check">
							<input class="form-check-input" name="send" value="1" type="checkbox" id="send"
								{if $mailing->send}checked{/if} />
							<label class="form-check-label" for="send">Отправлен</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" name="frozen" value="1" type="checkbox" id="frozen"
								{if $mailing->frozen}checked{/if} />
							<label class="form-check-label" for="frozen">Заморожен</label>
						</div>
					</div>
				</div>
				<div class="name_row">
					<input class="form-control form-control-lg" name="contact" type="text" value="{$mailing->contact}" />
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Настройки сообщения</h2>
				<ul class="property_block">

					{if !$mailing->token|empty}
						<li>
							<label for="contact" class="col-form-label">Ссылка для отслеживания</label>
							<div class="property_value_text copy_field"
								value="{$config->root_url}/m{$mailing->id}/{$mailing->token}">
								{$config->root_url}/m{$mailing->id}/{$mailing->token}
								<div class="copy_hover" data-bs-toggle="tooltip" title="Скопировать">
									<i class="material-icons">content_copy</i>
								</div>
							</div>
						</li>
						<li>
							<label for="contact" class="col-form-label">Метка для отслеживания</label>
							<div class="property_value_text copy_field"
								value="?utm_mid={$mailing->id}&utm_mtoken={$mailing->token}">
								?utm_mid={$mailing->id}&utm_mtoken={$mailing->token}
								<div class="copy_hover" data-bs-toggle="tooltip" title="Скопировать">
									<i class="material-icons">content_copy</i>
								</div>
							</div>
						</li>
					{/if}


					<li class="row_sm">
						<label for="sending_date" class="col-form-label">Когда отправить</label>
						<input name="sending_date" id="sending_date" type="text" class="form-control"
							value="{$mailing->sending_date}" />
					</li>
					<li>
						<label for="modules">Способы оповещения</label>
						<select class="form-select" name="notifier_id" id="modules">
							<option value="">Не установлен</option>
							{foreach $notifiers as $notifier}
								<option value="{$notifier->id}" {if $mailing->notifier_id == $notifier->id}selected{/if}>
									{$notifier->name}</option>
							{/foreach}
						</select>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				<ul class="property_block">
					<li>
						<label for="sent_date" class="col-form-label">Отправлен</label>
						<div class="property_value_text">
							{if !$mailing->sent_date|empty}
								{$mailing->sent_date|date} в {$mailing->sent_date|time}
							{else}
								не отправлен
							{/if}
						</div>
					</li>
					<li>
						<label for="contact" class="col-form-label">Переходов</label>
						<div class="property_value_text">{$mailing->count}</div>
					</li>

					{if !$mailing->ip|empty}
						<li>
							<label for="contact" class="col-form-label">IP</label>
							<div class="property_value_text">{$mailing->ip}</div>
						</li>
					{/if}

					{if $mailing->template_id|empty}
						<li>
							<label for="message" class="col-form-label">Сообщение</label>
							<textarea class="form-control" name="message" id="message">{$mailing->message}</textarea>
						</li>
					{else}
						<li>
							<label class="col-form-label">Шаблон</label>
							<a href="/admin/user/mailing/template/{$mailing->template_id}">{$mailing->template_id}</a>
						</li>
					{/if}
				</ul>
			</div>

			{if !$mailing->template_id|empty}
				<div class="col-12 layer">
					<h2>Сообщение</h2>
					{if $mailing->template->type|in_array:[sms, telegram]}
						<textarea name="content" class="form-control sms_editor" disabled>{$mailing->template->compiled}</textarea>
					{else}
						<textarea name="content" class=" editor_large" disabled>{$mailing->template->compiled}</textarea>
					{/if}

				</div>
			{/if}

			<div class="col-12 btn_row">
				<button class="btn btn-light" type="submit" name="action_send">Отправить</button>
				<button class="btn btn-primary" type="submit" name="action_save">Сохранить</button>
			</div>

		</div>
	</form>

	<script type="module">
		import "{'js/jquery/datepicker/jquery.ui.datepicker-ru.js'|asset}";

		{literal}
			$(function() {
				$('input[name="sending_date"]').datepicker({
					regional: 'ru'
				});
			});
		{/literal}
	</script>

{/block}