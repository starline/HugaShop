{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{$meta_title='Список рассылки'}

{block name=content}

	<div class="two_columns_list">
		<div class="header_top">
			<h1>
				{if $mail_template_count}
					{$mail_template_count} {$mail_template_count|plural:'сообщение':'сообщений':'сообщения'}
				{else}
					Еще нет шаблонов
				{/if}
			</h1>
			<a class="add" href="{'MailTemplateNewAdmin'|link}">Новый Шаблон</a>
		</div>


		<div class="navbar-expand-lg" id="right_menu">

			<div class="popup_menu_btn navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#filter_menu_block">
				<span class="material-icons">menu</span>
				<span class="popup_btn_text">Фильтр</span>
			</div>

			<div class="offcanvas offcanvas-start" id="filter_menu_block" tabindex="-1" aria-labelledby="offcanvasLabel">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="offcanvasLabel"></h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>

				<div class="offcanvas-body">
					<ul class="menu_list">
						<li class="{if $type|empty}selected{/if}">
							<a href="{url clear=true}">Показать все</a>
						</li>
						<li class="{if $type == 'sms'}selected{/if}">
							<a href="{url type='sms' clear=true}">SMS</a>
						</li>
						<li class="{if $type == 'email'}selected{/if}">
							<a href="{url type='email' clear=true}">Email</a>
						</li>
					</ul>
				</div>
			</div>
		</div>


		<div id="main_list">
			{if $mail_template_list}

				{include file='parts/pagination.tpl'}

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list">
						{foreach $mail_template_list as $mail_template}
							<div class="list_row {if $mail_template->send}highlight{/if} mail_template"
								item_id="{$mail_template->id}">
								<div class="checkbox">
									<input class="form-check-input" type="checkbox" name="check[]" value="{$mail_template->id}" />
								</div>

								<div class="number">
									<div class="badge text-bg-round">#{$mail_template->id}</div>
								</div>

								<div class="col">
									<a href="{'MailTemplateAdmin'|link:[id => $mail_template->id]}">{$mail_template->name}</a>
								</div>

								<div>
									<div class="badge text-bg-round">
										{$mail_template->type}
									</div>
								</div>

								<div class="icons">
									<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
								</div>
							</div>
						{/foreach}
					</div>

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

				</form>

				{include file='parts/pagination.tpl'}

			{/if}
		</div>
	</div>

{/block}