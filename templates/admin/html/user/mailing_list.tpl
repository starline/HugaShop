{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{$meta_title='Список рассылки'}

{block name=content}

	<div class="two_columns_list">
		<div class="header_top">
			<h1>
				{if $mailing_count}
					{$mailing_count} {$mailing_count|plural:'сообщение':'сообщений':'сообщения'}
				{else}
					Еще нет сообщений
				{/if}
			</h1>
                        <a class="add" href="{'MailingNewAdmin'|link}">Новый Сообщение</a>
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
			{if $mailing_list}

				{include file='parts/pagination.tpl'}

				<form method="post" class="list_form">
					{getCSRFInput}

					<div class="list">
						{foreach $mailing_list as $mailing}
							{include file='user/parts/mail_item_part.tpl'}
						{/foreach}
					</div>

					<div id="action">
						<span id="check_all" class="dash_link">Выбрать все</span>
						<span id="select">
							<select class="form-select" name="action">
								<option value="">Выбрать действие</option>
								<option value="send">Отправить</option>
								<option value="delete">Удалить</option>
							</select>
						</span>
						<button class="btn btn-primary apply" id="apply_action" type="submit">Применить</button>
					</div>

				</form>

				{include file='parts/pagination.tpl'}

			{/if}
		</div>
	</div>

{/block}
