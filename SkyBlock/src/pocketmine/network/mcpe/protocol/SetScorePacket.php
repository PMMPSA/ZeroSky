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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\utils\Binary;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class SetScorePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::SET_SCORE_PACKET;

	public const TYPE_MODIFY_SCORE = 0;
	public const TYPE_RESET_SCORE = 1;

	/** @var int */
	public $type;
	/** @var ScorePacketEntry[] */
	public $entries = [];

	protected function decodePayload(){
		$this->type = (\ord($this->get(1)));
		for($i = 0, $i2 = $this->getUnsignedVarInt(); $i < $i2; ++$i){
			$entry = new ScorePacketEntry();
			$entry->uuid = $this->getUUID();
			$entry->objectiveName = $this->getString();
			$entry->score = ((\unpack("V", $this->get(4))[1] << 32 >> 32));
		}
	}

	protected function encodePayload(){
		($this->buffer .= \chr($this->type));
		$this->putUnsignedVarInt(\count($this->entries));
		foreach($this->entries as $entry){
			$this->putUUID($entry->uuid);
			$this->putString($entry->objectiveName);
			($this->buffer .= (\pack("V", $entry->score)));
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleSetScore($this);
	}
}
