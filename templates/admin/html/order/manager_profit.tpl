{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{$meta_title='Настройки дохода менеджера'}


{block name=content}
	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		{getCSRFInput}

		<div class="row gx-5">

			<div class="col-lg-6 layer">
				<h2>Вознаграждения менеджеров</h2>
				<div>
					<ul class="property_block">
						<li class="row_sm">
							<label for="create_order_rate" class="col-form-label">Создал новый заказ</label>
							<div class="input-group">
								<input class="form-control" name="create_order_rate" id="create_order_rate" type="text"
									value="{$settings->create_order_rate}" />
								<span class="input-group-text">%</span>
							</div>
						</li>

						<li class="row_sm">
							<label for="take_order_rate" class="col-form-label">Взял готовый заказа</label>
							<div class="input-group">
								<input class="form-control" name="take_order_rate" id="take_order_rate" type="text"
									value="{$settings->take_order_rate}" />
								<span class="input-group-text">%</span>
							</div>
						</li>

						<li class="row_sm">
							<label for="referral_order_rate" class="col-form-label">Реферальный заказ</label>
							<div class="input-group">
								<input class="form-control" name="referral_order_rate" id="referral_order_rate" type="text"
									value="{$settings->referral_order_rate}" />
								<span class="input-group-text">%</span>
							</div>
						</li>

					</ul>
				</div>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl"}
			</div>
		</div>

	</form>
{/block}