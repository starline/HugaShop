{extends 'wrapper/main.tpl'}
{include 'finance/parts/menu_part.tpl'}

{$meta_title='График продаж'}

{block name=content}
   <div class="header_top">
      <h1>
         На складе {$total->sum_stock|number} товара
      </h1>

      <span class="sum_total">на сумму
         <span class="amount" data-bs-toggle="tooltip" title="Выручка в розницу">{$total->sum_price|price_html|raw}</span>
         <span class="sum_profit_price" data-bs-toggle="tooltip"
            title="Розничная прибыль">{($total->sum_price - $total->sum_wholesale_price)|price_html:profit|raw}
         </span>
      </span>

      <span class="sum_total">в ассортименте <span class="amount">{$total->products_count|number}
            единиц</span><span class="sum_profit_price"></span>
      </span>

      <span class="sum_total">себестоимостью
         <span class="amount">{$total->sum_wholesale_price|price_html|raw}</span>
      </span>
   </div>

   <div class="row gx-5">
      <div class="col-12">
         <div class="grafic">
            <div class="chart_actions btn_row">
               <a class="btn btn-light" id="day_chart_reset">Reset zoom</a>
            </div>
            <div>
               <canvas id="stats_byDay" height="350" role="img"></canvas>
            </div>
         </div>
      </div>

      <div class="col-12 mt-5">
         <div class="row">
            <h2 class="col-lg-6">Статистика заказов по месяцам</h2>
            <div class="col-lg-6">
               <select class="form-select" name="payment_method" id="payment_method">
                  <option value="">Все способы оплаты</option>
                  {foreach $payment_methods as $payment_method}
                     <option class="{if !$payment_method->enabled}disabled{/if}" value="{$payment_method->id}">
                        {$payment_method->name}
                     </option>
                  {/foreach}
               </select>
            </div>
         </div>

         <div class="grafic">
            <div class="chart_actions btn_row">
               <a class="btn btn-light" id="month_chart_reset">Reset zoom</a>
            </div>
            <div>
               <canvas id="stats_byMonth" height="350" role="img"></canvas>
            </div>
         </div>
      </div>
   </div>
{/block}


{block name=body_script append}

   <script type="text/javascript" src="{'js/chart/chart.umd.js'|asset}"></script>
   <script type="text/javascript" src="{'js/chart/luxon.js'|asset}"></script>
   <script type="text/javascript" src="{'js/chart/chartjs-adapter-luxon.js'|asset}"></script>
   <script type="text/javascript" src="{'js/chart/chartjs-plugin-datalabels.js'|asset}"></script>
   <script type="text/javascript" src="{'js/chart/hammerjs.js'|asset}"></script>
   <script type="text/javascript" src="{'js/chart/chartjs-plugin-zoom.min.js'|asset}"></script>


   <script type="module">
      import { getChartData } from '{"js/common.js"|asset}';

      var csrf = "{setCSRF}";
      let php_currency_name = '{$currency->name}';
      let php_currency_sign = '{$currency->sign}';

      var date = new Date();
      date.setMonth(date.getMonth() - 2); // 2 месяца
      var date_format = date.getDate() + '.' + date.getMonth() + '.' + date.getFullYear(); // 30.08.2020

      {literal}
         $(function() {
            let byDayChart = new Chart(document.getElementById('stats_byDay'), {
               type: 'bar',
               options: {
                  locale: 'ru',
                  maintainAspectRatio: false,
                  plugins: {
                     datalabels: {
                        color: 'black',
                        formatter: function(value) { return value.y; },
                        align: 'top',
                        anchor: 'end',
                        display: 'auto',
                        font: { weight: 'bold' }
                     },
                     zoom: {
                        pan: { enabled: true, mode: 'x', modifierKey: 'ctrl' },
                        zoom: { drag: { enabled: true }, mode: 'x' }
                     },
                     tooltip: { yAlign: 'bottom' }
                  },
                  scales: {
                     x: { type: 'time', time: { unit: 'day', tooltipFormat: 'dd LLL yyyy' } },
                     y: { display: true, title: { display: true, text: php_currency_name } }
                  }
               },
               plugins: [ChartDataLabels]
            });

            let byMonthChart = new Chart(document.getElementById('stats_byMonth'), {
               type: 'bar',
               options: {
                  locale: 'ru',
                  maintainAspectRatio: false,
                  plugins: {
                     datalabels: {
                        color: 'black',
                        formatter: function(value) { return value.y; },
                        align: 'top',
                        anchor: 'end',
                        display: 'auto',
                        font: { weight: 'bold' }
                     },
                     zoom: {
                        pan: { enabled: true, mode: 'x', modifierKey: 'ctrl' },
                        zoom: { drag: { enabled: true }, mode: 'x' }
                     },
                     tooltip: { yAlign: 'bottom' }
                  },
                  scales: {
                     x: {
                        type: 'time',
                        time: {
                           unit: 'month',
                           tooltipFormat: 'MMMM yyyy',
                           displayFormats: { quarter: 'MMM yy' }
                        }
                     },
                     y: { display: true, title: { display: true, text: php_currency_name } }
                  }
               },
               plugins: [ChartDataLabels]
            });

            getChartData(byDayChart, { filter: 'byDay', fromDate: date_format, csrf: csrf }, {
               label: 'Сумма заказов, ' + php_currency_sign,
               color: '#76c100',
               type: 'totalPrice',
               url: '/admin/ajax/stats/order'
            });
            getChartData(byDayChart, { filter: 'byDay', fromDate: date_format, csrf: csrf }, {
               label: 'Сумма прибыли, ' + php_currency_sign,
               color: '#f8a13f',
               type: 'profitPrice',
               url: '/admin/ajax/stats/order'
            });
            getChartData(byDayChart, { filter: 'byDay', fromDate: date_format, csrf: csrf }, {
               label: 'Колл-во заказов, шт',
               color: '#000000',
               type: 'amount',
               url: '/admin/ajax/stats/order'
            });

            function loadMonthChart(paymentMethod = '') {
               byMonthChart.data.datasets = [];
               let base = { filter: 'byMonth', csrf: csrf };
               if (paymentMethod) base.paymentMethod = paymentMethod;

               getChartData(byMonthChart, Object.assign({}, base), {
                  label: 'Сумма заказов, ' + php_currency_sign,
                  color: '#76c100',
                  type: 'totalPrice',
                  url: '/admin/ajax/stats/order'
               });
               getChartData(byMonthChart, Object.assign({}, base), {
                  label: 'Сумма прибыли, ' + php_currency_sign,
                  color: '#f8a13f',
                  type: 'profitPrice',
                  url: '/admin/ajax/stats/order'
               });
               getChartData(byMonthChart, Object.assign({}, base), {
                  label: 'Колл-во заказов, шт',
                  color: '#000000',
                  type: 'amount',
                  url: '/admin/ajax/stats/order'
               });
            }

            loadMonthChart();

            $('select[name="payment_method"]').change(function() {
               let paymentMethod = $('select[name="payment_method"]').val();
               loadMonthChart(paymentMethod);
            });

            $('#day_chart_reset').click(function() { byDayChart.resetZoom(); });
            $('#month_chart_reset').click(function() { byMonthChart.resetZoom(); });
         });
      {/literal}
   </script>
{/block}