{extends 'wrapper/main.tpl'}

{$meta_title = "Регистрация"}
{$meta_description = "Страница регистрации пользователя"}

{block name=content}
	<div class="login_wrap my-5">

                <h1>Регистрация <a href="{'UserLogin'|linkLang}">Вход →</a></h1>

		{if $error|in_array:['user_exists', 'captcha']}
			<div class="alert alert-danger">
				{if $error == 'user_exists'}Пользователь с таким email уже зарегистрирован{/if}
				{if $error == 'captcha'}Подтвердите что вы не робот{/if}
			</div>
		{/if}

		<form class="form-signin" method="post">
			{getCSRFInput}

			<div class="form-floating my-3">
				<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" type="text" name="name"
					id="name" value="{$name}" maxlength="255" autocomplete="name" placeholder="email" />
				<label for="name">Имя</label>
				<div class="invalid-feedback">Введите имя</div>
			</div>

			<div class="form-floating">
				<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" type="email" name="email"
					id="email" value="{$email}" maxlength="255" autocomplete="email" placeholder="Email" />
				<label for="email">Email</label>
				<div class="invalid-feedback">Введите email</div>
			</div>

			<div class="form-floating">
				<input class="form-control {if password|in_array:$form_invalid}is-invalid{/if}" type="password"
					name="password" id="password" value="" placeholder="Пароль" />
				<label for="password">Пароль</label>
				<div class="invalid-feedback">Введите пароль</div>
			</div>

			<div class="w-100 my-3">
				<div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
			</div>

			<div class="w-100">
				<button class="btn btn-primary">Зарегистрироваться</button>
			</div>
		</form>
	</div>

	<script src="https://www.google.com/recaptcha/api.js" async defer></script>

{/block}