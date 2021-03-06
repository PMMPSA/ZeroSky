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

/**
 * Implementation of the UT3 Query Protocol (GameSpot)
 * Source: http://wiki.unrealadmin.org/UT3_query_protocol
 */
namespace pocketmine\network\query;

use pocketmine\network\AdvancedSourceInterface;
use pocketmine\Server;
use pocketmine\utils\Binary;

class QueryHandler{
	private $server, $lastToken, $token, $longData, $shortData, $timeout;

	public const HANDSHAKE = 9;
	public const STATISTICS = 0;

	public function __construct(){
		$this->server = Server::getInstance();
		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.server.query.start"));
		$addr = $this->server->getIp();
		$port = $this->server->getPort();
		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.server.query.info", [$port]));
		/*
		The Query protocol is built on top of the existing Minecraft PE UDP network stack.
		Because the 0xFE packet does not exist in the MCPE protocol,
		we can identify	Query packets and remove them from the packet queue.

		Then, the Query class handles itself sending the packets in raw form, because
		packets can conflict with the MCPE ones.
		*/

		$this->regenerateToken();
		$this->lastToken = $this->token;
		$this->regenerateInfo();
		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.server.query.running", [$addr, $port]));
	}

	private function debug(string $message) : void{
		//TODO: replace this with a proper prefixed logger
		$this->server->getLogger()->debug("[Query] $message");
	}

	public function regenerateInfo(){
		$ev = $this->server->getQueryInformation();
		$this->longData = $ev->getLongQuery();
		$this->shortData = $ev->getShortQuery();
		$this->timeout = \microtime(\true) + $ev->getTimeout();
	}

	public function regenerateToken(){
		$this->lastToken = $this->token;
		$this->token = \random_bytes(16);
	}

	public static function getTokenString(string $token, string $salt) : int{
		return (\unpack("N", \substr(\hash("sha512", $salt . ":" . $token, \true), 7, 4))[1] << 32 >> 32);
	}

	public function handle(AdvancedSourceInterface $interface, string $address, int $port, string $packet){
		$offset = 2;
		$packetType = \ord($packet{$offset++});
		$sessionID = (\unpack("N", \substr($packet, $offset, 4))[1] << 32 >> 32);
		$offset += 4;
		$payload = \substr($packet, $offset);

		switch($packetType){
			case self::HANDSHAKE: //Handshake
				$reply = \chr(self::HANDSHAKE);
				$reply .= (\pack("N", $sessionID));
				$reply .= self::getTokenString($this->token, $address) . "\x00";

				$interface->sendRawPacket($address, $port, $reply);
				break;
			case self::STATISTICS: //Stat
				$token = (\unpack("N", \substr($payload, 0, 4))[1] << 32 >> 32);
				if($token !== ($t1 = self::getTokenString($this->token, $address)) and $token !== ($t2 = self::getTokenString($this->lastToken, $address))){
					$this->debug("Bad token $token from $address $port, expected $t1 or $t2");
					break;
				}
				$reply = \chr(self::STATISTICS);
				$reply .= (\pack("N", $sessionID));

				if($this->timeout < \microtime(\true)){
					$this->regenerateInfo();
				}

				if(\strlen($payload) === 8){
					$reply .= $this->longData;
				}else{
					$reply .= $this->shortData;
				}
				$interface->sendRawPacket($address, $port, $reply);
				break;
			default:
				$this->debug("Unhandled packet from $address $port: 0x" . \bin2hex($packet));
				break;
		}
	}
}
