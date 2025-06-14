{block name="tabs"}
	{if 'blog'|user_access and $route|in_array:[PostAdmin, BlogAdmin, PostNewAdmin]}
		<li class="mini active">
			<a href="/admin/blog">Блог</a>
		</li>
	{/if}

	{if 'comment'|user_access and $route|in_array:[CommentListAdmin, CommentAdmin]}
		<li class="mini active">
			<a href="/admin/comments">Комментарии</a>
		</li>
	{/if}

	{if 'feedback'|user_access and $route|in_array:[FeedbackListAdmin]}
		<li class="mini active">
			<a href="/admin/feedbacks">Обратная связь</a>
		</li>
	{/if}

	{if 'page'|user_access and $route|in_array:[PageListAdmin, PageAdmin]}
		<li class="mini active">
			<a href="/admin/pages">Страницы</a>
		</li>
	{/if}
{/block}