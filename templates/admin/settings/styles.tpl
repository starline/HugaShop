{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}

{if $style_file}
	{$meta_title = "Стиль $style_file" scope=global}
{/if}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">
				{if $message_error == 'permissions'}Установите права на запись для файла {$style_file}
				{elseif $message_error == 'theme_locked'}Текущая тема защищена от изменений. Создайте копию темы.
				{else}{$message_error}
				{/if}
			</span>
		</div>
	{/if}

	<div class="header_top">
		<h1>Тема {$current_theme}, стиль {$style_file}</h1>
	</div>

	<div class="row gx-5">

		<!-- Список файлов для выбора -->
		<div class="col-12 layer">
			<div class="templates_names">
				{foreach item=s from=$styles}
					<a {if $style_file == $s}class="selected" {/if} href='/admin/styles?file={$s}'>{$s}</a>
				{/foreach}
			</div>
		</div>

		{if $style_file}
			<div class="col-12">
				<form>
					<textarea id="content" name="content" style="width:700px;height:500px;">{$style_content}</textarea>
				</form>
			</div>
			<div class="col-12 btn_row">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</div>
		{/if}
	</div>
{/block}


{block name=body_script append}

	{* Подключаем редактор кода *}
	<script type="text/javascript" src="{'js/codemirror/lib/codemirror.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/addon/selection/active-line.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/mode/css/css.js'|asset}"></script>

	<link rel="stylesheet" href="{'js/codemirror/lib/codemirror.css'|asset}" />

	<style type="text/css">
		.CodeMirror {
			font-family: 'Courier New';
			margin-bottom: 10px;
			border: 1px solid #c0c0c0;
			background-color: #ffffff;
			height: auto;
			width: 100%;
		}

		.CodeMirror-scroll {
			overflow-y: hidden;
			overflow-x: auto;
		}
	</style>

	<script type="module">
		{literal}

			var editor = CodeMirror.fromTextArea(document.getElementById("content"), {
				mode: "css",
				lineNumbers: true,
				styleActiveLine: true,
				matchBrackets: false,
				enterMode: 'keep',
				indentWithTabs: false,
				indentUnit: 1,
				tabMode: 'classic'
			});

			$(function() {
				// Сохранение кода аяксом
				function save() {
					$('.CodeMirror').css('background-color', '#e0ffe0');
					content = editor.getValue();

					$.ajax({
						type: 'POST',
						url: '/admin/ajax/templates/save_style',
						data: {'content': content, 'theme':'{/literal}{$current_theme}{literal}', 'style': '{/literal}{$style_file}{literal}'},
						success: function(data) {

							$('.CodeMirror').animate({'background-color': '#ffffff'});
						},
						dataType: 'json'
					});
				}

				// Нажали кнопку Сохранить
				$('input[name="save"]').click(function() {
					save();
				});

				// Обработка ctrl+s
				var isCtrl = false;
				var isCmd = false;
				$(document).keyup(function(e) {
					if (e.which == 17) isCtrl = false;
					if (e.which == 91) isCmd = false;
				}).keydown(function(e) {
					if (e.which == 17) isCtrl = true;
					if (e.which == 91) isCmd = true;
					if (e.which == 83 && (isCtrl || isCmd)) {
						save();
						e.preventDefault();
					}
				});
			});

		{/literal}
	</script>
{/block}