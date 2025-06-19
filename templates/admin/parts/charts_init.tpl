<script type="module">
    import 'https://cdn.jsdelivr.net/npm/apexcharts';
    import { createApexChart, getChartData } from '{"js/common.js"|asset}';

    // Выводим график через ApexCharts
    window.showStatGraphic = function(element, dataParamObj, types_arr, my_options = null, currency_sign, callback) {
        if (!$("div").is('#' + element)) {
            return;
        }

        let options = {
            chart: { type: 'bar', height: 350 },
            xaxis: { type: 'datetime' },
            plotOptions: { bar: { dataLabels: { position: 'top' } } },
            tooltip: { x: { format: (dataParamObj.filter == 'byDay') ? 'dd LLL yyyy' : 'MMMM yyyy' } },
            title: { text: 'Статистика продаж' }
        };

        if (my_options !== null) {
            $.extend(true, options, my_options);
        }

        let chartData = { series: [] };
        let chart = createApexChart(document.getElementById(element), options);

        chart.render().then(function() {
            chartData.chart = chart;
            types_arr.forEach(function(t) {
                let params = $.extend({}, dataParamObj, { type: t });
                let opt = { url: '/admin/ajax/stats/order', label: '', color: '#000000' };

                if (t === 'totalPrice') {
                    opt.label = (dataParamObj.manager_id ? 'Сумма дохода, ' : 'Сумма заказов, ') +
                        currency_sign;
                    opt.color = '#76c100';
                } else if (t === 'profitPrice') {
                    opt.label = 'Сумма прибыли, ' + currency_sign;
                    opt.color = '#f8a13f';
                } else if (t === 'amount') {
                    opt.label = (dataParamObj.category_id || dataParamObj.product_id) ?
                        'Продано, шт' : 'Колл-во заказов, шт';
                    opt.color = '#000000';
                } else if (t === 'add') {
                    opt.label = 'Поставка, шт';
                    opt.color = '#673ab7';
                } else if (t === 'delete') {
                    opt.label = 'Списано, шт';
                    opt.color = '#f00';
                } else if (t === 'totalPayments') {
                    opt.label = 'Сумма платежей, ' + currency_sign;
                    opt.color = '#f8a13f';
                }

                getChartData(chartData, params, opt);
            });

            if (callback) callback(true);
        });
    };
</script>