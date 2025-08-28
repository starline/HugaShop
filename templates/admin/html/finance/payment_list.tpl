{extends file='wrapper/main.tpl'}
{include file='finance/parts/menu_part.tpl'}

{$meta_title='Финансы'}

{block name=content}

   <div class="two_columns_list">

      <div class="header_top">
         <h1 class="total_amount">
            Баланс:
            {foreach $total_amount as $ta}
               {if $ta->enabled OR $ta->amount > 0}
                  <div class="currency_amount">
                     <span class="sum_total">{$ta->amount|price_html:no_currency:$ta->code|raw} <span
                           class="sum_profit_price">{$ta->sign}</span></span>
                  </div>
               {/if}
            {/foreach}

            <div class="currency_amount">
               <span class="sum_total">{$total_dollars|price_html:no_currency:USD|raw}
                  <span class="sum_profit_price">Всего $</span>
               </span>
            </div>
         </h1>

         {foreach $payments_types as $pt}
            <a class="add {$pt->type}" data-bs-toggle="tooltip" title="Создать {$pt->name}"
               href="{'PaymentNewAdmin'|link:[cur_type => $pt->id]}">{$pt->name}</a>
         {/foreach}
      </div>

      <div id="right_menu" class="finance_menu">

         {if $payments || $keyword}
            <!-- Search -->
            <form method="get" id="search">
               <div class="input-group">
                  <input class="search form-control" type="text" name="keyword" value="{$keyword}"
                     placeholder="В комментариях" />
                  <input class="input-group-text search_button" type="submit" value="" />
               </div>
            </form>
         {/if}

         <select class="form-select" name="payments_type">
            <option value="">Все транзакции</option>
            {foreach $payments_types as $pt}
               {if $pt->id != 2}
                  <option {if $pt->type == $payments_type}selected{/if} value="{$pt->type}">{$pt->name}</option>
               {/if}
            {/foreach}
         </select>

         <select class="form-select" name="category_id">
            <option value="">Все категории</option>
            {if $categories_income|count > 0}
               <option disabled>─── Приход ───</option>
            {/if}
            {foreach $categories_income as $cat}
               <option {if $cat->id == $category_id} selected {/if} value="{$cat->id}">{$cat->name}</option>
            {/foreach}
            {if $categories_expense|count > 0 AND $categories_income|count > 0}
               <option disabled>─── Расход ───</option>
            {/if}
            {foreach $categories_expense as $cat}
               <option {if $cat->id == $category_id} selected {/if} value="{$cat->id}">{$cat->name}</option>
            {/foreach}
         </select>

         <div class="wallets">
            <div class='all {if !$purse_id}selected{/if}'>
               <a href="{url purse_id=null page=null}">Все кошельки</a>
            </div>
            <ul class="menu_list">
               {foreach $purses as $p}
                  <li class='{if $p->id == $purse_id}selected{/if}'>
                     <a href="{url purse_id=$p->id category_id=$category_id clear=true}" data-bs-toggle="tooltip"
                        title="{$p->comment}">{$p->name}</a>
                     <div>{$p->amount|price_html:color:$p->currency_code|raw}</div>
                  </li>
               {/foreach}
            </ul>
         </div>
      </div>



      <div id="main_list" class="finance">


         <div class="grafic">
            <div class="chart_actions btn_row">
               <a class="btn btn-light" id="chart_year">год</a>
               <a class="btn btn-light" id="chart_all">все</a>
               <a class="btn btn-light" id="chart_reset">Reset zoom</a>
            </div>
            <div>
               <div id="financeByMonth"></div>
            </div>
         </div>


         {if $payments}

            {include file='parts/pagination.tpl'}

            <form method="post">
               {getCSRFInput}

               <div class="list">
                  {foreach $payments as $p}
                     <div class="list_row {if !$p->verified}verified_off{else}verified_on{/if}" item_id="{$p->id}">
                        <div class="col">
                           <div class="row">
                              <div class="col-5 col-sm-3 text-end {if $p->related_payment_id}transfer{/if}">
                                 <a
                                    href="{'PaymentAdmin'|link:[id => $p->id]}">{$p->amount|price_html:profit:$p->currency_code|raw}</a>

                                 {if $p->currency_rate!=1 AND !$p->related_payment_id}
                                    <div class="notice">{$p->currency_amount|price_html|raw}</div>
                                 {/if}
                              </div>

                              <div class="col-7 col-sm-9">
                                 <div class="row">
                                    <div class="col-12 col-sm-3 text-sm-end order_date">
                                       <div class="date">{$p->date|date}</div>
                                       <div class="time">{$p->date|time}</div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                       {if $p->category_name}
                                          {$p->category_name}
                                       {else}
                                          Премещение между кошельками
                                       {/if}
                                       <div class="notice">{$p->comment|strip_tags|nl2br|raw}</div>
                                    </div>

                                    <div class="col-12 col-sm-5">
                                       {$p->purse_name}

                                       {if !$p->contractor->entity->name|empty}
                                          <div class="notice">
                                             <a
                                                href="/admin/{$p->contractor->view_name}/{$p->contractor->entity_id}">{$p->contractor->entity->name}</a>
                                          </div>
                                       {/if}
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <div class="icons flex-column">
                           <a class="verified edit" data-bs-toggle="tooltip" title="Cверка с бухгалтерией"></a>

                           {if $p->images|count}
                              <i>
                                 <img loading="lazy" src="{'images/clipboard.png'|asset}" data-bs-toggle="tooltip"
                                    title="Фотоотчет">
                              </i>
                           {/if}
                        </div>

                     </div>
                  {/foreach}
               </div>

            </form>

            {include file='parts/pagination.tpl'}

         {/if}
      </div>

   </div>
{/block}


{block name=body_script append}
   <script type="text/javascript" src="{'js/luxon.js'|asset}"></script>
   <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

   <script type="module">
      import { ajaxEntityUpdateIcon } from '{"js/common.js"|asset}';
      import { makeChart } from '{"js/chart.js"|asset}';

      let stats_url = "{'FinanceStatsAdmin'|link}";
      let php_currency_name = "{$currency->name}";
      let php_currency_sign = "{$currency->sign}";
      let purse_id = "{$purse_id}";
      let category_id = "{$category_id}";

      {literal}

         // Сделать проверенным
         $("a.verified.edit").click(function() {
            ajaxEntityUpdateIcon($(this), 'payment', 'verified');
            return false;
         });

         let current_url = new URL(window.location.href);

         // Select gategory
         $('select[name="category_id"]').change(function() {
            let id = $(this).val();
            if (id != '')
               current_url.searchParams.set('category_id', id);
            else
               current_url.searchParams.delete('category_id');

            current_url.searchParams.delete('page');
            window.location.href = current_url.toString();
         });

         // Select payments_type
         $('select[name="payments_type"]').change(function() {
            var type = $(this).val();
            if (type != '')
               current_url.searchParams.set('payments_type', type);
            else
               current_url.searchParams.delete('payments_type');

            current_url.searchParams.delete('page');
            current_url.searchParams.delete('category_id');
            window.location.href = current_url.toString();
         });


         // Grafic
         let myChart = makeChart(
               document.getElementById('financeByMonth'), {
                  chart: {type: 'bar', height: 250},
                  title: {text: 'Доходы и расходы'
               }
            },
            [{
                  filter: {
                     'filter': 'byMonth',
                     'type': 'plus',
                     'purse_id': purse_id,
                     'category_id': category_id
                  },
                  options: {
                     label: 'Сумма приходов, ' + php_currency_sign,
                     color: '#76c100',
                     url: stats_url,
                     range: 'year'
                  }
               },
               {
                  filter: {
                     'filter': 'byMonth',
                     'type': 'minus',
                     'purse_id': purse_id,
                     'category_id': category_id
                  },
                  options: {
                     label: 'Сумма расходов, ' + php_currency_sign,
                     color: '#f8a13f',
                     url: stats_url,
                     range: 'year'
                  }
               }
            ]
         );

         $('#chart_reset').click(function() {
            if (myChart.chart) myChart.chart.resetSeries()
         });

         $('#chart_year').click(function() {
            myChart.load({ range: 'year' });
         });

         $('#chart_all').click(function() {
            myChart.load({ range: 'all' });
         });

      {/literal}
   </script>
{/block}