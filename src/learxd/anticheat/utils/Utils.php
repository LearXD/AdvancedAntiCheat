<?php

namespace learxd\anticheat\utils;

use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

final class Utils
{

    /**
     * @param Position $position
     * @return bool
     */
    public static function checkAround(Position $position, $return = false): bool
    {
        return
            ($block = $position->getLevel()->getBlock($position->subtract(0, 1)))->getId() != Block::AIR or
            (self::getSide($block, Vector3::SIDE_NORTH)->getId() != Block::AIR or
                self::getSide($block, Vector3::SIDE_SOUTH)->getId() != Block::AIR or
                self::getSide($block, Vector3::SIDE_WEST)->getId() != Block::AIR or
                self::getSide($block, Vector3::SIDE_EAST)->getId() != Block::AIR);
    }


    /**
     * @param Block $block
     * @param int $side
     * @param float $step
     * @return Vector3
     */
    public static function getSide(Block $block, int $side = Vector3::SIDE_NORTH, float $step = 0.4): Vector3
    {
        switch ($side) {
            case Vector3::SIDE_NORTH:
                return $block->getLevel()->getBlock(new Vector3($block->x, $block->y, $block->z - $step));
            case Vector3::SIDE_SOUTH:
                return $block->getLevel()->getBlock(new Vector3($block->x, $block->y, $block->z + $step));
            case Vector3::SIDE_WEST:
                return $block->getLevel()->getBlock(new Vector3($block->x - $step, $block->y, $block->z));
            case Vector3::SIDE_EAST:
                return $block->getLevel()->getBlock(new Vector3($block->x + $step, $block->y, $block->z));
        }
    }

    public static function getLookingAt(Player $player)
    {
        return $player->getTargetBlock(100);
    }
}