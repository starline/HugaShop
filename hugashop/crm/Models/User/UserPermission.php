<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.0
 *
 * Тут работает с правами пользователя
 *
 */

namespace HugaShop\Models\User;

use HugaShop\Models\BaseModel;

class UserPermission extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'user_id' =>        ['type' => 'int',           'req' => true],
        'name' =>           ['type' => 'varchar',       'req' => true]

    ];

    protected static $table_keys = [
        'user_id' => ['column' => ['user_id'],    'type' => 'index']
    ];

    // Виды прав доступа
    public static $permissions_list = [
        'super_user'            => 'Полный доступ',

        'order'                 => 'Заказы - Создание, редактирование своих',
        'order_view_all'        => 'Заказы - Просмотр всего списка',
        'order_edit'            => 'Заказы - Редактирование всех',
        'order_delete'          => 'Заказы - Удаление',
        'order_label'           => 'Заказы - Метками - Управление',
        'order_finance'         => 'Заказы - Показать Финансы',
        'order_delivery'        => 'Заказы - Доставка - Управление',
        'order_payment'         => 'Заказы - Оплата - Управленине',

        'product_view'          => 'Товары - Просмотр списка',
        'product_content'       => 'Товары - Редактирование контента',
        'product_delete'        => 'Товары - Удаление',
        'product_price'         => 'Товары - Цены, аналитика',
        'product_import'        => 'Товары - Импорт цен',
        'product_marking'       => 'Товары - Маркировка',
        'product_category'      => 'Товары - Категории',
        'product_feature'       => 'Товары - Характеристики',

        'product_brand'         => 'Товары - Бренды - Добавлние, просмотр, редактирование',
        'product_brand_delete'  => 'Товары - Бренды - Удаление',

        'warehouse'             => 'Поставки - Просмотр',
        'warehouse_add'         => 'Поставки - Добавить',
        'warehouse_edit'        => 'Поставки - Редактирование',
        'warehouse_provider'    => 'Поставки - Поставщики',
        'warehouse_place'       => 'Поставки - Склады',

        'user'                  => 'Покупатели - Просмотр',
        'user_edit'             => 'Покупатели - Редактирование',
        'user_delete'           => 'Покупатели - Удаление',
        'user_manager'          => 'Покупатели - Управление Сотрудниками',
        'user_group'            => 'Покупатели - Группы Просмотр',
        'user_group_edit'       => 'Покупатели - Группы Редактирование',
        'user_group_delete'     => 'Покупатели - Группы Удаление',
        'user_coupon'           => 'Покупатели - Купоны',
        'user_notifier'         => 'Покупатели - Оповещения',
        'user_settings'         => 'Покупатели - Управление настройками',

        'page'                  => 'Контент - Страницы',
        'blog'                  => 'Контент - Блог',
        'comment'               => 'Контент - Комментарии',

        'finance'               => 'Финансы',
        'stats'                 => 'Статистика',

        'export'                => 'Экспорт',
        'backup'                => 'Бекап',
        'design'                => 'Дизайн',
        'settings'              => 'Настройки сайта',
        'addon'             => 'Модули расширения'
    ];


    /**
     * Выбираем названия прав доступа пользователя
     * @param int $user_id
     */
    public static function getUserPermissionsName(int $user_id)
    {
        return self::where('user_id', $user_id)->pluck('name')->toArray();
    }


    /**
     * Обновляем настройки доступа
     * @param int $user_id
     * @param ?array $permissions
     */
    public static function updatePermissions(int $user_id, ?array $permissions = [])
    {

        // Delete all permissions
        self::deleteBy('user_id', $user_id);

        if (!empty($permissions) && is_array($permissions)) {
            $values = [];

            foreach ($permissions as $perm) {
                if (!empty($perm)) {
                    $values[] = ['user_id' => $user_id, 'name' => $perm];
                }
            }

            return self::insert($values);
        }
        return true;
    }


    /**
     * Check user access

     * @param array|string $access_type
     * @param ?object $user
     */
    public static function checkAccess(array|string $access_type, ?object $user = null): bool
    {

        if (empty($user) and !empty(User::authUser('permissions'))) {
            $permissions = User::authUser('permissions');
        } elseif (!empty($user->permissions)) {
            $permissions = $user->permissions;
        }

        if (empty($permissions)) {
            return false;
        }

        if (is_array($access_type)) {
            foreach ($access_type as $access_one) {
                if (in_array($access_one, $permissions)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($access_type, $permissions);
    }
}
