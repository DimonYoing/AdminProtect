<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class UnbanIPC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "pardon-ip", "Unban IP", "/pardon-ip <player>", null, ["unbanip", "pardonip", "unban-ip"]);
        $this->setPermission("adminprotect.unbanip.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /unbanip <{$this->cfg->get("IP")}>");
        }else{
            
            if($sender instanceof Player){
                $admin = $sender->getNameTag();
            }else{
                $admin = $this->cfg->get("Console");
            }
            $name = array_shift($args);
            $allow_unban = true;
            if($this->getPlugin()->banInfoAPI != null){
                $banInfo = $this->getPlugin()->banInfoAPI->getBanInfo(true);
                if($banInfo->get($name) == null){
                    $allow_unban = false;
                }
            }
            if($allow_unban){
                if($this->getPlugin()->isIPValid($name)){
                    $broadcast = $this->cfg->get("UnbanBroadcast");
                    $broadcast = str_replace("%sender%", $admin, $broadcast);
                    $broadcast = str_replace("%player%", $name, $broadcast);
                    $sender->getServer()->getIPBans()->remove($name);
                    $sender->getServer()->getNetwork()->unblockAddress($name);
                    $sender->getServer()->broadcastMessage($broadcast);
                }else{
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("IncorrectIP")} {$this->cfg->get("forUnban")}");
                }
            }else{
                $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("IPNotBanned")}");
            }
        }
        return true;
    }
}