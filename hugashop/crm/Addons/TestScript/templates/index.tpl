{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Скрипты'}

{block name=content}

	<div class="header_top">
		<h1>Скрипты</h1>
	</div>


	<div class="">
		<form method="post">
			{getCSRFInput}

			<div class="checkbox_line">

				<div class="form-check">
					<input class="form-check-input" name="action" value="script" type="radio" id="script" />
					<label class="form-check-label" for="script">Запустить скрипт</label>
				</div>

				<div class="form-check">
					<input class="form-check-input" name="action" value="php_check" type="radio" id="php_check" />
					<label class="form-check-label" for="php_check">Проверить PHP</label>
				</div>

				<div class="form-check">
					<input class="form-check-input" name="action" value="cache_clear" type="radio" id="cache_clear" />
					<label class="form-check-label" for="cache_clear" data-bs-toggle="tooltip"
						title="Обновление Composer">Composer UPD & Cache clear</label>
				</div>

				<div class="form-check">
					<input class="form-check-input" name="action" value="assets_clear" type="radio" id="assets_clear" />
					<label class="form-check-label" for="assets_clear" data-bs-toggle="tooltip"
						title="Удаления Public Assets">Assets Delete</label>
				</div>


				<div class="form-check">
					<input class="form-check-input" name="action" value="related_products" type="radio"
						id="related_products" />
					<label class="form-check-label" for="related_products" data-bs-toggle="tooltip"
						title="Выбираем сопутсвующие товары к каждому товару">Сопутсвующие
						товары</label>
				</div>
			</div>


			<div class="col-12 btn_row">
				{include file="parts/button.tpl" label="Выполнить скрипт"}
			</div>
		</form>

		<div id="main_list">
			{if $new_users}
				{foreach $new_users as $user}
					<div>
						<a href="/admin/user/{$user->id}">{$user->name} {$user->phone}</a>
					</div>
				{/foreach}
			{/if}

			{if $php_check}
				<div><b>php version:</b> {$php_check->version}</div>
				<div><b>apc.shm_size:</b> {if $php_check->apc|isset}{$php_check->apc} enabled{else}disabled{/if}</div>
				<div><b>default_charset:</b> {$php_check->default_charset}</div>
				<div><b>short_open_tag:</b> {$php_check->short_open_tag}</div>
				<div><b>display_errors:</b> {$php_check->display_errors}</div>
				<div><b>mbstring.func_overload:</b> {$php_check->func_overload}</div>
			{/if}

			{if $result}
				<div>
					<pre>{$result}</pre>
				</div>
			{/if}
		</div>
	</div>
{/block}