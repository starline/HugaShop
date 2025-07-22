{extends 'wrapper/main.tpl'}

{block name=content}



	<div class="header-selection-wrapper">
		<div class="header-selection asva-container">

			<div class="header-selection-tabs">
				<div class="header-selection-tab bysize active" id="tab_by_size" onclick="tabs('by_size'); return false;">
					по размеру
				</div>
				<div class="header-selection-tab bycar" id="tab_by_car" onclick="tabs('by_car'); return false;">
					по автомобилю
				</div>
			</div>

			<!--Фильтр по размеру-->
			<div class="header-selection-by bysize" id="cont_by_size">
				<!-- asva:selection.size::filter_main-->
			</div>

			<!--Фильтр по автомобилю-->
			<div class="header-selection-by bycar hidden" id="cont_by_car">
				<div class="header-selection-by-tabs">
					<div class="header-selection-by-tab active" id="tab_by_car_tire">Шины</div>
					<div class="header-selection-by-tab" id="tab_by_car_disk">Диски</div>
				</div>

				<form method="GET" action="/catalog/by_auto/" id="filter_auto">
					<!-- asva:selection.auto::filter_main -->
				</form>
			</div>
		</div>
	</div>
{/block}