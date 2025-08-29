
/**
 * Common.js file
 * Functions, actions
 * 
 * @author Andri Huga
 * @version 2.2
 * 
 */

// Words count
export function worldsCount(selector = '.worlds_count') {
	$(selector).each(function () {
		const textarea = $(this).find('textarea, input');
		const fill = $(this).find('.worlds_fill');
		$(this).find('.worlds_max').text('max:' + textarea.attr('maxlength'));
		const update_count = function () {
			fill.text(textarea.val().length);
		};
		textarea.on('input', update_count);
		update_count();
	});
}


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
export function autofillMeta(
	name_selector = 'input[name="name"]',
	meta_title_selector = 'input[name="meta_title"]',
	url_selector = 'input[name="url"]'
) {
	let meta_title_touched = true;
	let url_touched = true;

	if ($(meta_title_selector).val() === generateMetaTitle() || $(meta_title_selector).val() === '') {
		meta_title_touched = false;
	}

	if ($(url_selector).val() === generateUrl() || $(url_selector).val() === '') {
		url_touched = false;
	}

	$(meta_title_selector).on('change', function () { meta_title_touched = true; });
	$(url_selector).on('change', function () { url_touched = true; });

	$(name_selector).on('keyup', function () {
		if (!meta_title_touched) {
			$(meta_title_selector).val(generateMetaTitle());
		}
		if (!url_touched) {
			$(url_selector).val(generateUrl());
		}
	});
}

function generateMetaTitle() {
	return $('input[name="name"]').val();
}

function generateUrl() {
	let url = $('input[name="name"]').val();
	url = url.replace(/[\s]+/gi, '-'); // пробелы
	url = translit(url);
	url = url.replace(/[^0-9a-z_\-]+/gi, '').toLowerCase();
	return url;
}


// Open Fancy
export function asignFancyOpen() {
	$('body').on('click', 'a.open_fancybox', function (e) {
		e.preventDefault();
		$.fancybox.open({
			type: 'ajax',
			src: $(this).attr('href'),
			touch: false,
			closeExisting: true,
			afterShow: asignFancyAjax
		});
	});
}


// Form in Fancy
export function asignFancyAjax() {

	// Ajax ссылки
	$('.fancybox-inner a.ajax').on('click', function (e) {
		e.preventDefault();
		$.fancybox.open({
			type: 'ajax',
			src: $(this).attr('href'),
			touch: false,
			closeExisting: true,
			afterShow: asignFancyAjax
		});
	})

	// Ajax форм
	$('.fancybox-inner form').on('submit', function (e) {
		e.preventDefault();
		$(this).ajaxSubmit({
			dataType: "html",
			beforeSubmit: function (formData, form, options) {
				$.fancybox.getInstance()?.showLoading();
			},
			success: function (response, statusText, xhr, $form) {

				// Приоритет: редирект через заголовок
				let redirectUrl = xhr.getResponseHeader('X-Redirect');
				if (redirectUrl) {
					window.location.href = redirectUrl;
					return;
				}

				response = parseResponseByType(response);
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
				console.error(errorThrown || 'Request failed');
			}
		});
	});
}


// Parse response
function parseResponseByType(response) {
	let result = {};
	if (response.indexOf('{') == 0) { 	// it is JSON response
		result['data'] = JSON.parse(response);
		result['type'] = 'json';
	} else {							// it is HTML response
		result['data'] = response;
		result['type'] = 'html';
	}
	return result;
}


// Ajax icons
export function ajaxEntityUpdateIcon(icon, entity, var_name, csrf) {

	icon.addClass('loading_icon');
	let line = icon.closest(".list_row");

	let id = line.find('input[name*="check"]').val();
	if (!id) {
		id = line.attr('item_id');
	}

	let state = line.hasClass(var_name + '_off') ? 1 : 0;
	csrf = csrf ?? window.csrf;

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


// Notice blocks show/hide
export function initNoticeBlocks(context = document) {
	$(context).find('.notice_block').each(function () {
		const height = $(this).height();
		const minimize_height = 60;
		if (height > minimize_height && (height - minimize_height) > 40) {
			$(this).addClass('minimizeble minimize');
			$(this).find('.show_link_block a').text("раскрыть ↓");
		}
	});

	$(context).find('.show_link_block a').off('click.notice').on('click.notice', function () {
		const block = $(this).closest('div.notice_block');
		if (block.hasClass('minimize')) {
			block.removeClass('minimize');
			$(this).text('скрыть ↑');
		} else {
			block.addClass('minimize');
			$(this).text('раскрыть ↓');
		}
		return false;
	});
}


// После завершения сортировки переиндексировать input-ы
export function indexListRows(container_selector, item_name) {
	$(container_selector).find('.list_row').each(function (idx) {
		$(this).find('input, select, textarea').each(function () {
			const pattern = new RegExp(item_name + '\\[(?:\\d+|INDEX)\\]');
			this.name = this.name.replace(pattern, item_name + '[' + idx + ']');
		});
	});
	console.log('index row');
}


// Tooltips and Popover
export function assignTooltip(selector) {
	const container = selector ? document.querySelector(selector) : document;
	if (!container) return;

	const tooltipTriggerList = container.querySelectorAll('[data-bs-toggle="tooltip"]');
	[...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));


	const popoverTriggerList = container.querySelectorAll('[data-bs-toggle="popover"]');
	[...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
}


// Action btn
export function assignButton(selector) {
	$(selector).on('click', function (e) {

		// Если уже в процессе — не даём нажимать повторно
		if ($(this).hasClass('disabled')) {
			e.preventDefault();
			(this).prop('disabled', true);
			return;
		}

		$(this).addClass('disabled');
	});
}

export function allButtonOn(selector) {
	$(selector).removeClass('disabled').prop('disabled', false);
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
