{block name="tabs"}

	{if 'order'|user_access and $route|in_array:[OrderListAdmin, OrderAdmin, OrderNewAdmin]}
		<li class="mini status_new {if $status == '0'}active{/if}">
			<a href="{'OrderListAdmin'|link:[status => 0]}">Новые</a>
			{if $orders_info_count[0]}
				<div class="counter"><span>{$orders_info_count[0]}</span></div>
			{/if}
		</li>

		<li class="mini status_work {if $status == 1}active{/if}">
			<a href="{'OrderListAdmin'|link:[status => 1]}">Приняты</a>
			{if $orders_info_count[1]}
				<div class="counter"><span>{$orders_info_count[1]}</span></div>
			{/if}
		</li>

		<li class="mini status_shipped {if $status == 4}active{/if}">
			<a href="{'OrderListAdmin'|link:[status => 4]}">Отгружены</a>
			{if $orders_info_count[4]}
				<div class="counter gray"><span>{$orders_info_count[4]}</span></div>
			{/if}
		</li>

		<li class="mini status_done {if $status == 2}active{/if}">
			<a href="{'OrderListAdmin'|link:[status => 2]}">Выполнены</a>
		</li>

		<li class="mini status_delete {if $status == 3}active{/if}">
			<a href="{'OrderListAdmin'|link:[status => 3]}">Отмена</a>
		</li>

		{if isset($keyword)}
			<li class="mini active">
				<a href="{url label=null}">Поиск</a>
			</li>
		{/if}
	{/if}


	{if 'order'|user_access and $route|in_array:[CartListAdmin]}
		<li class="mini active">
			<a href="/admin/order/carts">Корзины</a>
		</li>
	{/if}


	{if $route|in_array:[ManagerProfitAdmin, OrderPaymentListAdmin, OrderPaymentAdmin, OrderPaymentNewAdmin, DeliveryListAdmin, OrderDeliveryAdmin, OrderDeliveryNewAdmin, LabelListAdmin, LabelAdmin, LabelNewAdmin]}
		{if 'user_manager'|user_access}
			<li class="mini {if $route|in_array:[ManagerProfitAdmin]}active{/if}">
				<a href="/admin/order/manager_profit">Доход менеджера</a>
			</li>
		{/if}

		{if 'order_payment'|user_access}
			<li class="mini {if $route|in_array:[OrderPaymentListAdmin, OrderPaymentAdmin, OrderPaymentNewAdmin]}active{/if}">
				<a href="/admin/order/payments">Оплата</a>
			</li>
		{/if}

		{if 'order_delivery'|user_access}
			<li class="mini {if $route|in_array:[DeliveryListAdmin, OrderDeliveryAdmin, OrderDeliveryNewAdmin]}active{/if}">
				<a href="/admin/order/deliveries">Доставка</a>
			</li>
		{/if}

		{if 'order_label'|user_access}
			<li class="mini {if $route|in_array:[LabelListAdmin, LabelAdmin, LabelNewAdmin]}active{/if}"><a
					href="/admin/order/labels">Метки</a></li>
		{/if}
	{/if}

{/block}