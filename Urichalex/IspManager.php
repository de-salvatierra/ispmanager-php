<?php

namespace Urichalex;

/**
 * Класс для работы с ispManager
 * @author Александр Урих <urichalex@mail.ru>
 * @license Проприетарное программное обеспечение
 * @version 1.0
 */
class IspManager {

	/**
	 * Сформированный URL 
	 * @var string 
	 */
	private $url = null;
	
	/**
	 * IP адрес, назначаемый пользователю
	 * @var string
	 */
	private $ip = NULL;
	
	/**
	 * Логин администратора, имеющего права добавления пользователей
	 * @var string 
	 */
	private $admin;
	
	/**
	 * Пароль администратора ISP
	 * @var string
	 */
	private $pass;
	
	/**
	 * Домен, на котором лежит isp
	 * @var type 
	 */
	private $domain;
	
	/**
	 * Порт ISP. Не обязательно
	 * @var string 
	 */
	private $port;
	
	/**
	 * Шаблон прав пользователей
	 * @var string 
	 */
	private $preset;

	public function __construct($admin, $pass, $preset, $domain = 'localhost', $ip = NULL, $port = NULL)
	{
            $this->domain = $domain;
            $this->port = $port;
            $this->admin  = $admin;
            $this->pass = $pass;
            $this->preset = $preset;
			$this->ip = $ip;
            
            $this->url = 'https://' . $this->domain . ($this->port !== NULL ? ':' . $this->port : '/manager').
                            '/?authinfo='.$this->admin.':'.$this->pass.'&out=json&func=';
	}

	/**
	 * добавляет пользователя в IspManager
	 * @param string $name Имя нового пользователя
	 * @param string $pass Пароль нового пользователя
	 * @param string $email Мыло нового пользователя
	 * @param string $preset Тариф веб хостинга
	 * @param string $domain Домен для пользователя
	 * @param string $ip IP адрес назначаемый пользователю
	 * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
	 */
	public function ispUserAdd($name, $pass, $email, $domain=NULL, $ip = NULL)
	{
		if($ip !== NULL && !filter_var($ip, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV4)))
			return 'Неверно введен IP';
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			return 'Неверно введен Email';

		$params = 'user.edit&sok=yes&name='.
				$name.
				'&passwd='.$pass.
				'&confirm='.$pass.
				'&owner='.$this->admin.
				'&ip='.($ip === NULL ? $this->ip : $ip).
				($domain ? '&domain='.$domain : '').
				'&preset='.$this->preset.
				'&email='.$email;
		return $this->ispQuery($params);
	}

	public function ispPasswd($user, $passwd)
	{

		$params = 'usrparam&sok=yes&su='.$user.'&passwd='.$passwd.'&confirm='.$passwd;
		return $this->ispQuery($params);
	}

		/**
	 * Отключает пользователя ISP
	 * @param string $user Имя пользователя ISPManaget
	 * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
	 */
	public function ispUserDisable($user)
	{
		$params = 'user.disable&elid='.$user;
		return $this->ispQuery($params);
	}

	/**
	 * Включает пользователя ISP
	 * @param string $user Имя пользователя ISPManaget
	 * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
	 */
	public function ispUserEnable($user)
	{
		$params = 'user.enable&elid='.$user;
		return $this->ispQuery($params);
	}

	/**
	 * Удаляет пользователя ISP
	 * @param string $user Имя пользователя ISPManaget
	 * @return bool|string Возвращает true если ошибок нет, или ошибку в xml формате
	 */
	public function ispUserDel($user)
	{
		$params = 'user.delete&elid='.$user;
		return $this->ispQuery($params);
	}

	/**
	 *
	 * @return string Список тарифов на выбранном ISP
	 */
	public function ispGetPreset()
	{
		$params = 'preset';
		return $this->ispQuery($params);
	}

	/**
	 * Совершает запрос в ISP
	 * @param type $params
	 * @return boolean
	 */
	protected function ispQuery($params)
	{
		$url = $this->url . $params;

		$get = file_get_contents($url);

		// Вдруг пароль сменили
		if(trim($get) == 'access deny')
			return 'Ошибка авторизации';

		// Парсим ответ
		$json = json_decode($get);

		// Если есть ошибка, выводим ее
		if(isset($json->error))
			return isset($json->error->msg) ? $json->error->msg : $json->error->code;

		return TRUE;
	}

}

?>
