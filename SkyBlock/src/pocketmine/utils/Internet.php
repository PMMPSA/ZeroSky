<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\utils;

class Internet{
	public static $ip = \false;
	public static $online = \true;

	/**
	 * Gets the External IP using an external service, it is cached
	 *
	 * @param bool $force default false, force IP check even when cached
	 *
	 * @return string|bool
	 */
	public static function getIP(bool $force = \false){
		if(!self::$online){
			return \false;
		}elseif(self::$ip !== \false and !$force){
			return self::$ip;
		}

		$ip = self::getURL("http://api.ipify.org/");
		if($ip !== \false){
			return self::$ip = $ip;
		}

		$ip = self::getURL("http://checkip.dyndns.org/");
		if($ip !== \false and \preg_match('#Current IP Address\: ([0-9a-fA-F\:\.]*)#', \trim(\strip_tags($ip)), $matches) > 0){
			return self::$ip = $matches[1];
		}

		$ip = self::getURL("http://www.checkip.org/");
		if($ip !== \false and \preg_match('#">([0-9a-fA-F\:\.]*)</span>#', $ip, $matches) > 0){
			return self::$ip = $matches[1];
		}

		$ip = self::getURL("http://checkmyip.org/");
		if($ip !== \false and \preg_match('#Your IP address is ([0-9a-fA-F\:\.]*)#', $ip, $matches) > 0){
			return self::$ip = $matches[1];
		}

		$ip = self::getURL("http://ifconfig.me/ip");
		if($ip !== \false and \trim($ip) != ""){
			return self::$ip = \trim($ip);
		}

		return \false;
	}

	/**
	 * GETs an URL using cURL
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
	 *
	 * @param string  $page
	 * @param int     $timeout default 10
	 * @param array   $extraHeaders
	 * @param string  &$err    Will be set to the output of curl_error(). Use this to retrieve errors that occured during the operation.
	 * @param array[] &$headers
	 * @param int     &$httpCode
	 *
	 * @return bool|mixed false if an error occurred, mixed data if successful.
	 */
	public static function getURL(string $page, int $timeout = 10, array $extraHeaders = [], &$err = \null, &$headers = \null, &$httpCode = \null){
		try{
			list($ret, $headers, $httpCode) = self::simpleCurl($page, $timeout, $extraHeaders);
			return $ret;
		}catch(InternetException $ex){
			$err = $ex->getMessage();
			return \false;
		}
	}

	/**
	 * POSTs data to an URL
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
	 *
	 * @param string       $page
	 * @param array|string $args
	 * @param int          $timeout
	 * @param array        $extraHeaders
	 * @param string       &$err Will be set to the output of curl_error(). Use this to retrieve errors that occured during the operation.
	 * @param array[]      &$headers
	 * @param int          &$httpCode
	 *
	 * @return bool|mixed false if an error occurred, mixed data if successful.
	 */
	public static function postURL(string $page, $args, int $timeout = 10, array $extraHeaders = [], &$err = \null, &$headers = \null, &$httpCode = \null){
		try{
			list($ret, $headers, $httpCode) = self::simpleCurl($page, $timeout, $extraHeaders, [
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $args
			]);
			return $ret;
		}catch(InternetException $ex){
			$err = $ex->getMessage();
			return \false;
		}
	}

	/**
	 * General cURL shorthand function.
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
	 *
	 * @param string        $page
	 * @param float|int     $timeout      The maximum connect timeout and timeout in seconds, correct to ms.
	 * @param string[]      $extraHeaders extra headers to send as a plain string array
	 * @param array         $extraOpts    extra CURLOPT_* to set as an [opt => value] map
	 * @param callable|null $onSuccess    function to be called if there is no error. Accepts a resource argument as the cURL handle.
	 *
	 * @return array a plain array of three [result body : string, headers : array[], HTTP response code : int]. Headers are grouped by requests with strtolower(header name) as keys and header value as values
	 *
	 * @throws InternetException if a cURL error occurs
	 */
	public static function simpleCurl(string $page, $timeout = 10, array $extraHeaders = [], array $extraOpts = [], callable $onSuccess = \null){
		if(!self::$online){
			throw new InternetException("Cannot execute web request while offline");
		}

		$ch = \curl_init($page);

		\curl_setopt_array($ch, $extraOpts + [
			CURLOPT_SSL_VERIFYPEER => \false,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_AUTOREFERER => \true,
			CURLOPT_FOLLOWLOCATION => \true,
			CURLOPT_RETURNTRANSFER => \true,
			CURLOPT_CONNECTTIMEOUT_MS => (int) ($timeout * 1000),
			CURLOPT_TIMEOUT_MS => (int) ($timeout * 1000),
			CURLOPT_HTTPHEADER => \array_merge(["User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 " . \pocketmine\NAME], $extraHeaders),
			CURLOPT_HEADER => \true
		]);
		try{
			$raw = \curl_exec($ch);
			$error = \curl_error($ch);
			if($error !== ""){
				throw new InternetException($error);
			}
			$httpCode = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$headerSize = \curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$rawHeaders = \substr($raw, 0, $headerSize);
			$body = \substr($raw, $headerSize);
			$headers = [];
			foreach(\explode("\r\n\r\n", $rawHeaders) as $rawHeaderGroup){
				$headerGroup = [];
				foreach(\explode("\r\n", $rawHeaderGroup) as $line){
					$nameValue = \explode(":", $line, 2);
					if(isset($nameValue[1])){
						$headerGroup[\trim(\strtolower($nameValue[0]))] = \trim($nameValue[1]);
					}
				}
				$headers[] = $headerGroup;
			}
			if($onSuccess !== \null){
				$onSuccess($ch);
			}
			return [$body, $headers, $httpCode];
		}finally{
			\curl_close($ch);
		}
	}
}
