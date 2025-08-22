{$subject="Вопрос от пользователя `$feedback->name`"}

<h1 style='font-weight:normal;'>Вопрос от пользователя {$feedback->name}</h1>

<table cellpadding=6 cellspacing=0 style='border-collapse: collapse;'>
    <tr>
        <td style='padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;'>
            Имя
        </td>
        <td style='padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;'>
            {$feedback->name}
        </td>
    </tr>
    <tr>
        <td style='padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;'>
            Email
        </td>
        <td style='padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;'>
            <a href='mailto:{$feedback->email}?subject={$settings->domain}'>{$feedback->email}</a>
        </td>
    </tr>
    <tr>
        <td style='padding:6px; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;'>
            IP
        </td>
        <td style='padding:6px; width:170; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;'>
            {$feedback->ip} (<a href='https://www.ipaddress.com/ipv4/{$feedback->ip}/'>где это?</a>)
        </td>
    </tr>
    <tr>
        <td style='padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;'>
            Сообщение:
        </td>
        <td style='padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;'>
            {$feedback->message|strip_tags|nl2br|raw}
        </td>
    </tr>
</table>