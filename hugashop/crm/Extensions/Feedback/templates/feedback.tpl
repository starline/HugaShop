{extends 'wrapper/main.tpl'}

{$meta_title = 'Oбратный отзыв'}

{block name=content}
	<h1>{$page->name}</h1>

	{$page->body}

	<h2>Обратная связь</h2>

	{if $message_sent}
		{$name}, ваше сообщение отправлено.
	{else}

		{if $error}
			<div class="alert alert-danger">
				{if $error=='captcha'}
					Подтвердите что вы не робот
				{/if}
			</div>
		{/if}

		<form class="form feedback_form" method="post">
			{getCSRFInput}

			<div>
				<label>Имя</label>
				<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" value="{$name}" name="name"
					maxlength="255" type="text" placeholder="Имя" />
				<div class="invalid-feedback">Введите имя</div>
			</div>

			<div>
				<label>Email</label>
				<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" value="{$email}" name="email"
					maxlength="255" type="text" placeholder="email" />
				<div class="invalid-feedback">Введите Email</div>
			</div>

			<div>
				<label>Сообщение</label>
				<textarea class="form-control  {if message|in_array:$form_invalid}is-invalid{/if}" value="{$message}"
					name="message" placeholder="Сообщение">{$message}</textarea>
				<div class="invalid-feedback">Введите сообщение</div>
			</div>

			<div class="col-lg-6">
				<div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
			</div>

			<button class="btn btn-light" type="submit">Отправить</button>

		</form>

		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	{/if}
{/block}