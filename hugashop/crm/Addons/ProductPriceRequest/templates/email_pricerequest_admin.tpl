{$subject="Запрос на скидку по товару `$product->name`"}

<h1 style='font-weight:normal;'>Запрос на снижение цены</h1>
<table cellpadding=6 cellspacing=0 style='border-collapse: collapse;'>
    <tr>
        <td style='padding:6px;width:170;background-color:#f0f0f0;border:1px solid #e0e0e0;font-family:arial;'>Товар
        </td>
        <td style='padding:6px;width:330;background-color:#ffffff;border:1px solid #e0e0e0;font-family:arial;'>
            <a href='{'Product'|link:[url => $product->url]}'>{$product->name}</a>
        </td>
    </tr>
    <tr>
        <td style='padding:6px;background-color:#f0f0f0;border:1px solid #e0e0e0;font-family:arial;'>ФИО</td>
        <td style='padding:6px;background-color:#ffffff;border:1px solid #e0e0e0;font-family:arial;'>{$request->name}
        </td>
    </tr>
    <tr>
        <td style='padding:6px;background-color:#f0f0f0;border:1px solid #e0e0e0;font-family:arial;'>Телефон</td>
        <td style='padding:6px;background-color:#ffffff;border:1px solid #e0e0e0;font-family:arial;'>{$request->phone}
        </td>
    </tr>
    <tr>
        <td style='padding:6px;background-color:#f0f0f0;border:1px solid #e0e0e0;font-family:arial;'>Email</td>
        <td style='padding:6px;background-color:#ffffff;border:1px solid #e0e0e0;font-family:arial;'><a
                href='mailto:{$request->email}?subject={$settings->domain}'>{$request->email}</a></td>
    </tr>
    <tr>
        <td style='padding:6px;background-color:#f0f0f0;border:1px solid #e0e0e0;font-family:arial;'>Ссылка</td>
        <td style='padding:6px;background-color:#ffffff;border:1px solid #e0e0e0;font-family:arial;'><a
                href="{$request->link}">{$request->link}</a></td>
    </tr>
    <tr>
        <td style='padding:6px;background-color:#f0f0f0;border:1px solid #e0e0e0;font-family:arial;'>IP</td>
        <td style='padding:6px;background-color:#ffffff;border:1px solid #e0e0e0;font-family:arial;'>{$request->ip}</td>
    </tr>
</table>