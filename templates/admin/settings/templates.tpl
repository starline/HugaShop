{extends 'wrapper/main.tpl'}
{include 'settings/parts/menu_part.tpl'}

{if $template_file}
	{$meta_title = "Шаблон $template_file" scope=global}
{/if}

{block name=content}

	{if $message_error}
		<!-- Системное сообщение -->
		<div class="message message_error">
			<span class="text">
				{if $message_error == 'permissions'}Установите права на запись для файла {$template_file}
				{elseif $message_error == 'theme_locked'}Текущая тема защищена от изменений. Создайте копию темы.
				{else}{$message_error}
				{/if}
			</span>
		</div>
	{/if}

	<div class="header_top">
		<h1>Тема {$current_theme}, шаблон {$template_file}</h1>
	</div>

	<div class="row gx-5">

		<!-- Список файлов для выбора -->
		<div class="col-12 layer">
			<div class="templates_names">
				{foreach item=t from=$templates}
					<a {if $template_file == $t}class="selected" {/if} href='/admin/templates?file={$t}'>{$t}</a>
				{/foreach}
			</div>
		</div>

		{if $template_file}
			<div class="col-12">
				<form>
					<textarea class="form-control" id="template_content" name="template_content">{$template_content}</textarea>
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
	<script type="text/javascript" src="{'js/codemirror/mode/smarty/smarty.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/mode/smartymixed/smartymixed.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/mode/xml/xml.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/mode/htmlmixed/htmlmixed.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/mode/css/css.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/mode/javascript/javascript.js'|asset}"></script>
	<script type="text/javascript" src="{'js/codemirror/addon/selection/active-line.js'|asset}"></script>

	<link rel="stylesheet" href="{'js/codemirror/lib/codemirror.css'|asset}" />

	<style type="text/css">
		{literal}
			.CodeMirror {
				font-family: 'Courier New';
				margin-bottom: 10px;
				border: 1px solid #c0c0c0;
				background-color: #ffffff;
				height: auto;
			}

			.cm-s-default .cm-smarty.cm-tag{color: #ff008a;}
			.cm-s-default .cm-smarty.cm-string {color: #007000;}
			.cm-s-default .cm-smarty.cm-variable {color: #ff008a;}
			.cm-s-default .cm-smarty.cm-variable-2 {color: #ff008a;}
			.cm-s-default .cm-smarty.cm-variable-3 {color: #ff008a;}
			.cm-s-default .cm-smarty.cm-property {color: #ff008a;}
			.cm-s-default .cm-comment {color: #505050;}
			.cm-s-default .cm-smarty.cm-attribute {color: #ff20Fa;}
		{/literal}
	</style>

	<script type="module">
		{literal}

			var editor = CodeMirror.fromTextArea(document.getElementById("template_content"), {
				mode: "smartymixed",
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
						url: '/admin/ajax/templates/save_template',
						data: {'content': content, 'theme':'{/literal}{$current_theme}{literal}', 'template': '{/literal}{$template_file}{literal}'},
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