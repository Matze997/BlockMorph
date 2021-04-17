<?php

namespace matze\blockmorph\entity;

use matze\blockmorph\session\BlockMorphSession;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\ChunkLoader;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Server;
use function is_null;
use function spl_object_id;

class BlockMorphEntity extends Entity implements ChunkLoader {
    public const NETWORK_ID = self::FALLING_BLOCK;

    /** @var float  */
    public $width = 0.98;
    /** @var float  */
    public $height = 0.98;

    /** @var BlockMorphSession */
    private $session;

    /** @var Block */
    protected $block;

    /**
     * BlockMorphEntity constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param BlockMorphSession|null $session
     */
    public function __construct(Level $level, CompoundTag $nbt, ?BlockMorphSession $session = null){
        if(is_null($session)) {
            return;
        }
        $this->session = $session;
        parent::__construct($level, $nbt);
    }

    public function initEntity(): void{
        parent::initEntity();

        $player = $this->getSession()->getPlayer();
        $this->getDataPropertyManager()->setVector3(Entity::DATA_RIDER_SEAT_POSITION, $this->getSeatPosition());

        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink($player->id, $this->getId(), EntityLink::TYPE_RIDER, true, true);
        $this->server->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);

        $blockId = $this->namedtag->getInt("Tile");
        $blockDamage = $this->namedtag->getInt("Data");
        $this->setBlock(BlockFactory::get($blockId, $blockDamage));
    }

    /**
     * @return BlockMorphSession
     */
    public function getSession(): BlockMorphSession {
        return $this->session;
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool{
        if($this->isClosed() || $this->isFlaggedForDespawn()) {
            return false;
        }
        $session = $this->getSession();
        $player = $session->getPlayer();
        if(!$player->isOnline()) {
            return false;
        }

        //I hate entity link
        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink($player->id, $this->getId(), EntityLink::TYPE_RIDER, true, true);
        $this->server->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);

        $this->setPosition($player->add($this->getSeatPosition()));
        $this->onGround = false;
        return parent::entityBaseTick($tickDiff);
    }

    /**
     * @return Vector3
     */
    public function getSeatPosition(): Vector3 {
        return new Vector3(0, -1.125, 0);
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function canCollideWith(Entity $entity) : bool{
        return false;
    }

    /**
     * @return bool
     */
    public function canBeMovedByCurrents() : bool{
        return false;
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source) : void{
        if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
            parent::attack($source);
        }
    }

    /**
     * @return Block
     */
    public function getBlock() : Block{
        return $this->block;
    }

    /**
     * @param Block $block
     */
    public function setBlock(Block $block): void {
        $this->block = $block;
        $this->propertyManager->setInt(self::DATA_VARIANT, $this->block->getRuntimeId());
    }

    /**
     * @return bool
     */
    public function isLoaderActive(): bool{
        return !$this->isFlaggedForDespawn() && !$this->isClosed() && $this->isAlive();
    }

    /**
     * @return int
     */
    public function getLoaderId(): int{
        return spl_object_id($this);
    }

    public function onChunkPopulated(Chunk $chunk){}
    public function onBlockChanged(Vector3 $block){}
    public function onChunkChanged(Chunk $chunk){}
    public function onChunkLoaded(Chunk $chunk){}
    public function onChunkUnloaded(Chunk $chunk){}
}