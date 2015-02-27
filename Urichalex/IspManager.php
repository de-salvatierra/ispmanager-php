<?php

namespace Urichalex;

/**
 * Класс для работы с ispManager
 * @author Александр Урих <urichalex@mail.ru>
 * @license Проприетарное программное обеспечение
 * @version 1.1.1
 */
class IspManager {

    /**
     * Сформированный URL 
     * @var string 
     */
    private $url = null;

    /**
     * @var array Ошибки
     */
    private $errors = [];

    /**
     * IP адрес, назначаемый пользователю
     * @var string
     */
    public $ip = null;

    /**
     * Логин администратора, имеющего права добавления пользователей
     * @var string 
     */
    public $admin;

    /**
     * Пароль администратора ISP
     * @var string
     */
    public $pass;

    /**
     * Домен, на котором лежит isp
     * @var type 
     */
    public $domain;

    /**
     * Порт ISP. Не обязательно
     * @var string 
     */
    public $port;

    public function __construct($admin, $pass, $domain = 'localhost', $ip = null, $port = null) {
        $this->domain = $domain;
        $this->port = $port;
        $this->admin = $admin;
        $this->pass = $pass;
        $this->ip = $ip;

        $this->url = 'https://' . $this->domain . ($this->port !== null ? ':' . $this->port : '/manager') .
                '/?authinfo=' . $this->admin . ':' . $this->pass . '&out=json&func=';
    }

    /**
     * добавляет пользователя в IspManager
     * @param string $name Имя нового пользователя
     * @param string $pass Пароль нового пользователя
     * @param string $email Мыло нового пользователя
     * @param string $preset Шаблон
     * @param string $domain Домен для пользователя
     * @param string $ip IP адрес назначаемый пользователю
     * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
     */
    public function ispUserAdd($name, $pass, $email, $preset, $domain = null, $ip = null) {
        if ($ip !== null && !filter_var($ip, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV4))) {
            $this->errors[] = 'Неверно введен IP';
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Неверно введен Email';
            return false;
        }

        $params = 'user.edit&sok=yes&name=' .
                $name .
                '&passwd=' . $pass .
                '&confirm=' . $pass .
                '&owner=' . $this->admin .
                '&ip=' . ($ip === null ? $this->ip : $ip) .
                ($domain ? '&domain=' . $domain : '') .
                '&preset=' . $preset .
                '&email=' . $email;
        return $this->ispQuery($params);
    }

    public function ispPasswd($user, $passwd) {
        $params = 'usrparam&sok=yes&su=' . $user . '&passwd=' . $passwd . '&confirm=' . $passwd;
        return $this->ispQuery($params);
    }

    /**
     * Отключает пользователя ISP
     * @param string $user Имя пользователя ISPManaget
     * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
     */
    public function ispUserDisable($user) {
        $params = 'user.disable&elid=' . $user;
        return $this->ispQuery($params);
    }

    /**
     * Включает пользователя ISP
     * @param string $user Имя пользователя ISPManaget
     * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
     */
    public function ispUserEnable($user) {
        $params = 'user.enable&elid=' . $user;
        return $this->ispQuery($params);
    }

    /**
     * Удаляет пользователя ISP
     * @param string $user Имя пользователя ISPManaget
     * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
     */
    public function ispUserDel($user) {
        $params = 'user.delete&elid=' . $user;
        return $this->ispQuery($params);
    }

    /**
     * Список тарифов на выбранном ISP
     * @return string
     */
    public function ispGetPreset() {
        $params = 'preset';
        return $this->ispQuery($params);
    }

    /**
     * Проверяет наличие ошибок
     * @return boolean
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Отдает ошибки
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Совершает запрос в ISP
     * @param type $params
     * @return boolean
     */
    protected function ispQuery($params) {
        $url = $this->url . $params;

        $get = file_get_contents($url);

        // Вдруг пароль сменили
        if (trim($get) == 'access deny') {
            $this->errors[] = 'Ошибка авторизации';
            return false;
        }

        // Парсим ответ
        $json = json_decode($get);

        // Если есть ошибка, выводим ее
        if (isset($json->error)) {
            if(isset($json->error->msg) && trim($json->error->msg)) {
                $this->errors[] = $json->error->msg;
            } else {
                $this->errors[] = self::getError($json->error->code);
            }
            return false;
        }
        return true;
    }
    
    /**
	 * Конвертация ошибок ISP в текст
	 * @param integer $code
	 * @return string
	 */
	protected static function getError($code)
	{
		switch($code)
		{
			case 2:
				return 'Пользователь уже существует в ISP';
			case 3:
				return 'Пользователя не существует в ISP';
			case 4:
				return 'Не все параметры переданы';
            case 6:
				return 'Неверный IP адрес, назначаемый пользователю';
			case 8:
				return 'Домашняя папка пользователя уже существует';
		}

		return 'Неизвестная ошибка';
	}

}
