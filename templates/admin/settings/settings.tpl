{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}

{$meta_title = "Настройки сайта" scope=global}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">{if $message_error == 'watermark_is_not_writable'}Установите права на запись для файла
				{$config->images_watermark_file}{/if}</span>
		</div>
	{/if}

	<div class="header_top">
		<h1>Основные настройки сайта</h1>
	</div>

	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-lg-6 layer">
				<h2>Настройки сайта</h2>
				<ul class="property_block">
					<li>
						<label class="col-form-label" for="domain">Домен сайта</label>
						<input class="form-control" name="domain" id="domain" type="text" value="{$settings->domain}" />
					</li>
					<li>
						<label class="col-form-label" for="company_name">Имя компании</label>
						<input class="form-control" name="company_name" id="company_name" type="text"
							value="{$settings->company_name}" />
					</li>
					<li>
						<label class="col-form-label" for="company_description">Описание компании</label>
						<input class="form-control" name="company_description" id="company_description" type="text"
							value="{$settings->company_description}" />
					</li>
					<li>
						<label class="col-form-label" for="date_format">Формат даты</label>
						<input class="form-control" name="date_format" id="date_format" type="text"
							value="{$settings->date_format}" />
					</li>
					<li>
						<label class="col-form-label" for="timezone">Временная зона</label>
						<select class="form-select" name="timezone" id="timezone">
							{foreach $time_zones as $time_zone}
								<option value='{$time_zone}' {if $settings->timezone == $time_zone}selected{/if}>
									{$time_zone}
								</option>
							{/foreach}
						</select>
					</li>
				</ul>
			</div>

			<div class="col-lg-6 layer">
				<h2>Формат цены</h2>
				<ul class="property_block">
					<li>
						<label class="col-form-label" for="decimals_point">Разделитель копеек</label>
						<select class="form-select" name="decimals_point" id="decimals_point">
							<option value='.' {if $settings->decimals_point == '.'}selected{/if}>
								точка: 12.45 {$currency->sign}
							</option>
							<option value=',' {if $settings->decimals_point == ','}selected{/if}>
								запятая: 12,45 {$currency->sign}
							</option>
						</select>
					</li>
					<li>
						<label class="col-form-label" for="thousands_separator">Разделитель тысяч</label>
						<select class="form-select" name="thousands_separator" id="thousands_separator">
							<option value='' {if $settings->thousands_separator == ''}selected{/if}>
								без разделителя: 1245678 {$currency->sign}
							</option>
							<option value=' ' {if $settings->thousands_separator == ' '}selected{/if}>
								пробел: 1 245 678 {$currency->sign}
							</option>
							<option value=',' {if $settings->thousands_separator == ','}selected{/if}>
								запятая: 1,245,678 {$currency->sign}
							</option>
						</select>
					</li>
				</ul>
			</div>


			<div class="col-lg-6 layer">
				<h2>Настройки каталога</h2>
				<ul class="property_block">
					<li class="row_sm">
						<label class="col-form-label" for=products_num>Товаров на странице сайта</label>
						<input class="form-control" name="products_num" id=products_num type="text"
							value="{$settings->products_num}" />
					</li>
					<li class="row_sm">
						<label class="col-form-label" for=products_num_admin>Товаров на странице админки</label>
						<input class="form-control" name="products_num_admin" id=products_num_admin type="text"
							value="{$settings->products_num_admin}" />
					</li>
					<li class="row_sm">
						<label class="col-form-label" for=units>Единицы измерения товаров</label>
						<input class="form-control" name="units" id=units type="text" value="{$settings->units}" />
					</li>
					<li class="row_sm">
						<label class="col-form-label" for=weight_units>Единицы измерения веса</label>
						<input class="form-control" name="weight_units" id=weight_units type="text"
							value="{$settings->weight_units}" />
					</li>
					<li class="row_sm">
						<label class="col-form-label" for=rel_products_num>Рекомендуемых товаров</label>
						<input class="form-control" name="rel_products_num" id=rel_products_num type="text"
							value="{$settings->rel_products_num}" />
					</li>
				</ul>
			</div>


			<div class="col-lg-6 layer">
				<h2>Конфигурация водяного знака</h2>

				<div class="image_item">
					<div class="mb-3 row">
						<label for="watermark_file" class="col-3 col-form-label">Водяной знак</label>
						<div class="col-9">
							<input type="file" name="watermark_file" class="form-control" id="watermark_file" />
						</div>
					</div>
					<div class="mb-3 row">
						<img src="{$config->root_url}/files/watermark/watermark.png?{math equation='rand(10,10000)'}" />
					</div>
				</div>

				<div>
					<ul class="property_block">
						<li class="row_sm">
							<label for="watermark_offset_x" class="col-form-label">Горизонтальное положение водяного
								знака</label>
							<div class="input-group">
								<input class="form-control" name="watermark_offset_x" id="watermark_offset_x" type="text"
									value="{$settings->watermark_offset_x}" />
								<span class="input-group-text">%</span>
							</div>
						</li>
						<li class="row_sm">
							<label for="watermark_offset_y" class="col-form-label">Вертикальное положение водяного
								знака</label>
							<div class="input-group">
								<input class="form-control" name="watermark_offset_y" id="watermark_offset_y" type="text"
									value="{$settings->watermark_offset_y}" />
								<span class="input-group-text">%</span>
							</div>
						</li>
						<li class="row_sm">
							<label for="watermark_transparency" class="col-form-label">Непрозрачность знака (меньше &mdash;
								прозрачней)</label>
							<div class="input-group">
								<input class="form-control" name="watermark_transparency" id=watermark_transparency
									type=text value="{$settings->watermark_transparency}" />
								<span class="input-group-text">%</span>
							</div>
						</li>

						{if ($imagick)}
							<li class="row_sm">
								<label for="images_sharpen" class="col-form-label">Резкость изображений (рекомендуется
									20%)</label>
								<div class="input-group">
									<input class="form-control" name="images_sharpen" id=images_sharpen type=text
										value="{$settings->images_sharpen}" />
									<span class="input-group-text">%</span>
								</div>
							</li>
						{/if}
					</ul>
				</div>
			</div>


			<div class="col-lg-6 layer">
				<h2>SEO настройки</h2>
				<ul class="property_block">
					<li>
						<label for="meta_description" class="col-form-label">Товары (MetaDescription)</label>
						<textarea class="form-control" id="meta_description"
							name="product_meta_description">{$settings->product_meta_description}</textarea>
					</li>
					<li>
						<label for="emojis" class="col-form-label">Значки (Emojis)</label>
						<input class="form-control" id="emojis" name="emojis" type="text" value="{$settings->emojis}" />
					</li>
				</ul>
			</div>


			<div class="col-lg-6 layer">
				<h2>Настройки заказов</h2>
				<ul class="property_block">
					<li class="row_sm">
						<label class="col-form-label" for="max_order_amount">Максимум товаров в заказе</label>
						<input class="form-control" name="max_order_amount" id="max_order_amount" type="text"
							value="{$settings->max_order_amount}" />
					</li>
					<li>
						<label class="col-form-label" for="income_finance_category_id">Категория доходов</label>
						<select class="form-select" name="income_finance_category_id" id="income_finance_category_id">
							<option value="">Не выбрана</option>
							{foreach $income_finance_categories as $cat}
								<option value="{$cat->id}" {if $settings->income_finance_category_id == $cat->id}selected{/if}>
									{$cat->name}
								</option>
							{/foreach}
						</select>
					</li>
					<li>
						<label class="col-form-label" for="expense_finance_category_id">Категория расходов</label>
						<select class="form-select" name="expense_finance_category_id" id="expense_finance_category_id">
							<option value="">Не выбрана</option>
							{foreach $expense_finance_categories as $cat}
								<option value="{$cat->id}" {if $settings->expense_finance_category_id == $cat->id}selected{/if}>
									{$cat->name}
								</option>
							{/foreach}
						</select>
					</li>
				</ul>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>

	</form>

{/block}