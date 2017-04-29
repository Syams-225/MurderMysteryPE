<?php

namespace TurtleTeam\MurderMysteryPE;
  
/**
 * This is the main class of the plugin. It will load everything.
 *
 * @link https://github.com/TurtleTeam/MurderMysteryPE.git
 */
  
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use TurtleTeam\MurderScene;

define("START_TIME", microtime(true));
// If the plugin is running from the source
define("DEV_MODE", strpos(__FILE__, "phar://") === false);

/**
 * Returns a string
 *
 * @param string $key
 * @param string[] $params
 *
 * @return string
 */
function lang(string $key, $params = []): string {
    return MurderMystery::getInstance()->getMessage($key, (array) $params);
}

/**
 * Returns a setting value from a config file
 *
 * @param string $key
 * @param null $default
 * @param bool $nested
 *
 * @return mixed
 */
function _var(string $key, $default = null, $nested = false){
    if (!$nested) return MurderMystery::getInstance()->getConfig()->get($key, $default);
    return MurderMystery::getInstance()->getConfig()->getNested($key, $default);
}

class MurderMystery extends PluginBase{

    /** @var MurderMystery */
    private static $instance;

    public static function getInstance(): MurderMystery{
        return self::$instance;
    }

    /** @var Config $lang */
    private $lang;

    /** @var \TurtleTeam\MurderScene[] */
    private $murderScenes = [];

    public function onLoad(){
        self::$instance = $this;

        $df = $this->getDataFolder();
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . 'murderScenes/');
        $this->saveDefaultConfig();

        if (!file_exists($df . "messages.yml")) {
            $this->saveResource("messages.yml");
        }

        // Load messages
        $this->lang = new Config($df . "messages.yml");
    }

    public function onEnable(){
        $this->getLogger()->info(lang("plugin.enabling"));

        // Load Games

        // Load Signs

        // Set command executors

        // Schedule SceneTicker

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getLogger()->info(lang("plugin.enabled", ["time" => round(microtime(true) - START_TIME, 4)]));
    }

    public function onDisable(){
        $this->getLogger()->info(lang("plugin.disabling"));

        // Stop all MurderScenes
        foreach ($this->murderScenes as $scene) {
            $scene->stop();
        }

        // Save all MurderScenes settings into array then into file
        // TODO

        // Save all signs into array then into file
        // TODO

        $this->getLogger()->info(lang("plugin.disabled"));
    }

    private function getLang(): Config{
        return $this->lang;
    }


//      .d8b.  d8888b. d888888b 
//     d8' `8b 88  `8D   `88'   
//     88ooo88 88oodD'    88    
//     88~~~88 88~~~      88    
//     88   88 88        .88.   
//     YP   YP 88      Y888888b

    /**
     * @param string $key
     * @param array $params
     *
     * @return mixed
     */
    public function getMessage(string $key, array $params = []){
        $msg = $this->getLang()->getNested($key, $key);
        if ($msg === $key) {
            $this->getLogger()->debug("Undefined key '$key' " . (!empty($params) ? "(params=" . implode(", ", array_map(function ($key, $el) {
                        return $key . ": '" . $el . "'";
                    }, $params)) . ")" : ""));
        }

        $i = 0;
        foreach ($params as $key => $value) {
            $msg = str_replace([":$i", "{:$key}", ":$key"], $value, $msg);
            ++$i;
        }

        return $msg;
    }

    /**
     * @param int $id
     *
     * @return MurderScene|null
     */
    public function getMurderScene($id){
        if(isset($this->murderScenes[$id])){
            return $this->murderScenes[$id];
        }
        return null;
    }

    /**
     * @param Player $player
     *
     * @return MurderScene|null
     */
    public function getMurderSceneByPlayer(Player $player){
        foreach($this->murderScenes as $scene) if(array_key_exists(spl_object_hash($player), $scene->getParticipators())) return $scene;
        return null;
    }

    /**
     * Returns true if $player is currently playing in one of the scenes
     *
     * @param Player $player
     *
     * @return bool
     */
    public function isParticipator(Player $player): bool{
        return $this->getMurderSceneByPlayer($player) !== null;
    }

    /**
     * if participator returns int
     *
     * 0x00 = not participating
     * 0x01 = traitor
     * 0x02 = detective
     * 0x02 = innocent
     * 0x03 = unknown
     *
     * @param Player $player
     *
     * @return int
     */
    public function getRole(Player $player){
        if($this->isParticipator($player)){
            $scene = $this->getMurderSceneByPlayer($player);
            return $scene->getRole($player);
        }
        return 0x00;
    }

    /**
     * @return MurderScene[]
     */
    public function getAllMurderScenes(): array{
        return $this->murderScenes;
    }

    // TODO More functions: addMurderScene, murderSceneExists, removeMurderScene

}