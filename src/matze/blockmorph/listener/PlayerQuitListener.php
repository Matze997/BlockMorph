<?php

namespace matze\blockmorph\listener;

use matze\blockmorph\session\BlockMorphSessionManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use function is_null;

class PlayerQuitListener implements Listener {

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $session = BlockMorphSessionManager::getInstance()->getSessionByPlayer($player);

        if(!is_null($session)) {
            BlockMorphSessionManager::getInstance()->destroySession($session);
        }
    }
}