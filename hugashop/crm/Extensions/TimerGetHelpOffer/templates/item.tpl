{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title="Заявка #{$request->id}"}

{block name=content }
    <div class="header_top">
        <h1>Заявка #{$request->id}</h1>
    </div>
    <div class="row gx-5">
        <div class="col-lg-6">
            <ul class="property_block">
                <li>
                    <div class="col-form-label">Имя:</div>
                    <div class="col-form-label">{$request->name}</div>
                </li>
                <li>
                    <div class="col-form-label">Телефон:</div>
                    <div class="col-form-label">{$request->phone}</div>
                </li>
                <li>
                    <div class="col-form-label">Email:</div>
                    <div class="col-form-label">{$request->email}</div>
                </li>
                <li>
                    <div class="col-form-label">Дата:</div>
                    <div class="col-form-label">{$request->created_at|date} {$request->created_at|time}</div>
                </li>

                <li>
                    <div class="col-form-label"></div>
                    <div class="col-form-label">
                        <div class="list-group">
                            <div class="list-group-item">
                                <div>IP: {$request->ip}</div>
                                <div>
                                    <a class="badge text-bg-secondary" href='https://www.ipaddress.com/ipv4/{$request->ip}'
                                        target="_blank">где это?</a>
                                </div>
                            </div>

                            {if !$request->user_agent|empty}
                                <div class="list-group-item">
                                    <div class="col-6">{$request->user_agent->os} {$request->user_agent->os_version}</div>
                                    <span class="badge text-bg-secondary">{$request->user_agent->device_type}</span>
                                </div>
                                <div class="list-group-item">
                                    <div class="col-6">{$request->user_agent->browser}</div>
                                    <span class="badge text-bg-secondary">{$request->user_agent->device}</span>
                                </div>
                            {/if}
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
{/block}