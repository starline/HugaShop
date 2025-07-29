{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}

{if $current_theme->name}
	{$meta_title = "Тема {$current_theme->name}" scope=global}
{/if}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">{if $message_error == 'permissions'}Установите права на запись для папки {$themes_dir}
				{elseif $message_error == 'name_exists'}Тема с таким именем уже существует
				{else}{$message_error}
				{/if}</span>
		</div>
	{/if}

	<div class="header_top">
		<h1 class="{if $current_theme->locked}locked{/if}">Текущая тема &mdash; {$current_theme->name}</h1>
		<a class="add" href="#">Создать копию темы {$settings->theme}</a>
	</div>

	<form method="post" enctype="multipart/form-data">
		{getCSRFInput}
		<input type=hidden name="action">
		<input type=hidden name="theme">

		<div class="row gx-5">
			<div class="col-12 layer">
				<ul class="themes">
					{foreach $themes as $t}
						<li theme="{$t->name}">
							<div class="head_wrap">
								{if $current_theme->name == $t->name}
									<img class="tick" src="{'images/tick.png'|asset}">
								{/if}

								{if $t->locked}
									<img class="tick" src="{'images/lock_small.png'|asset}">
								{/if}

								{if $current_theme->name != $t->name && !$t->locked}
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
									<i class="edit material-icons" data-bs-toggle="tooltip" title="Изменить название">edit</i>
								{elseif !$t->locked}
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
									<i class="edit material-icons" data-bs-toggle="tooltip" title="Изменить название">edit</i>
								{/if}

								{if $current_theme->name == $t->name}
									<p class="name">{$t->name|truncate:16:'...'}</p>
								{else}
									<p class="name">
										<a href='#' class='set_main_theme'>{$t->name|truncate:16:'...'}</a>
									</p>
								{/if}
							</div>

							<img class="preview" src='{"images/preview.png"|asset:"{$t->name}/assets"}' />
						</li>
					{/foreach}
				</ul>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>
		</div>
	</form>
{/block}


{block name=body_script append}
	<script type="module">
		{literal}

			$(function() {

				// Выбрать тему
				$('.set_main_theme').click(function() {
					$("form input[name=action]").val('set_main_theme');
					$("form input[name=theme]").val($(this).closest('li').attr('theme'));
					$("form").submit();
				});

				// Клонировать текущую тему
				$('.header_top .add').click(function() {
					$("form input[name=action]").val('clone_theme');
					$("form").submit();
				});

				// Редактировать название
				$("i.edit").click(function() {
					let name = $(this).closest('li').attr('theme');
					let inp1 = $('<input type=hidden name="old_name[]">').val(name);
					let inp2 = $('<input type=text name="new_name[]">').val(name);
					$(this).closest('li').find("p.name").html('').append(inp1).append(inp2);
					inp2.focus().select();
					return false;
				});

				// Удалить тему
				$('i.delete').click(function() {
					$("form input[name=action]").val('delete_theme');
					$("form input[name=theme]").val($(this).closest('li').attr('theme'));
					$("form").submit();
				});

				$("form").submit(function() {
					if ($("form input[name=action]").val() == 'delete_theme' && !confirm('Подтвердите удаление'))
						return false;
				});

			});
		{/literal}
	</script>
{/block}