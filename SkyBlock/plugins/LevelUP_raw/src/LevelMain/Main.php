<?php
namespace LevelMain;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\scheduler\PluginTask;

class Main extends PluginBase implements Listener {

    public function onEnable() {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."lp/");
        $this->getLogger()->info("
          §7§l=[ §r§a================================================================================== §7§l]=

          §a                                █    ████  █   █  █████  █       
                                            █    █      █ █   █      █       
                                            █    ███    █ █   ███    █       
                                            ███  ████    █    █████  ███      

          §7§l=[ §r§a================================================================================== §7§l]=

                            §ePlugin By Nguyên (được làm dựa theo plugin minelevel bên mysu,ngoài sói ra ai cắp bản quyền bố đánh phát chết luôn :v)
								 
                  ");
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		$config = new Config($this->getDataFolder()."lp/".strtolower($sender->getName()).".yml", Config::YAML);
        $n = $sender->getName();
		if(strtolower($cmd->getName() == "level")){
        $sender->sendMessage("§bLevel:§7 ".$config->get('level'));
        $sender->sendMessage("§bĐKN:§7 ".$config->get('xp')."§9/". $config->get('level')*100);
        $sender->sendMessage("§e==========================");
		}
		if(strtolower($cmd->getName() == "addexp")){
			$sender->sendMessage("§cUsage:§e /addexp§b (số kinh nghiệm)");
			if($sender->isOP()){
			if(is_numeric($args[0])){
				$sender->sendMessage("§e".$args[0]." ĐKN§a đã được thêm vào level của bạn");
		        $config->set('xp', $config->get('xp')+$args[0]);
		        $config->save();
				return true;
			}
			else{
				$sender->sendMessage("§cĐKN phải là số!");
				return true;
			}
		}
		else{
			$sender->sendMessage("§cBạn không có quyền sử dụng lệnh này!");
		}
	}
		if($cmd->getName() == "levelreload"){
			
			$this->reloadConfig();
			$this->saveDefaultConfig();
			
		}
		if($cmd->getName() == "helpsv"){
			$sender->sendMessage("§e§l ====== §bREPORT§e ======");
			$sender->sendMessage("§e§l ====== §dFACTION§e ======");
			$sender->sendMessage("§a/g create (tên muốn đặt)§b để tạo hội");
			$sender->sendMessage("§a/g help§b để xem hướng dẫn");
			$sender->sendMessage("§b/buyec§a để mua Enchant, giá§e 5000 xu = 1 cấp độ");
		}
		return true;
	}

    public function onJoin(PlayerJoinEvent $event){
        $config = new Config($this->getDataFolder()."lp/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);
        $config->save();
        if($config->exists('xp')){
			$event->getPlayer()->setHealth($config->get('level') + 20);
			$event->getPlayer()->setMaxHealth($config->get('level') + 20);
        } else {
            $config->set('level', 1);
			$config->set('xp', 0);
			$config->save();
        }
    }
	public function onChat(PlayerChatEvent $event){
		$config = new Config($this->getDataFolder()."lp/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);
        $config->save();
		$p = $event->getPlayer();
		$n = $p->getName();
		$p->setDisplayName("§7[§bCấp §e". $config->get('level')."§7]§r " .$n);
	}
    public function onMove(PlayerMoveEvent $event){
        $config = new Config($this->getDataFolder()."lp/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);
        $p = $event->getPlayer();
        $n = $p->getName();
       if($config->get('level') <= 300){
           if($config->get('xp') >= $config->get('level')*100 ){
               $config->set('xp',$config->get("xp") - $config->get('level')*100);
               $config->set('level',$config->get('level') + 1);
			   $p->setMaxHealth($config->get('level') + 20);
			   $p->setHealth($config->get('level') + 20);
               $config->save();
           }
       }
    }

    public function onBlockBreak(BlockBreakEvent $event){
        $config = new Config($this->getDataFolder()."lp/".strtolower($event->getPlayer()->getName()).".yml", Config::YAML);
        $b = $event->getBlock()->getId();
        if($b == 56 or $b == 14 or $b == 15 or $b == 16 or $b == 73 or $b == 21){
       if($config->get('level') <= 150){
		   $chang = mt_rand(1,100);
		   switch ($chang){
			   case 10:
               $config->set('xp',$config->get('xp')+3);
               $event->getPlayer()->sendMessage("§b§l+3§e XP");
               $config->save();
			   break;
			   case 50:
			   $config->set('xp',$config->get('xp')+5);
			   $event->getPlayer()->sendMessage("§b§l+5§e XP");
			   $config->save();
			   break;
			   case 70:
			   $config->set('xp',$config->get('xp')+10);
			   $event->getPlayer()->sendMessage("§b§l+10§e XP");
			   $config->save();
			   break;
			   default:
			   $config->set('xp',$config->get('xp')+1);
			   $event->getPlayer()->sendMessage("§b§l+1§e XP");
			   $config->save();
			   break;
		   }
        }
    }
	}
}