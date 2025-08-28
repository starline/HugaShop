{extends file='wrapper/main.tpl'}
{include file='finance/parts/menu_part.tpl'}

{$meta_title = 'Валюты'}

{block name=content}

	<!-- Заголовок -->
	<div class="header_top">
		<h1>Валюты</h1>
		<a class="add" id="add_currency" href="#">Добавить</a>
	</div>


	<form method="post">
		{getCSRFInput}

		<!-- Валюты -->
		<div id="currencies_block">
			<div class="list sortable_on" id="currencies">
				{foreach $currencies as $c}
					<div class="list_row {if !$c->enabled} enabled_off{/if}{if $c->cents == 0} cents_off{/if}"
						item_id="{$c->id}">
						<input type="hidden" name="check[]" value="{$c->id}" />

						<div class="move">
							<div class="move_zone"></div>
						</div>

						<div class="col">
							<div class="row">
								<div class="col-12 col-md-5">
									<input class="form-control" name="currency[id][{$c->id}]" type="hidden" value="{$c->id}" />
									<input class="form-control" name="currency[name][{$c->id}]" type="text" value="{$c->name}"
										placeholder="Название валюты" />
								</div>

								<div class="col-6 col-md-1">
									<input class="form-control" name="currency[sign][{$c->id}]" type="text" value="{$c->sign}"
										placeholder="Знак" />
								</div>

								<div class="col-6 col-md-1">
									<input class="form-control" name="currency[code][{$c->id}]" type="text" value="{$c->code}"
										placeholder="Код ISO" />
								</div>

								<div class="col-12 col-md-4 rate">
									{if !$c@first}
										<div class="input-group">
											<span class="input-group-text">{$c->sign}</span>
											<input class="form-control" name="currency[rate_from][{$c->id}]" type="text"
												value="{$c->rate_from}" />
											<span class="input-group-text">=</span>

											<input class="form-control" name="currency[rate_to][{$c->id}]" type="text"
												value="{$c->rate_to}" placeholder="Курс" />
											<span class="input-group-text">{$currency->sign}</span>
										</div>
									{else}
										<input class="form-control" name="currency[rate_from][{$c->id}]" type="hidden"
											value="{$c->rate_from}" />
										<input class="form-control" name="currency[rate_to][{$c->id}]" type="hidden"
											value="{$c->rate_to}" />
									{/if}
								</div>
							</div>
						</div>

						<div class="icons">
							<a class="cents" data-bs-toggle="tooltip" title="Выводить копейки"></a>
							<i class="enable material-icons visibility" data-bs-toggle="tooltip"
								title="Показывать на сайте"></i>
							{if !$c@first}
								<i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
							{else}
								<i></i>
							{/if}
						</div>
					</div>
				{/foreach}



				<div class="list_row" id="new_currency" style="display:none;">
					<div class="move">
						<div class="move_zone"></div>
					</div>

					<div class="col">
						<div class="row">
							<div class="col-12 col-md-5">
								<input name="currency[id][]" type="hidden" value="" />
								<input class="form-control" name="currency[name][]" type=text value=""
									placeholder="Название валюты" />
							</div>
							<div class="col-6 col-md-1">
								<input class="form-control" name="currency[sign][]" type=text value="" placeholder="Знак" />
							</div>
							<div class="col-6 col-md-1">
								<input class="form-control" name="currency[code][]" type=text value=""
									placeholder="Код ISO" />
							</div>
							<div class="col-12 col-md-4 rate">
								<div class="input-group col">
									<span class="input-group-text"></span>
									<input class="form-control" name="currency[rate_from][]" type="text" value="1" />
									<span class="input-group-text">=</span>
									<input class="form-control" name="currency[rate_to][]" type="text" value="1"
										placeholder="Курс" />
									<span class="input-group-text">{$currency->sign}</span>
								</div>
							</div>
						</div>
					</div>

					<div class="icons">
						<i></i>
						<i></i>
						<i></i>
					</div>
				</div>
			</div>

		</div>

		<div id="action">
			<input type="hidden" name="recalculate" value='0' />
			<input type="hidden" name="action" value='' />
			<input type="hidden" name="action_id" value='' />
			<button class="btn btn-primary apply" type="submit">Применить</button>
		</div>
	</form>
{/block}


{block name=body_script append}
	<script type="module">
		import { ajaxEntityUpdateIcon } from '{"js/common.js"|asset}';

		{literal}
			$(function() {

				// Добавление валюты
				var curr = $('#new_currency').clone(true).removeAttr('id');
				$('#new_currency').removeAttr('id').remove();

				$('a#add_currency').on('click', function() {
					$(curr).clone(true).appendTo('#currencies').fadeIn('slow')
						.find("input[name*=currency][name*=name]").focus();
					return false;
				});

				// Скрыт/Видим
				$("i.enable").on('click', function() {
					ajaxEntityUpdateIcon($(this), 'currency', 'enabled', csrf);
					return false;
				});

				// Центы
				$("a.cents").on('click', function() {
					ajaxEntityUpdateIcon($(this), 'currency', 'cents', csrf);
					return false;
				});

				//  Удалить валюту
				$("i.delete").on('click', function() {
					$('input[type="hidden"][name="action"]').val('delete');
					$('input[type="hidden"][name="action_id"]').val($(this).closest(".list_row").find(
						'input[type="hidden"][name*="currency[id]"]').val());
					$(this).closest("form").submit();
				});

				// Запоминаем id первой валюты, чтобы определить изменение базовой валюты
				var base_currency_id = $('input[name*="currency[id]"]').val();

				$("form").on('submit', function() {
					if ($('input[type="hidden"][name="action"]').val() == 'delete' && !confirm(
							'Подтвердите удаление'))
						return false;
					if (base_currency_id != $('input[name*="currency[id]"]:first').val() && confirm(
							'Пересчитать все цены в ' + $('input[name*="name"]:first').val() +
							' по текущему курсу?', 'msgBox Title'))
						$('input[name="recalculate"]').val(1);
				});
			});
		{/literal}
	</script>
{/block}