<?php

namespace matze\blockmorph\command;

use matze\blockmorph\Loader;
use matze\blockmorph\session\BlockMorphSessionManager;
use pocketmine\block\Block;
use pocketmine\block\UnknownBlock;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use function explode;
use function is_null;

class BlockMorphCommand extends Command implements PluginIdentifiableCommand {

    /**
     * BlockMorphCommand constructor.
     */
    public function __construct(){
        parent::__construct("blockmorph", "BlockMorph Command");
        $this->setPermission("cmd.blockmorph");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof Player) return;
        if(!$this->testPermission($sender)) return;

        $session = BlockMorphSessionManager::getInstance()->getSessionByPlayer($sender);
        if(is_null($session) || isset($args[0])) {
            if(!isset($args[0])) {
                $sender->sendMessage("/blockmorph [ID:DAMAGE = 0]");
                return;
            }
            if($args[0] === "hand") {
                $block = $sender->getInventory()->getItemInHand();
                $blockID = $block->getId();
                $blockDamage = $block->getDamage();
            } else {
                $block = explode(":", $args[0]);
                $blockID = $block[0];
                $blockDamage = $block[1] ?? 0;
            }

            if($blockID > 256 || $blockID <= 0) {
                $sender->sendMessage("This block is not allowed.");
                return;
            }
            $block = Block::get($blockID, $blockDamage);
            $session = BlockMorphSessionManager::getInstance()->createSession($sender, $block);
            $session->getBlockEntity()->setBlock($block);

            $sender->sendMessage("Successfully morphed as " . $block->getName() . ".");
            return;
        }
        BlockMorphSessionManager::getInstance()->destroySession($session);
        $sender->sendMessage("Morph was removed.");
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin{
        return Loader::getInstance();
    }
}