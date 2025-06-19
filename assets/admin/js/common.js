
/**
 * Common.js file
 * Functions, actions
 * 
 * @author Andri Huga
 * @version 2.1
 * 
 */

// Зум картинок
export function initFancybox() {
	$("a.zoom").fancybox({
		buttons: [
			'close'
		],
		image: {
			preload: true
		},
		closeExisting: true,
		defaultType: "image"
	});
}


// Generate meta
export function generate_meta_title() {
	return $('input[name="name"]').val();
}

export function generate_meta_keywords() {
	let name = $('input[name="name"]').val();
	let result = name;
	let brand = $('select[name="brand_id"] option:selected').attr('brand_name');

	if (typeof (brand) == 'string' && brand != '')
		result += ', ' + brand;

	$('select[name="categories[]"]').each(function (index) {
		c = $(this).find('option:selected').attr('category_name');
		if (typeof (c) == 'string' && c != '')
			result += ', ' + c;
	});
	return result;
}

export function generate_url() {
	let url = $('input[name="name"]').val();
	url = url.replace(/[\s]+/gi, '-'); // пробелы
	url = translit(url);
	url = url.replace(/[^0-9a-z_\-]+/gi, '').toLowerCase();
	return url;
}

export function generate_meta_description() {
	return $('textarea[name=description]').val().
		replace(/(<([^>]+)>)/ig, ' ').
		replace(/(\&nbsp;)/ig, ' ').
		replace(/^\s+|\s+$/g, '').substr(0, 512);
}


// Form in Fancy
export function asignFancyAjax() {

	// Ajax ссылки
	$('.fancybox-inner a.ajax').on('click', function (e) {
		e.preventDefault();
		$.post($(this).attr('href'), function (response) {
			$.fancybox.open({
				type: 'html',
				src: response,
				touch: false,
				closeExisting: true,
				afterShow: asignFancyAjax
			});
		});
	})

	// Ajax форм
	$('.fancybox-inner form').on('submit', function (e) {
		e.preventDefault();
		$(this).ajaxSubmit({
			dataType: "html",
			beforeSubmit: function (arr, form, options) {
				// показать лоадер
			},
			success: function (response) {
				response = $.parseResponseByType(response);
				if (response.type == 'html') {
					$.fancybox.open({
						type: 'html',
						src: response.data,
						touch: false,
						closeExisting: true,
						afterShow: asignFancyAjax
					});
				} else {
					data = response.data;
					if (data.redirect) {
						window.location.href = data.redirect;
					}
				}
			},
			error: function (xmlRequest, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
	});
}


// Ajax icons
export function ajax_icon(icon, entity, var_name, csrf) {

	icon.addClass('loading_icon');
	let line = icon.closest(".list_row");

	let id = line.find('input[name*="check"]').val();
	if (!id) {
		id = line.attr('item_id');
	}

	let state = line.hasClass(var_name + '_off') ? 1 : 0;

	$.ajax({
		type: 'POST',
		url: '/admin/ajax/update_entity',
		data: {
			'entity': entity,
			'id': id,
			'values': { [var_name]: state },
			'csrf': csrf
		},
		success: function (data) {
			icon.removeClass('loading_icon');
			if (state) {
				line.removeClass(var_name + '_off');
				line.addClass(var_name + '_on');
			} else {
				line.removeClass(var_name + '_on');
				line.addClass(var_name + '_off');
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr.status);
			console.log(thrownError);
		},
		dataType: 'json'
	});
}


// Create ApexCharts chart with default Russian locale
export function createApexChart(element, options = {}) {
	const baseOptions = {
		series: [],
		chart: {
			locales: [{
				name: 'ru',
				options: {
					months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
					shortMonths: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек']
				}
			}],
			defaultLocale: 'ru'
		},
		tooltip: { x: { format: 'MMMM yyyy' } }
	};

	const finalOptions = $.extend(true, {}, baseOptions, options);
	return new ApexCharts(element, finalOptions);
}

// Get Chart data from Ajax
export function getChartData(apex, filter, options) {
	if (!options || !options.url) {
		console.error('getChartData: options.url is required');
		return;
	}
	if (options.type) {
		filter.type = options.type;
	}
	$.post(options.url, filter, function (data) {
		if (data && data[0] != null) {
			let datas = [];
			data.forEach((point) => {
				let dt;
				if (filter.filter == 'byMonth')
					dt = luxon.DateTime.local(point.year, point.month);
				else
					dt = luxon.DateTime.local(point.year, point.month, point.day);
				datas.push([dt.toJSDate().getTime(), parseInt(point.y)]);
			});

			apex.series.push({
				name: options.label,
				data: datas,
				color: options.color
			});

			apex.chart.updateSeries(apex.series);
		}
	});
}

// Hide overlapping ApexCharts data labels
export function hideOverlappingDataLabels(chartContext) {
	const labels = chartContext.el.querySelectorAll('.apexcharts-data-label');
	const boxes = [];

	labels.forEach((label) => {
		label.style.display = '';
	});

	labels.forEach((label) => {
		const rect = label.getBoundingClientRect();
		const overlap = boxes.some((box) => {
			return !(rect.right < box.left || rect.left > box.right || rect.bottom < box.top || rect.top > box.bottom);
		});
		if (overlap) {
			label.style.display = 'none';
		} else {
			boxes.push(rect);
		}
	});
}


// RU -> EN
export function translit(str) {
	let ru = (
		"А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я"
	).split("-");
	let en = (
		"A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-I-i-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-С-с-CH-ch-SH-sh-SCH-sch-'-'-Y-y-'-'-E-e-YU-yu-YA-ya"
	).split("-");

	let res = '';
	for (let i = 0, l = str.length; i < l; i++) {
		let s = str.charAt(i),
			n = ru.indexOf(s);
		if (n >= 0) { res += en[n]; } else { res += s; }
	}
	return res;
}
