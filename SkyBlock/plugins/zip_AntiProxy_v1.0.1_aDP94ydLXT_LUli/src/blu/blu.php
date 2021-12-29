<?php

namespace Blu;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class Blu extends PluginBase implements Listener {
	public function onEnable()
	{
		$this->getLogger()->info("§b§lAntiProxy§r§2 enabled!");
		$this->getLogger()->info("§b§lYouTube: Blux");
		
	}
	public function onReceive(DataPacketReceiveEvent $event)
	{
		$reason = "§bCheats §rDetectet";
		$player = $event->getPlayer();
		$ip = $player->getAddress();
		$packet = $event->getPacket();
		if ($packet instanceof LoginPacket) {
			if ($packet->serverAddress === "mcpeproxy.tk" or $packet->serverAddress === "165.227.79.111") {
				$server->getIPBans()->addBan($ip, $reason, null);
			}
			if ($packet->clientId === 0) {
				$player = $event->getPlayer();
				$server->getIPBans()->addBan($ip, $reason, null);
			}
		}
	}	
}
?>