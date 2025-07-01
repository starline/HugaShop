{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{if $notifier->id}
	{$meta_title = $notifier->name}
{else}
	{$meta_title = 'Новый способ оповещения'}
{/if}

{block name=content}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$notifier->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
                                                <div class="form-check form-switch">
                                                        <input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch" id="enabled"
                                                                {if $notifier->enabled}checked{/if} />
                                                        <label class="form-check-label" for="enabled">Активный</label>
                                                </div>
					</div>
				</div>

				<div class="name_row">
					<input class="form-control form-control-lg" name="name" type="text" value="{$notifier->name}"
						autocomplete="off" />
				</div>
			</div>


			<div class="col-lg-6 layer">
				<h2>Настройки оповещения</h2>
				<ul class="property_block">
					<li>
						<label for="modules">Модуль оповещения</label>
						<select class="form-select" name="module" id="modules">
							<option value="">Не установлен</option>
							{foreach $notifier_modules as $notifier_module}
								<option value="{$notifier_module@key}"
									{if $notifier->module == $notifier_module@key}selected{/if}>
									{$notifier_module->name}</option>
							{/foreach}
						</select>
					</li>

					<li>
						<label class="col-form-label" for="comment">Заметки</label>
						<textarea class="form-control" name="comment" id="comment">{$notifier->comment}</textarea>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				{include file='parts/module_settings_part.tpl' module_type='notifier' modules=$notifier_modules}
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>

	</form>
{/block}