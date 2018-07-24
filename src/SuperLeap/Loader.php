<?php

namespace SuperLeap;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use SuperLeap\Exception\ProgressbarException;

class Loader extends PluginBase implements Listener
{


    /**
     * @var bool $give_on_join
     */
    private $give_on_join;

    /**
     * @var array $leap_cooldown
     */
    public $leap_cooldown = [];

    /**
     * @var PluginBase $instance
     */
    private static $instance;

    public function onEnable() : void
    {
        $this->getLogger()->info(TextFormat::GREEN . "SuperLeap enabled!");
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->give_on_join = $this->getConfig()->get('give-on-join', true);
        self::$instance = $this;
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        if($this->give_on_join){
            $player->getInventory()->addItem(Item::get(Item::FEATHER)->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Super Leap"));
        }
    }

    /**
     * @return Loader
     */
    public static function getInstance() : Loader{
        return self::$instance;
    }

    /**
     * @param PlayerInteractEvent $event
     * @return bool
     * @throws \SuperLeap\Exception\ProgressbarException
     */
    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        if($item->getId() == Item::FEATHER && $item->getName() == TextFormat::RESET . TextFormat::GREEN . "Super Leap"){
            if(!isset($this->leap_cooldown[$player->getRawUniqueId()])){
                $player->setMotion(new Vector3(0,1.5,0));
                $this->leap_cooldown[$player->getRawUniqueId()] = $player;
                $bar = new Progressbar(50, "c", "a");
                $bar->apply($player);
            }
        }
        return true;
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) : bool
    {
        if(strtolower($command->getName()) == "superleap"){
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "This command can be executed only in-game!");
                return true;
            }
            $sender->getInventory()->addItem(Item::get(Item::LEATHER)->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Super Leap"));
        }
    }

}
