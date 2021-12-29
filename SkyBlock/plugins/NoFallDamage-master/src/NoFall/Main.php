<?php

namespace NoFall;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;

class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function damageHandler(EntityDamageEvent $event){
        $entity = $event->getEntity();
        $cause = $event->getCause();
        if($cause == EntityDamageEvent::CAUSE_FALL){
            $event->setCancelled(true);
        }
        
    }
}