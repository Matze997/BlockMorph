<?php

namespace matze\blockmorph\session;

use pocketmine\block\Block;
use pocketmine\Player;
use function is_null;

class BlockMorphSessionManager {

    /** @var BlockMorphSessionManager|null */
    private static $instance = null;

    /** @var array  */
    private $sessions = [];

    /**
     * BlockMorphSessionManager constructor.
     */
    public function __construct() {
        self::$instance = $this;
    }

    /**
     * @return BlockMorphSessionManager|null
     */
    public static function getInstance(): ?BlockMorphSessionManager{
        return self::$instance;
    }

    /**
     * @param Player $player
     * @param Block $block
     * @return BlockMorphSession
     */
    public function createSession(Player $player, Block $block): BlockMorphSession {
        if(!is_null(($session = $this->getSessionByPlayer($player)))) return $session;
        $session = new BlockMorphSession($player, $block);
        $this->addSession($session);
        return $session;
    }

    /**
     * @param BlockMorphSession $session
     */
    public function addSession(BlockMorphSession $session): void {
        $this->sessions[$session->getPlayer()->getName()] = $session;
    }

    /**
     * @param BlockMorphSession $session
     */
    public function removeSession(BlockMorphSession $session): void {
        if(!isset($this->sessions[$session->getPlayer()->getName()])) return;
        unset($this->sessions[$session->getPlayer()->getName()]);
    }

    /**
     * @param BlockMorphSession $session
     */
    public function destroySession(BlockMorphSession $session): void {
        $session->destroy();
        $this->removeSession($session);
    }

    /**
     * @param Player|string $player
     * @return BlockMorphSession|null
     */
    public function getSessionByPlayer($player): ?BlockMorphSession {
        if($player instanceof Player) $player = $player->getName();
        return $this->sessions[$player] ?? null;
    }
}