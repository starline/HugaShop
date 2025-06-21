<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 *
 * Тут работает с данными пользователей
 *
 */

namespace HugaShop\Api\User;

use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\BaseModel;
use HugaShop\Api\Order\Order;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class User extends BaseModel
{

    public static $table_fields = [
        'id' =>             ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>           ['type' => 'varchar',       'req' => true],
        'email' =>          ['type' => 'varchar'],
        'phone' =>          ['type' => 'varchar'],
        'comment' =>        ['type' => 'varchar'],
        'manager' =>        ['type' => 'tinyint',       'access' => 'user_manager'],
        'token' =>          ['type' => 'varchar',       'access' => false],
        'te_chat_id' =>     ['type' => 'int',           'access' => false],
        'te_name' =>        ['type' => 'varchar',       'access' => false],
        'password' =>       ['type' => 'varchar'],
        'last_ip' =>        ['type' => 'varchar',       'access' => false],
        'enabled' =>        ['type' => 'tinyint',       'access' => 'user_edit'],
        'group_id' =>       ['type' => 'int',           'access' => 'user_group'],
        'created' =>        ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP',   'access' => false]
    ];

    public static $auth_user;
    public static $cookie_uid = 'UID';

    public function group()
    {
        return $this->belongsTo(UserGroup::class, 'group_id', 'id');
    }


    /**
     * Get Users List
     * @param array $filter
     * @param bool $count
     */
    public static function getUsers(array $filter = [], bool $count = false): Collection|int
    {
        $query = User::query()->with(['group:id,discount,name']);

        // Фильтр по group_id
        if (!empty($filter['group_id'])) {
            $query->whereIn('group_id', (array) $filter['group_id']);
        }

        // Фильтр по manager
        if (isset($filter['manager'])) {
            $query->where('manager', (int) $filter['manager']);
        }

        // Поиск по ключевому слову
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            $query->where(function (Builder $q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%$word%")
                            ->orWhere('email', 'like', "%$word%")
                            ->orWhere('last_ip', 'like', "%$word%")
                            ->orWhere('phone', 'like', "%$word%");
                    });
                }
            });
        }

        // Сортировка
        $sort = $filter['sort'] ?? null;
        match ($sort) {
            'date'    => $query->orderBy('created_at', 'desc'),
            'manager' => $query->orderBy('manager', 'desc'),
            'name'    => $query->orderBy('name'),
            default   => $query->orderBy('name'),
        };

        // Подсчёт
        if ($count) {
            return $query->count();
        }

        // Лимит и пагинация
        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->skip(($page - 1) * $limit)->take($limit);
        }

        return $query->get();
    }


    /**
     * Выбираем общее кол-во user
     * @param array $filter
     */
    public static function countUsers(array $filter = []): int
    {
        return User::getUsers($filter, count: true);
    }


    /**
     * Выбираем пользователя с базы
     * @param int|array $id - ID|Email|Phone
     */
    public static function getUser(int|array $id): User|false
    {
        if (empty($id)) {
            return false;
        }

        $query = User::with(['group:id,discount,name']); # Подгружаем группу с нужными полями

        if (is_array($id)) {
            if (!empty($id['token'])) {
                $query->where('token', $id['token']);
            } elseif (!empty($id['email']) && substr_count($id['email'], '@') === 1) {
                $query->where('email', $id['email']);
            } elseif (!empty($id['phone'])) {
                $cleanPhone = Helper::clearPhoneNummber($id['phone']);
                $query->where('phone', 'like', '%' . $cleanPhone . '%');
            } else {
                return false;
            }
        } else {
            $query->where('id', intval($id));
        }

        $user = $query->first();

        if (!$user) {
            return false;
        }

        if (!empty($user->group->discount)) {
            $user->group->discount *= 1; # Убираем лишние нули, чтобы было 5 вместо 5.00
        }

        return $user;
    }


    /**
     * Check email exists
     * @param string $email
     * @param int $except_user_id
     */
    public static function checkEmailExists(string $email, ?int $except_user_id = null): bool
    {
        $query = User::where('email', $email);

        if (!empty($except_user_id)) {
            $query->where('id', '!=', $except_user_id);
        }

        return $query->exists();
    }


    /**
     * Добавляем нового пользователя
     * @param $user
     */
    public static function addUser($user)
    {

        // Шифруем пароль
        if (isset($user->password)) {
            $user->password = User::makePasswordHash($user->password);
        }

        // Убираем пробелы в номере телефона и добавляем +38
        if (isset($user->phone)) {
            $user->phone = Helper::clearPhoneNummber($user->phone);
        }

        // Если такой email есть, не добавляем
        if (!empty($user->email)) {
            if (User::checkEmailExists($user->email) > 0) {
                return false;
            }
        }

        // Определяем IP
        $user->last_ip = $_SERVER['REMOTE_ADDR'];
        $user->token = Helper::makeToken(uniqid(), 16);

        return User::create($user)->id();
    }


    /**
     * Обновляем данные пользователя
     * @param int $id
     * @param object $user
     */
    public static function updateUser(int $id, object|array $user)
    {
        if (empty($user)) {
            return false;
        }

        // Убираем пробелы в номере телефона и добавляем +38
        if (!empty($user->phone)) {
            $user->phone = Helper::clearPhoneNummber($user->phone);
        }

        // TODO: проверять старый пароль
        if (!empty($user->password)) {
            $user->password = self::makePasswordHash($user->password);
        }

        return User::updateOne($id, $user);
    }


    /**
     * Удалить пользователя
     * @param int $id - ID пользователя
     */
    public static function deleteUser(int $id)
    {
        Order::updateOne(['user_id' => $id], ['user_id' => null]);
        Order::updateOne(['manager_id' => $id], ['manager_id' => null]);

        return User::deleteOne($id);
    }


    /**
     * Проверяем пароль пользователя
     * @param ?string $email
     * @param ?string $password
     */
    public static function checkPassword(?string $email, ?string $password)
    {
        if (empty($password) || empty($email)) {
            return false;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        if (!self::verifyPassword($password, $user->password, $user->id)) {
            return false;
        }

        return $user->id;
    }


    /**
     * Generate remember me key.
     * 
     * TODO: save token in DB
     * 
     * @param int $id
     * @return string
     */
    public static function generateRememberMeKey(int $user_id): string
    {
        $key = 'rememberme_uid_' . $user_id;
        return md5($key);
    }


    /**
     * Check remember me key.
     * @param string $key
     * @param int $id
     * @return bool
     */
    public static function checkRememberMeKey(string $key, int $user_id): bool
    {
        return $key == User::generateRememberMeKey($user_id);
    }


    /**
     * Set remember me cookie.
     * Example: 12.token
     * @param int $id
     * @return void
     */
    public static function setRememberMeCookie(int $user_id): void
    {
        $data = $user_id . '.' . User::generateRememberMeKey($user_id);
        Request::setCookie(self::$cookie_uid, $data, 360);
    }


    /**
     * Check remember me cookie.
     * Does autologin.
     * @return bool|int
     */
    public static function checkRememberMeCookie(): bool|int
    {
        if (empty($cookie_uid = Request::getCookie(self::$cookie_uid))) {
            return false;
        }

        $cookie_uid_arr = explode('.', $cookie_uid);

        $uid = $cookie_uid_arr[0] ?? null;
        $key = $cookie_uid_arr[1] ?? null;

        if (isset($uid) && isset($key)) {
            if (User::checkRememberMeKey($key, $uid) === true) {
                Request::setSession('user_id', $uid);
                return $uid;
            }
        }

        return false;
    }


    /**
     * Check remind code
     * @param string $code
     */
    public static function checkRemindCode(string $code): bool
    {
        // Проверяем существование сессии
        if (empty(Request::getSession('password_remind_code')) || empty(Request::getSession('password_remind_user_id'))) {
            return false;
        }

        // Проверяем совпадение кода в сессии и в ссылке
        if ($code != Request::getSession('password_remind_code')) {
            return false;
        }

        // Выбераем пользователя из базы
        if (empty($user = User::getUser(Request::getSession('password_remind_user_id')))) {
            return false;
        }

        // Залогиниваемся под пользователем
        Request::setSession('user_id', $user->id);
        return true;
    }


    /**
     * Make password hash
     * @param string $password
     */
    public static function makePasswordHash(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Legacy MD5 password hash
     * @param string $password
     * @return string
     */
    protected static function makeLegacyPasswordHash(string $password): string
    {
        return md5(Config::get('salt_psw') . $password . md5($password));
    }

    /**
     * Verify password and migrate old hashes when needed
     *
     * @param string $password Plain text password
     * @param string $hash Stored hash
     * @param int|null $user_id User id for migration
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash, ?int $user_id = null): bool
    {
        if (password_verify($password, $hash)) {
            if (password_needs_rehash($hash, PASSWORD_BCRYPT) && $user_id !== null) {
                self::updateUser($user_id, ['password' => $password]);
            }
            return true;
        }

        // Fallback to legacy MD5
        if ($hash === self::makeLegacyPasswordHash($password)) {
            if ($user_id !== null) {
                self::updateUser($user_id, ['password' => $password]);
            }
            return true;
        }

        return false;
    }


    /**
     * Check user is logged
     */
    public static function isLoggedIn(): bool|int
    {
        if (!empty(self::$auth_user)) {
            return self::$auth_user->id;
        }

        if (empty($user_id = Request::getSession('user_id'))) {
            if (empty($user_id = User::checkRememberMeCookie())) { # If session is over, check Cookie)
                return false;
            }
        }

        return $user_id;
    }


    /**
     * Get Auth user
     * @param string $param
     */
    public static function authUser(?string $option = null)
    {

        // If user is logged In
        if (empty(self::$auth_user) and !empty($user_id = self::isLoggedIn())) {

            $user = User::getOne($user_id, 'group');

            if (!empty($user) && $user->enabled == 1) {
                if ($user->manager == 1) {
                    $user->permissions = UserPermission::getUserPermissionsName($user->id);
                }
                self::$auth_user = $user;
            }
        }

        if (!empty(self::$auth_user)) {
            if (!empty($option)) {
                return self::$auth_user->{$option};
            }
            return self::$auth_user;
        }

        return null;
    }
}
