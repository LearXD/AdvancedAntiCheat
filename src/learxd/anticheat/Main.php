<?php

namespace learxd\anticheat;

class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener
{

    /** @var array */
    public static $config = null;

    public function onEnable(){
        @mkdir($folder = $this->getDataFolder());
        $this->saveResource('config.yml', true);
        self::$config = @yaml_parse_file($folder . 'config.yml');

        $this->getServer()->getPluginManager()->registerEvents(new AnticheatListener($this), $this);
        $this->getServer()->getLogger()->info("§aAdvanced Anti-Cheat Operante :D");
    }

    /**
     * @param string $name
     * @param false $default
     * @return false|mixed
     */
    public static function getConfiguration(string $name, $default = false){
        if(isset(self::$config[$name])){
            return self::$config[$name];
        } else {
            return $default;
        }
    }

}