{extends 'wrapper/main.tpl'}

{$meta_title = "{'Восстановление пароля'|trans}"}

{block name=content}
	<div class="login_wrap my-5">

		{if $email_sent}
			<h1>{'Вам отправлено письмо'|trans}</h1>
			<p>{'На'|trans} <b>{$email}</b> {'отправлено письмо для восстановления пароля'|trans}</p>
			<p>{'Письмо действительно'|trans} <b>{$session_lifetime} {'минуты'|trans}</b></p>
		{else}
			<h1>{'Напоминание пароля'|trans}</h1>

			{if $error|in_array:['user_not_found', 'invalid_code']}
				<div class="alert alert-danger">
					{if $error == 'user_not_found'}{'Email не найден'|trans}{/if}
					{if $error == 'invalid_code'}{'Срок ссылки для восстановления пароля истек. Начните заново'|trans}{/if}
				</div>
			{/if}

			<form method="post">
				{getCSRFInput}

				<div class="my-3">{'Введите email, который вы указывали при регистрации'|trans}</div>

				<div class="form-floating my-3">
					<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" type="email" name="email"
						id="email" value="{$email}" maxlength="255" autocomplete="email" placeholder="{'Email'|trans}" />
					<label for="email">{'Email'|trans}</label>
					<div class="invalid-feedback">{'Введите Email'|trans}</div>
				</div>

				<div class="col-12">
					<button class="btn btn-primary">{'Вспомнить'|trans}</button>
				</div>
			</form>
		{/if}
	</div>
{/block}