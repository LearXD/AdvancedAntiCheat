<?php


namespace learxd\anticheat;


use learxd\anticheat\utils\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\Plugin;


class AnticheatListener implements \pocketmine\event\Listener
{

    /** @var Main */
    protected $owner = null;

    /** @var array */
    protected $lastMovement = [];

    /** @var array */
    protected $lastHit = [];

    /** @var array */
    protected $lastInteract = [];

    public function __construct(Plugin $plugin)
    {
        $this->owner = $plugin;
    }

    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        switch ($event->getItem()->getId()){
            case Item::GOLDEN_APPLE:
			case Item::ENCHANTED_GOLDEN_APPLE:
                if (Main::getConfiguration('anti-autogap')) $this->lastInteract[strtolower($player->getName())][$event->getItem()->getId()] = microtime(true);
                break;
			
        }
    }

    public function onConsume(PlayerItemConsumeEvent $event){
        $player = $event->getPlayer();
        if (Main::getConfiguration('anti-autogap')) {
            if (
                $event->getItem()->getId() == Item::GOLDEN_APPLE and
                isset($this->lastInteract[strtolower($player->getName())][$event->getItem()->getId()]) and
                (microtime(true) - ($this->lastInteract[strtolower($player->getName())][$event->getItem()->getId()])) <= Main::getConfiguration('anti-autogap-time')
            ) {
                $event->setCancelled(true);
            }
            //var_dump((microtime(true) - ($this->lastInteract[strtolower($player->getName())][$event->getItem()->getId()])));
        }
    }

    public function onEntityDamage(EntityDamageEvent $event){
        if(($entity = $event->getEntity()) instanceof Player and $event instanceof EntityDamageByEntityEvent and ($damager = $event->getDamager())) {

            if (Main::getConfiguration('anti-reach')) {
                if ($damager->distance($entity) > Main::getConfiguration('anti-reach-distance', 4.5)) {
                    $event->setCancelled(true);
                }
            }

            if (Main::getConfiguration('anti-auto-clicker')) {
                if (isset($this->lastHit[strtolower($damager->getName())])) {
                    if ((microtime(true) - $this->lastHit[strtolower($damager->getName())]) < Main::getConfiguration('anti-auto-clicker-countdown')) {
                        $event->setCancelled(true);
                    } else {
                        $this->lastHit[strtolower($damager->getName())] = microtime(true);
                    }
                } else {
                    $this->lastHit[strtolower($damager->getName())] = microtime(true);
                }
            }
        }
    }
    
    public function onTeleport(EntityTeleportEvent $event){
        if (Main::getConfiguration('anti-fly')) {
            if (($entity = $event->getEntity()) instanceof Player) {
                $this->lastMovement[strtolower($entity->getName())] = [$event->getTo(), 0];
            }
        }
    }

    /**
     * ESSA FUNÇÃO PODE CAUSAR LAG!
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();

        if (Main::getConfiguration('anti-fly')) {
            if ($player->getGamemode() == Player::CREATIVE or $player->getGamemode() == Player::SPECTATOR or $player->getAllowFlight())
                return false;

            if (isset($this->lastMovement[strtolower($player->getName())])) {
                $data = $this->lastMovement[strtolower($player->getName())];
                /** @var Position $position */
                $position = $data[0];

                if ($data[1] >= (Main::getConfiguration('anti-fly-precision') < 15 ? 15 :  Main::getConfiguration('anti-fly-precision'))) {
                    $player->teleport($position);
                    $this->lastMovement[strtolower($player->getName())] = [$position, 0];

                } else if (
                    (($player->getY() - $position->getY()) < 1 or
                        $player->distance($position) < 6) and
                    Utils::checkAround($player)
                ) {
                    $this->lastMovement[strtolower($player->getName())] = [$player->getPosition(), 0];
                } else {
                    $this->lastMovement[strtolower($player->getName())][1]++;
                }

            } else {
                $this->lastMovement[strtolower($player->getName())] = [$player->getPosition(), 0];
            }
        }
    }

}