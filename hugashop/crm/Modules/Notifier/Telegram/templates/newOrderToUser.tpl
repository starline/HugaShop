Ваш заказ <b><a href="{$config->root_url}{'Order'|link:[id => $order->id, order_token => $order->token]}">№{$order->id}</a></b>{PHP_EOL}
<b>Получатель:</b>
{if !$order->name|empty}{$order->name}{PHP_EOL}{/if}
{if !$order->phone|empty}{$order->phone}{PHP_EOL}{/if}
{if !$order->email|empty}{$order->email}{PHP_EOL}{/if}
{if !$order->address|empty}{$order->address}{PHP_EOL}{/if}
{if !$order->comment|empty}{PHP_EOL}<blockquote>{PHP_EOL}<i>{$order->comment}</i>{PHP_EOL}{PHP_EOL}</blockquote>{/if}{PHP_EOL}
{if !$purchases|empty}Товары:
{foreach $purchases as $purchase}<b>{$purchase@index + 1}. {$purchase->product_name}</b> {$purchase->variant_name}{if $purchase->sku} ({$purchase->sku}){/if} - {$purchase->price|price_html:no_html} - {$purchase->amount} {$settings->units}{PHP_EOL}{/foreach}{PHP_EOL}{/if}
{if !$delivery_method->public_name|empty}<b>Доставка:</b> {$delivery_method->public_name}{PHP_EOL}{PHP_EOL}{/if}
{if !$payment_method->public_name|empty}<b>Оплата:</b> {$payment_method->public_name}{PHP_EOL}{PHP_EOL}{/if}
К оплате: <b>{$order->payment_price|price_html:no_html}</b>
{$url = "{$config->root_url}{'Order'|link:[id => $order->id, order_token => $order->token]}"}{$url_text = "Открыть заказ на сайте"}