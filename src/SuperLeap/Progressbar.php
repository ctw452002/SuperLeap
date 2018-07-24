<?php

namespace SuperLeap;


use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use SuperLeap\Exception\ProgressbarException;

class Progressbar
{

    /**
     * @var int
     */
    public $length;

    /**
     * @var int|string
     */
    public $default_color, $progress_color;

    /**
     * Progressbar constructor.
     * @param int $length
     * @param int $default_color
     * @param int $progress_color
     * @throws ProgressbarException
     */
    public function __construct(int $length, $default_color, $progress_color)
    {
        if($length > 100){
            throw new ProgressbarException("Progressbar length can't be higher than 100");
        }

        $this->length = $length;
        $this->default_color = $default_color;
        $this->progress_color = $progress_color;
    }

    /**
     * @param Player $player
     */
    public function apply(Player $player){
        $task = new class($this, $player) extends Task{

            /**
             * @var Player $player
             */
            private $player;

            /**
             * @var Progressbar $progressbar
             */
            private $progressbar;

            /**
             * @var int
             */
            private $max_length, $progress;


            /**
             *  constructor.
             * @param Progressbar $progressbar
             * @param Player $player
             */
            public function __construct(Progressbar $progressbar, Player $player)
            {
                $this->progressbar = $progressbar;
                $this->player = $player;
                $this->max_length = $this->progressbar->length; //used to fill missing color
            }

            /**
             * @param int $currentTick
             */
            public function onRun(int $currentTick)
            {
                $player = $this->player;
                if(!$player->isOnline()){
                    Loader::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                }
                $default_text = str_repeat("§r§" . $this->progressbar->default_color . "|", $this->progressbar->length);
                $final_text = preg_replace("/" . $this->progressbar->default_color . "/", $this->progressbar->progress_color, $default_text, $this->progress++);
                $time = ($this->max_length -$this->progress) / 100 * 20;
                if($time < 0)$time = 0;
                $item = $player->getInventory()->getItemInHand();
                if($item->getId() == Item::FEATHER && $item->getName() == TextFormat::RESET . TextFormat::GREEN . "Super Leap"){
                    $player->sendTip(TextFormat::BOLD . TextFormat::WHITE.  "Leap " . $final_text . " " . TextFormat::WHITE .  $time);
                }
                if($this->progress > $this->max_length){
                    unset(Loader::getInstance()->leap_cooldown[$player->getRawUniqueId()]);
                    Loader::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                }

            }
        };
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 5);
    }

}