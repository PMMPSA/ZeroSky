<?php
namespace AntiServerStop;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

/**
 *  ____     ______    ______    _________   _________     _______
 * |  _ \   |  __  |  |  ____|  |___   ___| |___   ___|   |__   __|
 * | |_) /  | |__| |  | |____       | |         | |          | |
 * |  __/   |  __  |  |  ____|      | |         | |          | |
 * | |      | |  | |  | |____       | |         | |        __| |__
 * |_|      |_|  |_|  |______|      |_|         |_|       |_______|
 * Plugin coded by paetti.
 * This Label is by paetti.
**/
class Main extends PluginBase implements Listener{
  
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->getLogger()->info(TextFormat::BLUE . "AntiServerStop by paetti loaded.");
    }
    
    public function onDisable(){
        $this->getLogger()->info(TextFormat::BLUE . "AntiServerStop disabled.");
    }
public function onCommandPreProcess(PlayerCommandPreprocessEvent $event){

	

 $args = explode(" ", $event->getMessage());

if($args[0] == "/stop"){
     
if (!($event->getPlayer() instanceof Player)){ 

 return true;
} else {
    $event->getPlayer()->sendMessage(TextFormat::DARK_RED."tuổi lol tắt sv t :>>");
$event->setCancelled();
}


}
}

       public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool {
        switch($command->getName()){

            
            case "antiserverstop":

$sender->sendMessage(TextFormat::GREEN."AntiServerStop v1.1 coded by paetti. Kik: Oupsay");
$sender->sendMessage(TextFormat::GREEN."YouTube: paetti");


return true;

}
}
}
