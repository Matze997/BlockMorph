<?php

namespace matze\blockmorph;

use matze\blockmorph\command\BlockMorphCommand;
use matze\blockmorph\entity\BlockMorphEntity;
use matze\blockmorph\listener\PlayerQuitListener;
use matze\blockmorph\session\BlockMorphSessionManager;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Loader extends PluginBase {

    /** @var Loader|null */
    private static $instance = null;

    public function onEnable(): void {
        self::$instance = $this;

        $this->initListener();

        new BlockMorphSessionManager();

        Entity::registerEntity(BlockMorphEntity::class, true);
        Server::getInstance()->getCommandMap()->register("blockmorph", new BlockMorphCommand());
    }

    /**
     * @return Loader|null
     */
    public static function getInstance(): ?Loader {
        return self::$instance;
    }

    private function initListener(): void {
        $listeners = [
            new PlayerQuitListener()
        ];
        $pManager = Server::getInstance()->getPluginManager();
        foreach($listeners as $listener) {
            $pManager->registerEvents($listener, $this);
        }
    }
}