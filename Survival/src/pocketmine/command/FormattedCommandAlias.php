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

namespace pocketmine\command;

use pocketmine\lang\TranslationContainer;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FormattedCommandAlias extends Command{
	private $formatStrings = [];

	/**
	 * @param string   $alias
	 * @param string[] $formatStrings
	 */
	public function __construct(string $alias, array $formatStrings){
		parent::__construct($alias);
		$this->formatStrings = $formatStrings;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){

		$commands = [];
		$result = \false;

		foreach($this->formatStrings as $formatString){
			try{
				$commands[] = $this->buildCommand($formatString, $args);
			}catch(\InvalidArgumentException $e){
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
				return \false;
			}catch(\Throwable $e){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.exception"));
				$sender->getServer()->getLogger()->logException($e);

				return \false;
			}
		}

		foreach($commands as $command){
			$result |= Server::getInstance()->dispatchCommand($sender, $command);
		}

		return (bool) $result;
	}

	/**
	 * @param string $formatString
	 * @param array  $args
	 *
	 * @return string
	 */
	private function buildCommand(string $formatString, array $args) : string{
		$index = \strpos($formatString, '$');
		while($index !== \false){
			$start = $index;
			if($index > 0 and $formatString{$start - 1} === "\\"){
				$formatString = \substr($formatString, 0, $start - 1) . \substr($formatString, $start);
				$index = \strpos($formatString, '$', $index);
				continue;
			}

			$required = \false;
			if($formatString{$index + 1} == '$'){
				$required = \true;

				++$index;
			}

			++$index;

			$argStart = $index;

			while($index < \strlen($formatString) and self::inRange(\ord($formatString{$index}) - 48, 0, 9)){
				++$index;
			}

			if($argStart === $index){
				throw new \InvalidArgumentException("Invalid replacement token");
			}

			$position = (int) \substr($formatString, $argStart, $index);

			if($position === 0){
				throw new \InvalidArgumentException("Invalid replacement token");
			}

			--$position;

			$rest = \false;

			if($index < \strlen($formatString) and $formatString{$index} === "-"){
				$rest = \true;
				++$index;
			}

			$end = $index;

			if($required and $position >= \count($args)){
				throw new \InvalidArgumentException("Missing required argument " . ($position + 1));
			}

			$replacement = "";
			if($rest and $position < \count($args)){
				for($i = $position, $c = \count($args); $i < $c; ++$i){
					if($i !== $position){
						$replacement .= " ";
					}

					$replacement .= $args[$i];
				}
			}elseif($position < \count($args)){
				$replacement .= $args[$position];
			}

			$formatString = \substr($formatString, 0, $start) . $replacement . \substr($formatString, $end);

			$index = $start + \strlen($replacement);

			$index = \strpos($formatString, '$', $index);
		}

		return $formatString;
	}

	/**
	 * @param int $i
	 * @param int $j
	 * @param int $k
	 *
	 * @return bool
	 */
	private static function inRange(int $i, int $j, int $k) : bool{
		return $i >= $j and $i <= $k;
	}
}
