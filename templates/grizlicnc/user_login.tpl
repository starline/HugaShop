{extends 'wrapper/main.tpl'}

{$meta_title = "{'Вход'|trans}"}

{block name=content}
	<div class="login_wrap my-5">

		<h1>{'Вход'|trans} <a href="{'UserRegister'|linkLang}">{'Регистрация'|trans} →</a></h1>

		{if $error|in_array:['login_incorrect', 'user_disabled']}
			<div class="alert alert-danger">
				{if $error == 'login_incorrect'}{'Неверный Email или Пароль'|trans}{/if}
				{if $error == 'user_disabled'}{'Ваш аккаунт еще не активирован'|trans}{/if}
			</div>
		{/if}

		<form class="form-signin" method="post" action="/user/login">
			{getCSRFInput}

			<div class="form-floating">
				<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" type="email" id="email"
					name="email" value="{$email}" maxlength="255" autocomplete="email" placeholder="{'Email'|trans}" />
				<label for="email">{'Email'|trans}</label>
				<div class="invalid-feedback">{'Введите Email'|trans}</div>
			</div>

			<div class="form-floating">
				<input class="form-control {if password|in_array:$form_invalid}is-invalid{/if}" type="password"
					id="password" name="password" value="" placeholder="{'Пароль'|trans}" />
				<label for="password">{'Пароль'|trans}</label>
				<div class="invalid-feedback">{'Введите Пароль'|trans}</div>
			</div>

			<div class="form-check text-start my-3">
				<input class="form-check-input" type="checkbox" name="remember" value="1" id="remember" checked>
				<label class="form-check-label" for="remember">
					{'Запомнить меня на этом компьютере'|trans}
				</label>
			</div>

			<div class="row align-items-center">
				<div class="col-6">
					<button class="btn btn-primary">{'Войти'|trans}</button>
				</div>
				<div class="col-6 text-end">
					<a href="{'UserPasswordRemind'|linkLang}">{'Забыл пароль?'|trans}</a>
				</div>
			</div>

		</form>
	</div>
{/block}