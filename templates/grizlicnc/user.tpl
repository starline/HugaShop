{extends 'wrapper/main.tpl'}

{$meta_title = 'Личные данные'}

{block name=content}
	<div class="profile row">
		<div class="col-lg-3" id="catalog_menu">
			<ul>
				<li class="category_main">
					<a class="{if $route|in_array:[UserOrderList]}selected{/if}" href="{'UserOrderList'|linkLang}">Заказы</a>
				</li>
				<li class="category_main">
					<a class="{if $route|in_array:[User]}selected{/if}" href="{'User'|linkLang}">Личные данные</a>
				</li>
			</ul>
		</div>

		<div class="col-lg-9">
			<div class="login_wrap">
				<h1>Личные данные</h1>

				{if $error|in_array:['email_exists']}
					<div class="alert alert-danger">
						{if $error == 'email_exists'}{'Такой email уже используется'|trans}{/if}
					</div>
				{/if}

				{if $success}
					<div class="alert alert-success">
						{'Данные обновлены'|trans}
					</div>
				{/if}

				<form method="post">
					{getCSRFInput}

					<div class="form-floating my-3">
						<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" value="{$user->name}"
							name="name" id="name" maxlength="255" type="text" placeholder="{'Имя'|trans}" />
						<label for="name">{'Имя'|trans}</label>
						<div class="invalid-feedback">{'Введите Имя'|trans}</div>
					</div>

					<div class="form-floating my-3">
						<input class="form-control  {if email|in_array:$form_invalid}is-invalid{/if}" value="{$user->email}"
							name="email" id="email" maxlength="255" type="email" placeholder="{'Email'|trans}" />
						<label for="email">{'Email'|trans}</label>
						<div class="invalid-feedback">{'Введите Email'|trans}</div>
					</div>

					<a class="my-3" href='#'
						onclick="$('#password_form').show(); $(this).hide(); return false;">{'Изменить пароль'|trans}</a>

					<div class="form-floating my-3" style="display:none;" id="password_form">
						<input class="form-control {if password|in_array:$form_invalid}is-invalid{/if}" id="password"
							value="" name="password" type="password" placeholder="Пароль" />
						<label for="password">{'Пароль'|trans}</label>
						<div class="invalid-feedback">{'Введите Пароль'|trans}</div>
					</div>

					<div class="w-10 my-3">
						<button class="btn btn-primary">{'Сохранить'|trans}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/block}