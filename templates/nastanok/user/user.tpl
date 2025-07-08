{extends 'wrapper/main.tpl'}

{$meta_title = $user->name scope=global}

{block name=content}
	<div class="login_wrap">
		<h1>{$user->name}</h1>

		{if $error|in_array:['email_exists']}
			<div class="alert alert-danger">
				{if $error == 'email_exists'}Такой email уже используется{/if}
			</div>
		{/if}

		{if $success}
			<div class="alert alert-success">
				Данные обновлены
			</div>
		{/if}

		<form method="post">
			{getCSRFInput}

			<div class="form-floating my-3">
				<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" value="{$user->name}"
					name="name" id="name" maxlength="255" type="text" placeholder="Имя" />
				<label for="name">Имя</label>
				<div class="invalid-feedback">Введите Имя</div>
			</div>

			<div class="form-floating my-3">
				<input class="form-control {if email|in_array:$form_invalid}is-invalid{/if}" value="{$user->email}"
					name="email" id="email" maxlength="255" type="email" placeholder="Email" />
				<label for="email">Email</label>
				<div class="invalid-feedback">Введите Email</div>
			</div>

			<a class="my-3" href='#' onclick="$('#password_form').show(); $(this).hide(); return false;">Изменить пароль</a>

			<div class="form-floating my-3" style="display:none;" id="password_form">
				<input class="form-control {if password|in_array:$form_invalid}is-invalid{/if}" id="password" value=""
					name="password" type="password" placeholder="Пароль" />
				<label for="password">Пароль</label>
				<div class="invalid-feedback">Введите Пароль</div>
			</div>

			<div class="w-10 my-3">
				<button class="btn btn-primary">Сохранить</button>
			</div>
		</form>
	</div>


	<div class="user_orders">
		{if $orders}

			<h1>Ваши заказы</h1>

			<div id="orders_history">
				{foreach name=orders item=order from=$orders}
					<div>
						{$order->date|date} <a href="{'Order'|urll:[id => $order->id, order_url => $order->url]}">Заказ
							№{$order->id}</a>
						{if $order->paid == 1}оплачен,{/if}
						{if $order->status == 0}ждет обработки
						{elseif $order->status == 1}в обработке
						{elseif $order->status == 2}выполнен
						{elseif $order->status == 3}отменен
						{/if}
					</div>
				{/foreach}
			</div>
		{/if}
	</div>
{/block}