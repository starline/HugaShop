{extends 'wrapper/main.tpl'}

{$meta_title = "Восстановление пароля"}
{$meta_description = "Восстановление пароля"}

{block name=content}
	<div class="login_wrap my-5">

		{if $email_sent}
			<h1>Вам отправлено письмо</h1>
			<p>На <b>{$email}</b> отправлено письмо для восстановления пароля.</p>
			<p>Письмо действительно <b>{$session_lifetime} минуты</b></p>
		{else}
			<h1>Напоминание пароля</h1>

			{if $error|in_array:['user_not_found', 'invalid_code']}
				<div class="alert alert-danger">
					{if $error == 'user_not_found'}Email не найден.{/if}
					{if $error == 'invalid_code'}Срок ссылки для восстановления пароля истек. Начните заново.{/if}
				</div>
			{/if}

			<form method="post">
				{getCSRFInput}

				<div class="my-3">Введите email, который вы указывали при регистрации</div>

				<div class="form-floating my-3">
					<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" type="email" name="email"
						id="email" value="{$email}" maxlength="255" autocomplete="email" placeholder="Email" />
					<label for="email">Email</label>
					<div class="invalid-feedback">Введите Email</div>
				</div>

				<div class="col-12">
					<button class="btn btn-primary">Вспомнить</button>
				</div>
			</form>
		{/if}
	</div>
{/block}