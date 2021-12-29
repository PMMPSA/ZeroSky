<?php
namespace MyPlot\subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\NBT;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Position;

class ClaimSubCommand extends SubCommand
{
    public function canUse(CommandSender $sender) {
        return ($sender instanceof Player) and $sender->hasPermission("myplot.command.claim");
    }

    public function execute(CommandSender $sender, array $args) {
        if (count($args) > 1) {
            return false;
        }
        $name = "";
        if (isset($args[0])) {
            $name = $args[0];
        }
        $player = $sender->getServer()->getPlayer($sender->getName());
        $plot = $this->getPlugin()->getPlotByPosition($player->getPosition());
        if ($plot === null) {
            $sender->sendMessage(TextFormat::RED . $this->translateString("notinplot"));
            return true;
        }
        if ($plot->owner != "") {
            if ($plot->owner === $sender->getName()) {
                $sender->sendMessage(TextFormat::RED . $this->translateString("claim.yourplot"));
            } else {
                $sender->sendMessage(TextFormat::RED . $this->translateString("claim.alreadyclaimed", [$plot->owner]));
            }
            return true;
        }

        $maxPlots = $this->getPlugin()->getMaxPlotsOfPlayer($player);
        $plotsOfPlayer = count($this->getPlugin()->getProvider()->getPlotsByOwner($player->getName()));
        if ($plotsOfPlayer >= $maxPlots) {
            $sender->sendMessage(TextFormat::RED . $this->translateString("claim.maxplots", [$maxPlots]));
            return true;
        }

        $plotLevel = $this->getPlugin()->getLevelSettings($plot->levelName);
        $economy = $this->getPlugin()->getEconomyProvider();
        if ($economy !== null and !$economy->reduceMoney($player, $plotLevel->claimPrice)) {
            $sender->sendMessage(TextFormat::RED . $this->translateString("claim.nomoney"));
            return true;
        }
		

        $plot->owner = $sender->getName();
        $plot->name = $name;
        if ($this->getPlugin()->getProvider()->savePlot($plot)) {
            $sender->sendMessage($this->translateString("claim.success"));
			
			$player->getInventory()->addItem(Item::get(8, 0, 2));
			$player->getInventory()->addItem(Item::get(85, 0, 2));
			$player->getInventory()->addItem(Item::get(270, 0, 1));
			$player->getInventory()->addItem(Item::get(295, 0, 1));
			$player->getInventory()->addItem(Item::get(86, 0, 1));
			$player->getInventory()->addItem(Item::get(360, 13, 1));
			$player->getInventory()->addItem(Item::get(391, 0, 1));
			$player->getInventory()->addItem(Item::get(392, 0, 1));
			$player->getInventory()->addItem(Item::get(338, 0, 1));
			$player->getInventory()->addItem(Item::get(296, 6, 1));
			$player->getInventory()->addItem(Item::get(361, 0, 1));
			$player->getInventory()->addItem(Item::get(362, 0, 1));
			$player->getInventory()->addItem(Item::get(81, 0, 1));
			$player->getInventory()->addItem(Item::get(39, 0, 1));
			$player->getInventory()->addItem(Item::get(40, 0, 1));
			$player->getInventory()->addItem(Item::get(351, 15, 1));
			$player->getInventory()->addItem(Item::get(50, 0, 10));
            $sender->sendMessage("§7[§4Zero§6Sky§a-§aSkyBlock§7] §eĐã Thêm§b Nước§e Và§6 Hàng rào§e và một số đồ Vào Kho Đồ Của Bạn!(nước+hàng rào=block với ore nhé)");
        } else {
            $sender->sendMessage(TextFormat::RED . $this->translateString("error"));
        }
        return true;
    }
}