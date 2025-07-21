{extends 'wrapper/main.tpl'}
{include 'user/parts/menu_part.tpl'}

{if $coupon->code}
	{$meta_title = $coupon->code}
{else}
	{$meta_title = 'Новый купон'}
{/if}

{block name=content}

	{if $message_error}
		<div class="message message_error">
			<span class="text">{if $message_error == 'code_exists'}Купон с таким кодом уже существует{/if}</span>
		</div>
	{/if}


	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-12">
				<div class="name_row">
					<input class="form-control form-control-lg" name="code" type="text" value="{$coupon->code}" />
					<input name="id" class="name" type="hidden" value="{$coupon->id}" />
				</div>
			</div>

			<div class="col-lg-6">
				<ul class="property_block">
					<li class="row_sm">
						<label class="col-form-label" for="value">Скидка</label>

						<div class="input-group">
							<input class="form-control coupon_value" name="value" id="value" type="text"
								value="{$coupon->value}" />

							<select class="form-select" name="type">
								<option value="percentage" {if $coupon->type=='percentage'}selected{/if}>%</option>
								<option value="absolute" {if $coupon->type=='absolute'}selected{/if}>{$currency->sign}
								</option>
							</select>
						</div>
					</li>
					<li class="row_sm">
						<label for="min_order_price" class="col-form-label">Для заказов от</label>
						<div class="input-group">
							<input class="form-control coupon_value" id="min_order_price" type="text" name="min_order_price"
								value="{$coupon->min_order_price}">
							<span class="input-group-text"> {$currency->sign}</span>
						</div>
					</li>
				</ul>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="single" id="single" value="1"
						{if $coupon->single==1}checked{/if}>
					<label class="form-check-label" for="single">одноразовый</label>
				</div>
			</div>

			<div class="col-lg-6">
				<ul class="property_block">
					<li class="row_sm">
						<span class="form-check">
							<input class="form-check-input" type="checkbox" name="expires" id="expires" value="1"
								{if $coupon->expire}checked{/if}>
							<label class="form-check-label" for="expires">Истекает</label>
						</span>

						<input class="form-control" type="text" name="expire" value='{$coupon->expire|date}'>
					</li>
				</ul>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>
		</div>
	</form>

	{include file='parts/tinymce_init.tpl'}

	<script type="module">
		import '{"js/jquery/datepicker/jquery.ui.datepicker-ru.js"|asset}';

		{literal}
			$(function() {

				$('input[name="expire"]').datepicker({
					regional: 'ru'
				});
				$('input[name="end"]').datepicker({
					regional: 'ru'
				});

				// On change date
				$('input[name="expire"]').focus(function() {
					$('input[name="expires"]').attr('checked', true);
				});

			});
		{/literal}
	</script>

{/block}