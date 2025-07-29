{extends 'wrapper/main.tpl'}

{$meta_title="Экспорт $entity_name"}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">
				{if $message_error == 'no_permission'}
					Установите права на запись в папку {$export_files_dir}
				{else}
					{$message_error}
				{/if}
			</span>
		</div>
	{/if}

	<div class="header_top">
		<h1>Экспортировать {$entity_name} в CSV</h1>
	</div>

	<div class="row gx-5">
		{if $message_error != 'no_permission'}
			<div class="col-12 mt-2">
				<div class="progress" id="progressbar">
					<div class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0"
						aria-valuemax="100" style="width: 0%"></div>
				</div>
			</div>

			<div class="col-12 btn_row">
				{include file="parts/button.tpl" label="Экспортировать" extra_attrs="id=start"}
			</div>
		{/if}
	</div>
{/block}


{block name=body_script append}

	<script type="text/javascript" src="{'js/piecon/piecon.js'|asset}" charset="utf-8"></script>

	<script type="module">
		var filter_arr = {$filter_arr|raw};
		var export_file_url =  "{$export_file_url}";
		var entity = "{$entity}";

		{literal}	
			var in_process = false;

			$(function() {

				// On document load
				$('button#start').click(function() {

					Piecon.setOptions({fallback: 'force'});
					Piecon.setProgress(0);

					$("#start").hide('fast');
					do_export();

				});

				function do_export(page) {
					filter_arr['page'] = typeof(page) != 'undefined' ? page : 1;
					filter_arr['csrf'] = window.csrf;

					$.ajax({
						url: "/admin/ajax/" + entity + "/export",
						data: filter_arr,
						dataType: 'json',
						success: function(data) {

							if (data && !data.end) {
								Piecon.setProgress(Math.round(100 * data.page / data.totalpages));
								$('.progress-bar').css('width', Math.round(100 * data.page / data.totalpages) +
									'%');
								do_export(data.page * 1 + 1);
							} else {
								Piecon.setProgress(100);
								$("#progressbar").hide('fast');
								window.location.href = export_file_url;

							}
						},
						error: function(xhr, status, errorThrown) {
							alert(errorThrown + '\n' + xhr.responseText);
						}
					});
				}
			});
		{/literal}
	</script>
{/block}