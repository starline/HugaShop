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
         <div id="stats_byDay"></div>
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

         <div id="stats_byMonth"></div>
      </div>
   </div>
{/block}


{block name=body_script append}

   {include file='parts/charts_init.tpl'}


   <script type="module">
      var csrf = "{setCSRF}";
      let php_currency_name = '{$currency->name}';
      let php_currency_sign = '{$currency->sign}';

      {literal}
         $(function() {

            var date = new Date();
            date.setMonth(date.getMonth() - 2); // 2 месяца

            // 30.08.2020
            var date_format = date.getDate() + '.' + date.getMonth() + '.' + date.getFullYear()

            // Выводим график
            let my_options = {
               title: {
                  text: 'Статистика заказов'
               },
               subtitle: {
                  text: 'Выручка по дням'
               },
               xAxis: {
                  type: 'datetime',
                  minRange: 7 * 24 * 3600000,
                  maxZoom: 7 * 24 * 3600000,
                  gridLineWidth: 1,
                  ordinal: true,
                  showEmpty: true
               },
               yAxis: {
                  title: {
                     text: php_currency_name
                  }
               }
            }

            // Выводим график по дням
            showStatGraphic(
               'stats_byDay', {
                  fromDate: date_format,
                  filter: 'byDay',
                  'csrf': csrf
               },
               ['totalPrice', 'profitPrice', 'amount'],
               my_options,
               php_currency_sign,
               function(response) {
                  return response;
               }
            );


            // Выводим график по месяцам
            showStatGraphic(
               'stats_byMonth', {
                  filter: 'byMonth',
                  'csrf': csrf
               },
               ['totalPrice', 'profitPrice', 'amount'],
               my_options,
               php_currency_sign,
               function(response) {
                  return response;
               }
            );

            $('select[name="payment_method"]').change(function() {
               let paymentMethod = $('select[name="payment_method"]').val();

               showStatGraphic(
                  'stats_byMonth', {
                     filter: 'byMonth',
                     paymentMethod: paymentMethod,
                     'csrf': csrf
                  },
                  ['totalPrice', 'profitPrice', 'amount'],
                  my_options,
                  php_currency_sign,
                  function(response) {
                     return response;
                  }
               );
            });

         });

      {/literal}
   </script>
{/block}