{block name=tabs}

	{if 'user'|user_access and $route|in_array:[UserListAdmin, UserAdmin, UserSettingsAdmin, GroupListAdmin, GroupAdmin, GroupNewAdmin]}
		<li class="mini {if $route|in_array:[UserListAdmin, UserAdmin, UserSettingsAdmin]}active{/if}">
			<a href="/admin/users">Покупатели</a>
		</li>
	{/if}

	{if 'user_group'|user_access and $route|in_array:[GroupListAdmin, GroupAdmin, GroupNewAdmin, UserListAdmin, UserAdmin, UserSettingsAdmin]}
		<li class="right mini {if $route|in_array:[GroupListAdmin, GroupAdmin, GroupNewAdmin]}active{/if}">
			<a href="/admin/user/groups">Группы</a>
		</li>
	{/if}

	{if 'user_notifier'|user_access and $route|in_array:[MailingNewAdmin, MailingAdmin, MailingListAdmin, NotifierAdmin, NotifierListAdmin, NotifierNewAdmin, MailTemplateNewAdmin, MailTemplateListAdmin, MailTemplateAdmin]}
		<li class="mini {if $route|in_array:[MailingNewAdmin, MailingAdmin, MailingListAdmin]}active{/if}">
			<a href="/admin/user/mailings">Список рассылки</a>
		</li>

		<li class="right mini {if $route|in_array:[MailTemplateNewAdmin, MailTemplateListAdmin, MailTemplateAdmin]}active{/if}">
			<a href="/admin/user/mailing/templates">Шаблоны</a>
		</li>

		<li class="right mini {if $route|in_array:[NotifierAdmin, NotifierListAdmin, NotifierNewAdmin]}active{/if}">
			<a href="/admin/user/notifiers">Оповещения</a>
		</li>
	{/if}

	{if 'user_coupon'|user_access and $route|in_array:[CouponListAdmin, CouponAdmin, CouponNewAdmin]}
		<li class="mini active">
			<a href="/admin/user/coupons">Купоны</a>
		</li>
	{/if}

{/block}