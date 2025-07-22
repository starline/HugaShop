if (window.jQuery) {
	$(document).ready(function () {
		//выводим дополнительную секцию в "Настройках главного модуля"
		if ($("[name='mid']").length && $("[name='mid']").val() == 'main') {
			//отправляем запрос на получение данных о текущих выставленных периодах
			$.post("/ajax/admin.php", { 'func': 'getSeasonsDates' }, function (data) {
				if (data.status == 'ok') {
					var content = "<tr class='heading'>" +
						"<td valig='top' align='center' colspan='2'>" +
						"<b>Настройка сезонных интервалов</b>" +
						"<td>" +
						"</tr>" +
						"<tr>" +
						"<td class='field-name' width='50%' valign='top'>Лето:</td>" +
						"<td width='50%' valign='top'>" +
						"с <input class='seasons_input' type='text' id='spring_date_from' name='spring_date_from' value='" + data.data['spring_f'] + "' /> " +
						"по <input class='seasons_input' type='text' id='spring_date_to' name='spring_date_to' value='" + data.data['spring_t'] + "' />" +
						"</td>" +
						"</tr>" +
						"<tr>" +
						"<td class='field-name' width='50%' valign='top'>Зима:</td>" +
						"<td width='50%' valign='top'>" +
						"с <input class='seasons_input' type='text' id='winter_date_from' name='winter_date_from' value='" + data.data['winter_f'] + "' /> " +
						"по <input class='seasons_input' type='text' id='winter_date_to' name='winter_date_to' value='" + data.data['winter_t'] + "' />" +
						"<div style='padding-left:10px;'><i>формат даты <b>dd.mm.YYYY</b>, все даты имеют логику \"включительно\", изменения вступают в действие только после нажатия кнопки \"Применить\" под этим сообщением</i></div>" +
						"&nbsp;&nbsp;&nbsp;<input onclick='saveSeasonsDates();' type='button' name='seasons_date_save' value='Применить' />" +
						"<span style='padding-left:25px' id='seasons_message'></span>" +
						"</td>" +
						"</tr>"
						;
					$("#edit1_edit_table").find("tr:last").after(content);
				}
			}, 'json')
		}

	});

	function saveSeasonsDates() {
		var obj = { 'func': 'saveSeasonsDates' };
		$("#seasons_message").html("");
		$(".seasons_input").each(function () {
			if (!$(this).val()) {
				$("#seasons_message").css("color", "red");
				$("#seasons_message").html("Поля даты не должны быть пустыми");
				return false;
			}
			else {
				obj[$(this).attr('name')] = $(this).val();
			}
		});
		$.post("/ajax/admin.php", obj, function (data) {
			if (data.status == "ok") {
				$("#seasons_message").css("color", "green");
				$("#seasons_message").html("Сезонные интервалы были успешно сохранены");
			}
			else {
				$("#seasons_message").css("color", "red");
				$("#seasons_message").html(data.msg);
			}

		}, 'json');
	}
}