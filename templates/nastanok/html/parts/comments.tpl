<!-- Комментарии -->
<div id="comments">

	<h2>Отзывы и Комментарии</h2>

	{if $comments}
		<ul class="comment_list">
			{foreach $comments as $comment}
				<li id="comment_{$comment->id}">
					<div class="comment_header">
						{$comment->name} <i>{$comment->date|date} в {$comment->date|time}</i>
						{if !$comment->approved}<span class="await_approval">ожидает модерации</span>{/if}
					</div>

					<div class="comment_body">
						{$comment->text|strip_tags|nl2br|raw}
					</div>

					<a class="add_answer" data-id="{$comment->id}">ответить</a>
				</li>

				{if isset($comment->answer)}
					{foreach $comment->answer as $answer}
						<li class="answer" id="comment_{$answer->id}">
							<div class="comment_header">
								{$answer->name} <i>{$answer->date|date} в {$answer->date|time}</i>
								{if !$answer->approved}<span class="await_approval">ожидает модерации</span>{/if}
							</div>
							{$answer->text|strip_tags|nl2br|raw}
						</li>
					{/foreach}
				{/if}

			{/foreach}
		</ul>
	{else}
		<p>
			Ваш комментарий будет первым!
		</p>
	{/if}


	<!-- Форма отправления комментария -->
	<form id="comment_form" class="comment_form row g-4 needs-validation" method="post" action="#comments">
		<input type="hidden" id="comment_related_id" name="comment_related_id" value="" />
		{getCSRFInput}

		<div class="h4">{'Написать комментарий'|trans}</div>

		{if $error}
			<div class="col-12">
				<div class="alert alert-danger">
					{if $error=='captcha'}
						Подтвердите что вы не робот
					{/if}
				</div>
			</div>
		{/if}

		<div class="col-lg-4">
			<label class="form-label" for="comment_name">Имя</label>
			<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" type="text" id="comment_name"
				name="comment_name" value="{$comment_name}" placeholder="Укажите ваше имя" autocomplete="name" />
			<div class="invalid-feedback">Введите Имя</div>
		</div>

		<div class="col-lg-4">
			<input class="comment_email {if email|in_array:$form_invalid}is-invalid{/if}" type="text" id="comment_email"
				name="comment_email" value="" placeholder="Укажите email" />
			<div class="invalid-feedback">Введите email</div>
		</div>

		<div class="col-12">
			<textarea class="form-control comment_textarea {if text|in_array:$form_invalid}is-invalid{/if}"
				id="comment_text" name="comment_text" placeholder="Ваш комментарий">{$comment_text}</textarea>
			<div class="invalid-feedback">Введите сообщенние</div>
		</div>

		<div class="col-lg-6">
			<div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
		</div>

		<div class="col-12">
			<button class="btn btn-light" type="submit" name="comment" value="true">Отправить</button>
		</div>
	</form>
</div>


<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
	{literal}
		$('.add_answer').click(function() {
			let id = $(this).data('id');
			$('form.comment_form').find('#comment_related_id').val(id);
			$('#comment_' + id).append($('form.comment_form'));
		});
	{/literal}
</script>