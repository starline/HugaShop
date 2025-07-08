{extends 'wrapper/main.tpl'}

{$meta_title = "Вход"}
{$meta_description = "Страница входа пользователя"}

{block name=content}
	<div class="login_wrap my-5">

		<h1>Вход <a href="/user/register">Регистрация →</a></h1>

		{if $error|in_array:['login_incorrect', 'user_disabled']}
			<div class="alert alert-danger">
				{if $error == 'login_incorrect'}Неверный Email или Пароль.{/if}
				{if $error == 'user_disabled'}Ваш аккаунт еще не активирован.{/if}
			</div>
		{/if}

		<form class="form-signin" method="post" action="/user/login">
			{getCSRFInput}

			<div class="form-floating">
				<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" type="email" id="email"
					name="email" value="{$email}" maxlength="255" autocomplete="email" placeholder="email" />
				<label for="email">Email</label>
				<div class="invalid-feedback">Введите Email</div>
			</div>

			<div class="form-floating">
				<input class="form-control {if password|in_array:$form_invalid}is-invalid{/if}" type="password"
					id="password" name="password" value="" placeholder="Пароль" />
				<label for="password">Пароль</label>
				<div class="invalid-feedback">Введите Пароль</div>
			</div>

			<div class="form-check text-start my-3">
				<input class="form-check-input" type="checkbox" name="remember" value="1" id="remember" checked>
				<label class="form-check-label" for="remember">
					Запомнить меня на этом компьютере
				</label>
			</div>

			<div class="row align-items-center">
				<div class="col-6">
					<button class="btn btn-primary">Войти</button>
				</div>
				<div class="col-6 text-end">
					<a href="/user/password-remind">Забыл пароль?</a>
				</div>
			</div>

		</form>
	</div>

{/block}