
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


// Create chart and load datasets
export function makeChart(element, chartOptions = {}, datasets = []) {
	if (!element) {
		console.warn('makeChart: element not found');
		return;
	}

	// Build default marker sizes based on datasets
	if (!chartOptions.markers) chartOptions.markers = {};
	chartOptions.markers.size = datasets.map((d) => (d.options && d.options.markerSize) ? d.options.markerSize : 0);

	let chartData = {
		series: [],
		chart: null,
		load: function (overrides = {}) {
			this.series = [];
			if (this.chart) this.chart.updateSeries([]);
			datasets.forEach((data) => {
				if (data && data.filter && data.options) {
					let filter = $.extend({}, data.filter, overrides);
					getChartData(this, filter, data.options);
				}
			});
		}
	};

	let chart = createApexChart(element, chartOptions);
	chartData.ready = chart.render().then(function () {
		chartData.chart = chart;
		chartData.load();
	});
	return chartData;
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
			defaultLocale: 'ru',
			zoom: {
				autoScaleYaxis: true,
				enabled: true
			},
			toolbar: {
				show: false
			},
			events: {
				mounted: hideOverlappingDataLabels,
				updated: hideOverlappingDataLabels
			}
		},
		xaxis: {
			type: 'datetime'
		},
		plotOptions: {
			bar: {
				dataLabels: { position: 'top' },
				columnWidth: '60%',
				rangeBarGroupRows: false
			}
		},
		tooltip: { x: { format: 'MMMM yyyy' } },
		dataLabels: {
			enabled: true,
			offsetY: -15,
			style: {
				colors: ['#000']
			}
		},
		title: { align: 'left' }
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

	options.range = filter.range ?? options.range;
	if (options.range) {

		const now = luxon.DateTime.utc();

		if (options.range === 'month') {
			filter.fromDate = now.minus({ months: 1 }).toISODate();
			filter.toDate = now.plus({ day: 1 }).toISODate();
		} else if (options.range === 'year') {
			filter.fromDate = now.minus({ years: 1 }).toISODate();
			filter.toDate = now.plus({ day: 1 }).toISODate();
		} else if (options.range === 'all') {
			delete filter.fromDate;
			delete filter.toDate;
		}

		delete filter.range;
	}

	if (options.type) {
		filter.type = options.type;
	}

	if (apex.chart) {
		if (filter.filter === 'byDay') {
			apex.chart.updateOptions({ tooltip: { x: { format: 'dd MMMM yyyy' } } });
		} else if (filter.filter === 'byMonth') {
			apex.chart.updateOptions({ tooltip: { x: { format: 'MMMM yyyy' } } });
		}
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
				color: options.color,
				type: options.chartType
			});

			apex.chart.updateSeries(apex.series);
		}
	});
}


// Hide overlapping ApexCharts data labels
export function hideOverlappingDataLabels(chartContext) {
	const nodes = Array.from(chartContext.el.querySelectorAll('.apexcharts-datalabel'));

	nodes.forEach((label) => {
		label.style.display = '';
	});

	const items = nodes.map((label) => ({ label, rect: label.getBoundingClientRect() }));

	items.sort((a, b) => {
		if (a.rect.top === b.rect.top) {
			return a.rect.left - b.rect.left;
		}
		return a.rect.top - b.rect.top;
	});

	const boxes = [];
	items.forEach((item) => {
		const rect = item.label.getBoundingClientRect();
		const overlap = boxes.some((box) => {
			return !(rect.right < box.left || rect.left > box.right || rect.bottom < box.top || rect.top > box.bottom);
		});
		if (overlap) {
			item.label.style.display = 'none';
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
