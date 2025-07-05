/**
 * Chart utilities component
 * Functions related to rendering ApexCharts graphs
 *
 * @author Andri Huga
 * @version 1.3
 */

export function makeChart(element, chartOptions = {}, datasets = []) {
    if (!element) {
        console.warn('makeChart: element not found');
        return;
    }

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


// make ApexChart
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


// get data
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
        } else if (options.range === 'quarter') {
            filter.fromDate = now.minus({ months: 3 }).toISODate();
            filter.toDate = now.plus({ day: 1 }).toISODate();
        } else if (options.range === 'half_year') {
            filter.fromDate = now.minus({ months: 6 }).toISODate();
            filter.toDate = now.plus({ day: 1 }).toISODate();
        } else if (options.range === 'year') {
            filter.fromDate = now.minus({ years: 1 }).toISODate();
            filter.toDate = now.plus({ day: 1 }).toISODate();
        } else if (options.range === 'two_years') {
            filter.fromDate = now.minus({ years: 2 }).toISODate();
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


// hide labels 
export function hideOverlappingDataLabels(chartContext) {
    const nodes = Array.from(chartContext.el.querySelectorAll('.apexcharts-datalabel'));

    nodes.forEach((label) => {
        label.style.display = '';
        if (label.parentElement && label.parentElement !== label) {
            label.parentElement.style.display = '';
        }
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
            if (item.label.parentElement && item.label.parentElement !== item.label) {
                item.label.parentElement.style.display = 'none';
            }
        } else {
            boxes.push(rect);
        }
    });
}