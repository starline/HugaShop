{extends file='wrapper/main.tpl'}
{include file="order/parts/menu_part.tpl"}


{if $label->id}
	{$meta_title = $label->name}
{else}
	{$meta_title = 'Новая метка'}
{/if}


{block name=content}
	<!-- Основная форма -->
	<form method="post" enctype="multipart/form-data">
		<input name="id" type="hidden" value="{$label->id}" />
		{getCSRFInput}

		<div class="row gx-5">
			<div class="col-12">
				<div class="over_name">
					<div class="checkbox_line">
                                                <div class="form-check form-switch">
                                                        <input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch" id="active_checkbox"
                                                                {if $label->enabled}checked{/if} />
                                                        <label class="form-check-label" for="active_checkbox">Активен</label>
                                                </div>
                                                <div class="form-check form-switch">
                                                        <input class="form-check-input" name="in_filter" value="1" type="checkbox" role="switch" id="in_filter_checkbox"
                                                                {if $label->in_filter}checked{/if} />
                                                        <label class="form-check-label" for="in_filter_checkbox">Использовать в фильтре заказов</label>
                                                </div>
					</div>
				</div>

				<div class="name_row">
					<span class="color_piker">
						<input type="color" class="form-control form-control-color" name="color" id="color_input"
							value="#{$label->color}" data-bs-toggle="tooltip" title="Цвет метки" />
					</span>
					<input class="form-control form-control-lg" name="name" type="text" value="{$label->name}" />

				</div>
			</div>

			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		</div>
	</form>
{/block}