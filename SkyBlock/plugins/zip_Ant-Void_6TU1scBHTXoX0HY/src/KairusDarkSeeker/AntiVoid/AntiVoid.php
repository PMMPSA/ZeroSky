<?php

namespace KairusDarkSeeker\AntiVoid;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\Config;

class AntiVoid extends PluginBase implements Listener {
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info("§aAntiVoid by KairusDarkSeeker was enabled!");
	}
	
	public function onDisable() {
		$this->getServer()->getLogger()->info("§cAntiVoid by KairusDarkSeeker was disabled!");
	}
	
	public function onMove(PlayerMoveEvent $event) {
		if($event->getPlayer()->getY() < -5) {
			$event->getPlayer()->teleport($event->getPlayer()->getLevel()->getSafeSpawn());
		}
	}
	
	public function onDamage(EntityDamageEvent $event) {
		if($event->getEntity() instanceof Player && $event->getEntity()->getY() < 0) {
			$event->setCancelled();
		}
	}
}