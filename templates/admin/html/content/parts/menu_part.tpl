{block name="tabs"}
	{if 'blog'|user_access and $route|in_array:[PostAdmin, PostListAdmin, PostNewAdmin]}
		<li class="mini active">
			<a href="{'PostListAdmin'|link}">Блог</a>
		</li>
	{/if}

	{if 'comment'|user_access and $route|in_array:[CommentListAdmin, CommentAdmin]}
		<li class="mini active">
			<a href="{'CommentListAdmin'|link}">Комментарии</a>
		</li>
	{/if}

	{if 'feedback'|user_access and $route|in_array:[FeedbackListAdmin]}
		<li class="mini active">
			<a href="{'FeedbackListAdmin'|link}">Обратная связь</a>
		</li>
	{/if}

	{if 'page'|user_access and $route|in_array:[PageListAdmin, PageAdmin]}
		<li class="mini active">
			<a href="{'PageListAdmin'|link}">Страницы</a>
		</li>
	{/if}
{/block}