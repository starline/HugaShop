{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}


{* Title *}
{$meta_title='Бекап' scope=global}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">
				{if $message_error == 'no_permission'}Установите права на запись в папку {$backup_dir}
				{else}{$message_error}
				{/if}
			</span>
		</div>
	{/if}

	{* Заголовок *}
	<div class="header_top">
		<h1>Бекап</h1>
		{if $message_error != 'no_permission'}
			<form id="hidden" method="post">
				{getCSRFInput}
				<input type="hidden" name="action" value="">
				<input type="hidden" name="name" value="">
			</form>

			<a class="add" href="">Создать бекап</a>
		{/if}
	</div>


	<div id="main_list">
		{if $backups}
			<form method="post" class="list_form">
				{getCSRFInput}

				<div class="list">
					{foreach $backups as $backup}
						<div class="list_row">
							{if $message_error != 'no_permission'}
								<div class="checkbox">
									<input class="form-check-input" type="checkbox" name="check[]" value="{$backup->name}" />
								</div>
							{/if}

							<div class="col row">
								<div class="col-12 col-sm-10">
									<a href="{$config->root_url}/files/backup/{$backup->name}">{$backup->name}</a>
								</div>
								<div class="col-12 col-sm-2 text-end">
									<div class="badge text-bg-round">
										{if $backup->size>1024*1024}
											{($backup->size/1024/1024)|round:2} МБ
										{else}
											{($backup->size/1024)|round:2} КБ
										{/if}
									</div>
								</div>
							</div>

							<div class="icons">
								{if $message_error != 'no_permission'}
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
								{/if}

								<i class="restore material-icons cloud_upload" data-bs-toggle="tooltip"
									title="Восстановить этот бекап"></i>
							</div>
						</div>
					{/foreach}
				</div>

				{if $message_error != 'no_permission'}
					<div id="action">
						<span id="check_all" class="dash_link">Выбрать все</span>
						<span id="select">
							<select class="form-select" name="action">
								<option value="">Выбрать действие</option>
								<option value="delete">Удалить</option>
							</select>
						</span>
						{include file="parts/button.tpl" label="Применить" extra_attrs='id=apply_action'}
					</div>
				{/if}

			</form>
		{/if}
	</div>
{/block}


{block name=body_script append}
	<script type="module">
		{literal}

			$(function() {

				// Восстановить 
				$("i.restore").click(function() {
					let file = $(this).closest(".list_row").find('[name*="check"]').val();
					$('form#hidden input[name="action"]').val('restore');
					$('form#hidden input[name="name"]').val(file);
					$('form#hidden').submit();
					return false;
				});

				// Создать бекап 
				$("a.add").click(function() {
					$('form#hidden input[name="action"]').val('create');
					$('form#hidden').submit();
					return false;
				});

				$("form#hidden").submit(function() {
					if ($('input[name="action"]').val() == 'restore' && !confirm(
							'Текущие данные будут потеряны. Подтвердите восстановление'))
						return false;
				});

			});

		{/literal}
	</script>

{/block}