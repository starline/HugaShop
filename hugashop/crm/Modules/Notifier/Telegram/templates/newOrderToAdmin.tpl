Заказ <b><a href="{$config->root_url}{'OrderAdmin'|link:[id => $order->id]}">№{$order->id}</a></b> - {$order->date|date} {$order->date|time}{PHP_EOL}
<b>Заказчик:</b>
{if !$order->name|empty}{$order->name}{PHP_EOL}{/if}
{if !$order->phone|empty}{$order->phone}{PHP_EOL}{/if}
{if !$order->email|empty}{$order->email}{PHP_EOL}{/if}
{if !$order->address|empty}{$order->address}{PHP_EOL}{/if}
{if !$order->comment|empty}{PHP_EOL}<blockquote>{PHP_EOL}<i>{$order->comment}</i>{PHP_EOL}{PHP_EOL}</blockquote>{/if}{PHP_EOL}
{if !$purchases|empty}Товары:
{foreach $purchases as $purchase}<b>{$purchase@index + 1}. {$purchase->product_name}</b> {$purchase->variant_name}{if $purchase->sku} ({$purchase->sku}){/if} - {$purchase->price|price_html:no_html} - {$purchase->amount} {$settings->units}{PHP_EOL}{/foreach}{PHP_EOL}{/if}
{if !$delivery_method->name|empty}<b>Доставка:</b> {$delivery_method->name}{PHP_EOL}{PHP_EOL}{/if}
{if !$payment_method->name|empty}<b>Оплата:</b> {$payment_method->name}{PHP_EOL}{PHP_EOL}{/if}
К оплате: <b>{$order->payment_price|price_html:no_html}</b>
{$url = "{$config->root_url}{'OrderAdmin'|link:[id => $order->id]}"}{$url_text = "Открыть заказ на сайте"}