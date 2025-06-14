<!-- Комментарии -->
<div id="comments">

	<h2>{'Отзывы и Комментарии'|trans}</h2>

	{if $comments}
		<ul class="comment_list">
			{foreach $comments as $comment}
				<li id="comment_{$comment->id}">
					<div class="comment_header">
						{$comment->name} <i>{$comment->date|date} в {$comment->date|time}</i>
						{if !$comment->approved}<span class="await_approval">{'ожидает модерации'|trans}</span>{/if}
					</div>

					<div class="comment_body">
						{$comment->text|strip_tags|nl2br|raw}
					</div>

					<span class="link add_answer" data-id="{$comment->id}">Ответить</span>
				</li>

				{if isset($comment->answer)}
					{foreach $comment->answer as $answer}
						<li class="answer" id="comment_{$answer->id}">
							<div class="comment_header">
								{$answer->name} <i>{$answer->date|date} в {$answer->date|time}</i>
								{if !$answer->approved}<span class="await_approval">{'ожидает модерации'|trans}</span>{/if}
							</div>
							{$answer->text|strip_tags|nl2br|raw}
						</li>
					{/foreach}
				{/if}

			{/foreach}
		</ul>
	{else}
		<p>
			{'Ваш комментарий будет первым!'|trans}
		</p>
	{/if}


	<!-- Форма отправления комментария -->
	<form id="comment_form" class="comment_form  needs-validation" method="post" action="#comments">
		<div class="row g-4">
			<input type="hidden" id="comment_related_id" name="comment_related_id" value="" />
			{getCSRFInput}

			<h4>{'Написать комментарий'|trans}</h4>

			{if $error}
				<div class="col-12">
					<div class="alert alert-danger">
						{if $error=='captcha'}
							{'Подтвердите что вы не робот'|trans}
						{/if}
					</div>
				</div>
			{/if}

			<div class="col-lg-4">
				<label class="form-label" for="comment_name">Имя</label>
				<input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" type="text"
					id="comment_name" name="comment_name" value="{$comment_name}" placeholder="{'Укажите имя'|trans}"
					autocomplete="name" />
				<div class="invalid-feedback">{'Укажите имя'|trans}</div>
			</div>

			<div class="col-lg-4">
				<input class="comment_email {if email|in_array:$form_invalid}is-invalid{/if}" type="email"
					id="comment_email" name="comment_email" value="" placeholder="{'Укажите email'|trans}">
				<div class="invalid-feedback">{'Укажите email'|trans}</div>
			</div>

			<div class="col-12">
				<textarea class="form-control comment_textarea {if text|in_array:$form_invalid}is-invalid{/if}"
					id="comment_text" name="comment_text"
					placeholder="{'Ваш комментарий'|trans}">{$comment_text}</textarea>
				<div class="invalid-feedback">{'Укажите сообщение'|trans}</div>
			</div>

			<div class="col-lg-6">
				<div class="g-recaptcha" data-sitekey="{$config->recaptcha->public_key}"></div>
			</div>

			<div class="col-12">
				<button class="btn btn-light" type="submit" name="comment" value="true">{'Отправить'|trans}</button>
			</div>
		</div>
	</form>
</div>


<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script type="module">
	{literal}
		$('.add_answer').click(function() {
			let id = $(this).data('id');
			$('form.comment_form').find('#comment_related_id').val(id);
			$('#comment_' + id).append($('form.comment_form'));
		});
	{/literal}
</script>