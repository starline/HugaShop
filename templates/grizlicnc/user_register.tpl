{extends 'wrapper/main.tpl'}

{$meta_title = "{'Регистрация'|trans}"}

{block name=content}
	<div class="login_wrap my-5">

		<h1>{'Регистрация'|trans} <a href="{'UserLogin'|linkLang}">{'Вход'|trans} →</a></h1>

		{if $error|in_array:['user_exists', 'captcha']}
			<div class="alert alert-danger">
				{if $error == 'user_exists'}{'Пользователь с таким email уже зарегистрирован'|trans}{/if}
				{if $error == 'captcha'}{'Подтвердите что вы не робот'|trans}{/if}
			</div>
		{/if}

		<form class="form-signin" method="post">
			{getCSRFInput}

			<div class="form-floating my-3">
				<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" type="text" name="name"
					id="name" value="{$name}" maxlength="255" autocomplete="name" placeholder="{'Имя'|trans}" />
				<label for="name">{'Имя'|trans}</label>
				<div class="invalid-feedback">{'Введите имя'|trans}</div>
			</div>

			<div class="form-floating">
				<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" type="email" name="email"
					id="email" value="{$email}" maxlength="255" autocomplete="email" placeholder="{'Email'|trans}" />
				<label for="email">{'Email'|trans}</label>
				<div class="invalid-feedback">{'Введите Email'|trans}</div>
			</div>

			<div class="form-floating">
				<input class="form-control {if password|in_array:$form_invalid}is-invalid{/if}" type="password"
					name="password" id="password" value="" placeholder="{'Пароль'|trans}" />
				<label for="password">{'Пароль'|trans}</label>
				<div class="invalid-feedback">{'Введите пароль'|trans}</div>
			</div>

			<div class="w-100 my-3">
				<div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
			</div>

			<div class="w-100">
				<button class="btn btn-primary">{'Зарегистрироваться'|trans}</button>
			</div>
		</form>
	</div>

	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
{/block}