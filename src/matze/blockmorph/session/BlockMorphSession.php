<?php

namespace matze\blockmorph\session;

use matze\blockmorph\entity\BlockMorphEntity;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\Player;

class BlockMorphSession {

    /** @var Player */
    private $player;
    /** @var BlockMorphEntity */
    private $blockEntity;

    /**
     * BlockMorphSession constructor.
     * @param Player $player
     * @param Block $block
     */
    public function __construct(Player $player, Block $block) {
        $this->player = $player;
        $this->blockEntity = $this->createBlockMorphEntity($player, $block);

        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 999999, 1, false));
    }

    /**
     * @param Player $player
     * @param Block $block
     * @return BlockMorphEntity
     */
    private function createBlockMorphEntity(Player $player, Block $block): BlockMorphEntity {
        $nbt = Entity::createBaseNBT($player);
        $nbt->setInt("Tile", $block->getId());
        $nbt->setInt("Data", $block->getDamage());
        $blockMorphEntity = new BlockMorphEntity($player->getLevel(), $nbt, $this);
        $blockMorphEntity->spawnToAll();

        return $blockMorphEntity;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player{
        return $this->player;
    }

    /**
     * @return BlockMorphEntity
     */
    public function getBlockEntity(): BlockMorphEntity{
        return $this->blockEntity;
    }

    public function destroy(): void {
        if(!$this->blockEntity->isClosed()) {
            $this->blockEntity->flagForDespawn();
        }
        $this->getPlayer()->removeEffect(Effect::INVISIBILITY);
    }
}