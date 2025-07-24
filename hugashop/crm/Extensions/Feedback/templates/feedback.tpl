{extends 'wrapper/main.tpl'}

{$meta_title = 'Oбратный отзыв'}

{block name=content}

	<!-- Breadcrumbs -->
	<div id="path">
		<ul itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<a href="{'Main'|linkLang}" itemprop="item">
					<span itemprop="name">{'Главная'|trans}</span>
					<meta itemprop="position" content="1" />
				</a>
			</li>
		</ul>
	</div>

	<h1>Обратная связь</h1>

	{if $message_sent}
		<div class="alert alert-info">
			{$name}, ваше сообщение отправлено.
		</div>
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

			<div class="row g-4">
				<div class="col-lg-4">
					<labe class="form-label" for="name">Имя</label>
						<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" value="{$name}" name="name"
							maxlength="255" type="text" id="name" placeholder="Имя" />
						<div class="invalid-feedback">Введите имя</div>
				</div>

				<div class="col-lg-4">
					<label lass="form-label" for="email">Email</label>
					<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" value="{$email}" name="email"
						maxlength="255" type="text" id="email" placeholder="email" />
					<div class="invalid-feedback">Введите Email</div>
				</div>

				<div class="col-12">
					<textarea class="form-control comment_textarea {if message|in_array:$form_invalid}is-invalid{/if}"
						value="{$message}" name="message" placeholder="Сообщение">{$message}</textarea>
					<div class="invalid-feedback">Введите сообщение</div>
				</div>

				<div class="col-lg-6">
					<div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
				</div>

				<div class="col-12">
					<button class="btn btn-light" type="submit" value="true">Отправить</button>
				</div>
			</div>
		</form>

		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	{/if}
{/block}