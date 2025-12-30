{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{if $mailing->code}
	{$meta_title = $mailing->code}
{else}
	{$meta_title = 'Новое сообщение'}
{/if}

{block name=content}

	<!-- Основная форма -->
	<form method="post">
		<input class="name" type="hidden" name="id" value="{$mailing->id}" />
		{getCSRFInput}

		<div class="row gx-5">
			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
						<div class="form-check form-switch">
							<input type="hidden" name="send" value="0">
							<input class="form-check-input" name="send" value="1" type="checkbox" role="switch" id="send"
								{if $mailing->send}checked{/if} />
							<label class="form-check-label" for="send">Отправлен</label>
						</div>
						<div class="form-check form-switch">
							<input type="hidden" name="frozen" value="0">
							<input class="form-check-input" name="frozen" value="1" type="checkbox" role="switch"
								id="frozen" {if $mailing->frozen}checked{/if} />
							<label class="form-check-label" for="frozen">Заморожен</label>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-6 layer">
				<h2>Настройки сообщения</h2>
				<ul class="property_block">
					<li>
						<label for="contact" class="col-form-label">Получатель</label>
						<input id="contact" class="form-control" name="contact" type="text" value="{$mailing->contact}" />
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

					<li class="row_sm">
						<label for="sending_date" class="col-form-label">Когда отправить</label>
						<input name="sending_date" id="sending_date" type="text" class="form-control"
							value="{$mailing->sending_date}" />
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				<ul class="property_block">
					{if !$mailing->token|empty}
						<li>
							<label class="col-form-label">Ссылка для отслеживания</label>
							<div class="property_value_text badge text-bg-round copy_field"
								value="{$config->root_url}/m{$mailing->id}/{$mailing->token}">
								{$config->root_url}/m{$mailing->id}/{$mailing->token}
								<div class="copy_hover" data-bs-toggle="tooltip" title="Скопировать">
									<i class="material-icons">content_copy</i>
								</div>
							</div>
						</li>

						<li>
							<label class="col-form-label">Метка для отслеживания</label>
							<div class="property_value_text badge text-bg-round copy_field"
								value="?utm_mid={$mailing->id}&utm_mtoken={$mailing->token}">
								?utm_mid={$mailing->id}&utm_mtoken={$mailing->token}
								<div class="copy_hover" data-bs-toggle="tooltip" title="Скопировать">
									<i class="material-icons">content_copy</i>
								</div>
							</div>
						</li>
					{/if}

					<li>
						<label class="col-form-label">Отправлен</label>
						<div class="property_value_text">
							{if !$mailing->sent_date|empty}
								{$mailing->sent_date|date} в {$mailing->sent_date|time}
							{else}
								не отправлен
							{/if}
						</div>
					</li>

					<li>
						<label class="col-form-label">Переходов</label>
						<div class="property_value_text">{$mailing->count}</div>
					</li>

					{if !$mailing->ip|empty}
						<li>
							<label class="col-form-label">IP</label>
							<div class="property_value_text">{$mailing->ip}</div>
						</li>
					{/if}

					{if $mailing->template_id}
						<li>
							<label class="col-form-label">Шаблон</label>
							<a href="{'MailTemplateAdmin'|link:[id => $mailing->template_id]}">{$mailing->template_id}</a>
						</li>
					{/if}
				</ul>
			</div>

			<div class="col-12 layer">
				<h2>Сообщение</h2>
				<textarea name="message"
					class="form-control {if $mailing->template->type|in_array:[sms, telegram]}sms_editor{else}editor_small{/if}"
					{if $mailing->send}disabled{/if}>{$mailing->message}</textarea>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl" class="btn-light" label="Отправить" extra_attrs="name=action value=send"}
				{include file="parts/button.tpl"}
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