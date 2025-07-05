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
               <a class="btn btn-light" id="day_chart_month">месяц</a>
               <a class="btn btn-light" id="day_chart_quarter">квартал</a>
               <a class="btn btn-light" id="day_chart_half_year">полгода</a>
               <a class="btn btn-light" id="day_chart_reset">Reset zoom</a>
            </div>
            <div>
               <div id="stats_byDay" style="height: 350px;"></div>
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
               <a class="btn btn-light" id="month_chart_year">год</a>
               <a class="btn btn-light" id="month_chart_two_years">2 года</a>
               <a class="btn btn-light" id="month_chart_all">все</a>
               <a class="btn btn-light" id="month_chart_reset">Reset zoom</a>
            </div>
            <div>
               <div id="stats_byMonth" style="height: 350px;"></div>
            </div>
         </div>
      </div>
   </div>
{/block}


{block name=body_script append}

   <script type="text/javascript" src="{'js/chart/luxon.js'|asset}"></script>
   <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


   <script type="module">
      import { makeChart } from '{"js/chart.js"|asset}';

      let php_currency_name = '{$currency->name}';
      let php_currency_sign = '{$currency->sign}';

      let now = luxon.DateTime.now();
      var fromDate = now.minus({ months: 2 }).toISODate();

      {literal}
         $(function() {

            let byDay = makeChart(
               document.getElementById('stats_byDay'), {
                  chart: { type: 'bar', height: 350 },
               },
               [{
                     filter: { filter: 'byDay', fromDate: fromDate, csrf: csrf },
                     options: {
                        label: 'Сумма заказов, ' + php_currency_sign,
                        color: '#76c100',
                        type: 'totalPrice',
                        url: '/admin/ajax/stats/order'
                     }
                  },
                  {
                     filter: { filter: 'byDay', fromDate: fromDate, csrf: csrf },
                     options: {
                        label: 'Сумма прибыли, ' + php_currency_sign,
                        color: '#f8a13f',
                        type: 'profitPrice',
                        url: '/admin/ajax/stats/order'
                     }
                  },
                  {
                     filter: { filter: 'byDay', fromDate: fromDate, csrf: csrf },
                     options: {
                        label: 'Колл-во заказов, шт',
                        color: '#000000',
                        type: 'amount',
                        url: '/admin/ajax/stats/order'
                     }
                  }
               ]
            );


            let byMonth = makeChart(
               document.getElementById('stats_byMonth'), {
                  chart: { type: 'bar', height: 350 }
               },
               [{
                     filter: { filter: 'byMonth', csrf: csrf },
                     options: {
                        label: 'Сумма заказов, ' + php_currency_sign,
                        color: '#76c100',
                        type: 'totalPrice',
                        url: '/admin/ajax/stats/order',
                        range: 'year'
                     }
                  },
                  {
                     filter: { filter: 'byMonth', csrf: csrf },
                     options: {
                        label: 'Сумма прибыли, ' + php_currency_sign,
                        color: '#f8a13f',
                        type: 'profitPrice',
                        url: '/admin/ajax/stats/order',
                        range: 'year'
                     }
                  },
                  {
                     filter: { filter: 'byMonth', csrf: csrf },
                     options: {
                        label: 'Колл-во заказов, шт',
                        color: '#000000',
                        type: 'amount',
                        url: '/admin/ajax/stats/order',
                        range: 'year'
                     }
                  }
               ]
            );


            $('select[name="payment_method"]').change(function() {
               let paymentMethod = $('select[name="payment_method"]').val();
               if (paymentMethod) {
                  byMonth.load({ paymentMethod: paymentMethod });
               } else {
                  byMonth.load();
               }
            });

            $('#day_chart_month').click(function() {
               byDay.load({ range: 'month' });
            });
            $('#day_chart_quarter').click(function() {
               byDay.load({ range: 'quarter' });
            });
            $('#day_chart_half_year').click(function() {
               byDay.load({ range: 'half_year' });
            });
            $('#day_chart_reset').click(function() {
               if (byDay.chart) byDay.chart.resetSeries();
            });
            $('#month_chart_reset').click(function() {
               if (byMonth.chart) byMonth.chart.resetSeries();
            });
            $('#month_chart_year').click(function() {
               byMonth.load({ range: 'year' });
            });
            $('#month_chart_two_years').click(function() {
               byMonth.load({ range: 'two_years' });
            });
            $('#month_chart_all').click(function() {
               byMonth.load({ range: 'all' });
            });
         });
      {/literal}
   </script>
{/block}