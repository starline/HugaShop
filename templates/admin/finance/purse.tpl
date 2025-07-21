{extends file='wrapper/main.tpl'}
{include file='finance/parts/menu_part.tpl'}

{if $purse->id}
	{$meta_title = "Кошелек"}
{else}
	{$meta_title = 'Новый кошелек'}
{/if}

{block name=content}

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$purse->id}" />
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
                                                <div class="form-check form-switch">
                                                        <input class="form-check-input" name="enabled" value='1' type="checkbox" role="switch" id="active_checkbox"
                                                                {if $purse->enabled}checked{/if} />
                                                        <label class="form-check-label" for="active_checkbox">Активен</label>
                                                </div>
					</div>
				</div>
				<div class="name_row">
					<input class="form-control form-control-lg" name="name" type="text" value="{$purse->name}"
						autocomplete="none" />

				</div>
			</div>

			<div class="col-lg-6">
				<ul class="property_block">
					<li>
						<label for="amount" class="col-form-label">Баланс</label>
						<input class="form-control" id="amount" name="amount" type="text" value="{$purse->amount}"
							disabled />
					</li>

					{if $check_purse_amount != $purse->amount}
						<li>
							<label for="check_purse_amount" class="col-form-label">Проверочный баланс</label>
							<input class="form-control" id="check_purse_amount" name="check_purse_amount " type="text"
								value="{$check_purse_amount}" disabled />
						</li>
					{/if}

					<li>
						<label for="currency_id" class="col-form-label">Валюта</label>
						<select class="form-select" id="currency_id" name="currency_id">
							{foreach $currencies as $c}
								<option value="{$c->id}" {if $purse->currency_id == $c->id}selected{/if}>{$c->name}
								</option>
							{/foreach}
						</select>
					</li>

					<li>
						<label for="comment" class="col-form-label">Заметки</label>
						<textarea class="form-control" id="comment" name="comment">{$purse->comment}</textarea>
					</li>
				</ul>
			</div>

			<div class="col-12 btn_row">
				<{include file="parts/button.tpl"}
			</div>
		</div>
	</form>

{/block}