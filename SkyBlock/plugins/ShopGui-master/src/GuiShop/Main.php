<?php

namespace GuiShop;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\{Item, ItemBlock};
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\event\server\DataPacketReceiveEvent;
use GuiShop\Modals\elements\{Dropdown, Input, Button, Label, Slider, StepSlider, Toggle};
use GuiShop\Modals\network\{GuiDataPickItemPacket, ModalFormRequestPacket, ModalFormResponsePacket, ServerSettingsRequestPacket, ServerSettingsResponsePacket};
use GuiShop\Modals\windows\{CustomForm, ModalWindow, SimpleForm};
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender, CommandExecutor};

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {
  public $shop;
  public $item;

  //documentation for setting up the items
  /*
  "Item name" => [item_id, item_damage, buy_price, sell_price]
  */
public $Blocks = [
    "ICON" => ["Blocks",2,0],
    "Oak Wood" => [17,0,50,10],
    "Birch Wood" => [17,2,50,10],
    "Spruce Wood" => [17,1,50,10],
    "Acacia Wood" => [162,0,50,10],
	"Cobblestone" => [4,0,10,2],
	"Grass" => [2,0,5,1],
	"Obsidian" => [49,0,100,25],
	"Sand " => [12,0,150,45],
    "Sandstone " => [24,0,100,50],
	"Nether Rack" => [87,0,500,10],
    "Glass" => [20,0,150,100],
    "Glowstone" => [89,0,100,10],
    "Sea Lantern" => [169,0,100,50],
	"Grass" => [2,0,50,15],
	"Dirt" => [3,0,10,5],
    "Stone" => [1,0,200,2.5],
    "Oak Wood Planks" => [5,0,20,5],
    "Prismarine" => [168,0,300,200],
    "End Stone" => [121,0,200,30],
    "Emerald Block" => [133,0,350,10],
    "Diamond Block" => [57,0,300,10],
    "Iron Block" => [42,0,299,15],
    "Gold Block" => [41,0,299,15],
    "Purpur Blocks" => [201,0,201,6],
	"Bone Block" => [216,0,201,50],
	"Oak Leaves" => [18,0,20,5],
	"White Wool" => [35,0,10,5],
	"Orange Wool" => [35,1,10,5],
	"Magenta Wool" => [35,2,10,5],
	"Light Blue Wool" => [35,3,10,5],
    "Yellow Wool" => [35,4,10,5],
	"Lime Wool" => [35,5,10,5],
	"Pink Wool" => [35,6,10,5],
	"Gray Wool" => [35,7,10,5],
	"Light Gray Wool" => [35,8,10,5],
	"Cyan Wool" => [35,9,10,5],
	"Purple Wool" => [35,10,10,5],
	"Blue Wool" => [35,11,10,5],
	"Brown Wool" => [35,12,10,5],  
	"Green Wool" => [35,13,10,5],
	"Red Wool" => [35,14,10,5],
	"Black Wool" => [35,15,10,5],
	"Beacon" => [138,0,10000,5000],
	"Black Glazed Terracotta" => [250,0,100,50],
	"Blue Glazed Terracotta" => [246,0,100,50],
	"Black Glazed Terracotta" => [250,0,100,50],
	"Brown Glazed Terracotta" => [247,0,100,50],
	"Cyan Glazed Terracotta" => [244,0,100,50],
	"Black Glazed Terracotta" => [250,0,100,50],
	"Gray Glazed Terracotta" => [242,0,100,50],
	"Green Glazed Terracotta" => [248,0,100,50],
	"Terracotta" => [172,0,100,50],
	"Light Blue Glazed Terracotta" => [238,0,100,50],
	"Lime Glazed Terracotta" => [240,0,100,50],
	"Magenta Glazed Terracotta" => [237,0,100,50],
	"Orange Glazed Terracotta" => [236,0,100,50],
	"Pink Glazed Terracotta" => [241,0,100,50],
	"Purple Glazed Terracotta" => [245,0,100,50],
	"Red Glazed Terracotta" => [249,0,100,50],
	"Light Gray Glazed Terracotta" => [243,0,100,50],
	"White Terracotta" => [159,0,100,50],
	"Orange Terracotta" => [159,1,100,50],
	"Magenta Terracotta" => [159,2,100,50],
	"Light Blue Terracotta" => [159,3,100,50],
	"Yellow Terracotta" => [159,4,100,50],
	"Lime Terracotta" => [159,5,100,50],
	"Pink Terracotta" => [159,6,100,50],
	"Gray Terracotta" => [159,7,100,50],
	"Light Gray Terracotta" => [159,8,100,50],
	"Cyan Terracotta" => [159,9,100,50],
	"Purple Terracotta" => [159,10,100,50],
	"Blue Terracotta" => [159,11,100,50],
	"Brown Terracotta" => [159,12,100,50],
	"Green Terracotta" => [159,13,100,50],
	"Red Terracotta" => [159,14,100,50],
	"Black Terracotta" => [159,15,100,50],
	"White Glazed Terracotta" => [235,0,100,50],
	"Yellow Glazed Terracotta" => [239,0,100,50],
    "Quartz Block" => [155,0,201,6]
  ];

  public $Ores = [
    "ICON" => ["Ores",266,0],
    "Coal" => [263,0,400,10],
    "Iron Ingot" => [265,0,600,15],
    "Gold Ingot" => [266,0,300,15],
	"RedStone" => [331,0,300,10],
    "Diamond" => [264,0,350,15]
  ];

  public $Tools = [
    "ICON" => ["Tools",278,0],
    "Diamond Pickaxe" => [278,0,2500,900],
    "Diamond Shovel" => [277,0,2500,900],
    "Diamond Axe" => [279,0,2500,900],
    "Diamond Hoe" => [293,0,1500,500],
    "Diamond Sword" => [276,0,6500,1000],
	"Wooden Sword" => [268,0,0,0],
	"Wooden Pickaxe" => [276,0,0,0],
	"Wooden Axe" => [276,0,0,0],
	"Wooden Hoe" => [276,0,0,0],
    "Bow" => [261,0,2000,450],
	"lava" => [11,0,0,0],
	"Water" => [9,0,0,0],
    "Arrow" => [262,0,500,0]
  ];

  public $Armor = [
    "ICON" => ["Armor",311,0],
    "Diamond Helmet" => [310,0,10000,15],
    "Diamond Chestplate" => [311,0,25000,25],
    "Diamond Leggings" => [312,0,15000,20],
    "Diamond Boots" => [313,0,10000,10]
  ];

  public $Seeds = [
    "ICON" => ["Seeds",293,0],
    "Pumpkin" => [86,0,100,5],
    "Melon" => [360,13,100,5],
    "Carrot" => [391,0,80,10],
    "Potato" => [392,0,80,10],
    "Sugarcane" => [338,0,80,10],
    "Wheat" => [296,6,80,40],
    "Pumpkin Seed" => [361,0,50,10],
    "Melon Seed" => [362,0,50,10],
    "Red Mushroom" => [40,0,50,10],
	"Brown Mushroom" => [39,0,50,10],
	"Wheat" => [295,0,50,10]
  ];

  public $Food = [
    "ICON" => ["Food",364,0],
	"Cooked Chicken" => [366,0,100,5],
    "Steak" => [364,0,1000,0],
    "Golden Apple" => [322,0,100,100],
    "Enchanted Golden Apple" => [466,0,5000,0]
  ];

  public $Miscellaneous = [
    "ICON" => ["Miscellaneous",368,0],
	"Furnace" => [61,0,20,10],
    "Crafting Table" => [58,0,20,10],
	"Ender Chest " => [130,0,100000,500],
    "Enderpearl" => [368,0,2500,40],
    "Book & Quill" => [386,0,100,0],
    "Elytra" => [444,0,200000,500]
  ];

  public $Raiding = [
    "ICON" => ["Raiding",46,0],
    "Flint & Steel" => [259,0,100,50],
    "Torch" => [50,0,0,0],
	"Packed Ice " => [174,0,5000,50],
    "Redstone" => [331,0,50,25],
    "Chest" => [54,0,100,50],
	"Bone Meal" => [351,15,50,25],
	"Oak Sapling" => [6,0,50,25],
    "TNT" => [46,0,9999999999999999999999999999999,0]
  ];
	
  public $Mobs = [
    "ICON" => ["Mobs",52,0],
    "Blaze" => [383,94,50000,1000],
    "Creeper" => [383,33,50000,1000],
    "Zombie" => [383,32,50000,1000],
    "Husk" => [383,47,50000,1000],
    "Zombie_Pigman" => [383,36,50000,1000],
	"Skeleton" => [383,34,50000,1000],
	"Spider" => [383,35,50000,1000],
	"Chicken" => [383,10,50000,1000],
	"Cow" => [383,11,50000,1000],
	"Pig" => [383,12,50000,1000],
	"Sheep" => [383,13,50000,1000],
	"Wolf" => [383,14,50000,1000],
	"Villager" => [383,15,50000,1000],
	"Mooshroom" => [383,16,50000,1000],
	"Squid" => [383,17,50000,1000],
	"Rabbit" => [383,18,50000,1000],
	"Bat" => [383,19,50000,1000],
    "Enderman" => [383,38,50000,1000],	
    "Mob Spawner" => [52,0,30000,2000]
  ];

  public $Potions = [
    "ICON" => ["Potions",373,0],
    "Strength" => [373,33,1000,100],
    "Regeneration" => [373,28,1000,100],
    "Speed" => [373,16,1000,500],
    "Fire Resistance" => [373,13,1000,100],
    "Poison (SPLASH)" => [438,27,1000,100],
    "Weakness (SPLASH)" => [438,35,1000,100],
    "Slowness (SPLASH)" => [438,17,1000,100]
  ];

  public $Skulls = [
    "ICON" => ["Skulls",144,0],
    "Zombie Skull" => [397,2,5000,50],
    "Wither Skull" => [397,1,5000,50],
    "Skin Head" => [397,3,500,10],
    "Creeper Skull" => [397,4,5000,50],
    "Dragon Skull" => [397,5,10000,60],
    "Skeleton Skull" => [397,0,5000,50]
  ];
	
  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    PacketPool::registerPacket(new GuiDataPickItemPacket());
		PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
    $this->item = [$this->Skulls, $this->Potions, $this->Mobs, $this->Raiding, $this->Seeds, $this->Armor, $this->Tools, $this->Food, $this->Ores, $this->Blocks, $this->Miscellaneous];
  }

  public function sendMainShop(Player $player){
    $ui = new SimpleForm("§4Shop","       Mua và bán đồ vật");
    foreach($this->item as $category){
      if(isset($category["ICON"])){
        $rawitemdata = $category["ICON"];
        $button = new Button($rawitemdata[0]);
        $button->addImage('url', "http://avengetech.me/items/".$rawitemdata[1]."-".$rawitemdata[2].".png");
        $ui->addButton($button);
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 110;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendShop(Player $player, $id){
    $ui = new SimpleForm("§4Shop","       Mua và bán đồ vật!");
    $ids = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $id){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            $button = new Button($name);
            $button->addImage('url', "http://avengetech.me/items/".$item[0]."-".$item[1].".png");
            $ui->addButton($button);
          }
        }
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 111;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendConfirm(Player $player, $id){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $id){
              $this->item[$player->getName()] = $id;
              $iname = $name;
              $cost = $item[2];
              $sell = $item[3];
              break;
            }
          }
          $idi++;
        }
      }
    }

    $ui = new CustomForm($iname);
    $slider = new Slider("Số lượng ",1,500,1);
    $toggle = new Toggle("Bán");
    if($sell == 0) $sell = "0";
    $label = new Label(TF::GREEN."Mua: $".TF::GREEN.$cost.TF::RED."\nBán: $".TF::RED.$sell);
    $ui->addElement($label);
    $ui->addElement($toggle);
    $ui->addElement($slider);
    $pk = new ModalFormRequestPacket();
    $pk->formId = 112;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sell(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if($sell == 0){
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§4Đây không phải đồ có thể bán!");
                return true;
              }
              if($player->getInventory()->contains(Item::get($id,$damage,$amount))){
                $player->getInventory()->removeItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->addMoney($player, $sell);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§2Bạn đã bán $amount cái $iname .Bạn nhận được số tiền là: $sell xu");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§4Bạn không có đủ món đẻ bán $amount cái $iname");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function purchase(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if(EconomyAPI::getInstance()->myMoney($player) > $cost){
                $player->getInventory()->addItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->reduceMoney($player, $cost);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§2Bạn đã mua $amount cái $iname.Với số tiền sử dụng: $cost xu");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§4Bạn không đủ tiền để mua $amount cái $iname");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function DataPacketReceiveEvent(DataPacketReceiveEvent $event){
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    if($packet instanceof ModalFormResponsePacket){
      $id = $packet->formId;
      $data = $packet->formData;
      $data = json_decode($data);
      if($data === Null) return true;
      if($id === 110){
        $this->shop[$player->getName()] = $data;
        $this->sendShop($player, $data);
        return true;
      }
      if($id === 111){
        //$this->shop[$player->getName()] = $data;
        $this->sendConfirm($player, $data);
        return true;
      }
      if($id === 112){
        $selling = $data[1];
        $amount = $data[2];
        if($selling){
          $this->sell($player, $data, $amount);
          return true;
        }
        $this->purchase($player, $data, $amount);
        return true;
      }
    }
    return true;
  }

  public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool{
    switch(strtolower($command)){
      case "shop":
        $this->sendMainShop($player);
        return true;
    }
  }

}
