<?php


namespace BedWars;


use pocketmine\block\utils\SignText;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\network\protocol\LevelSoundEventPacket;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use BedWars\command\DefaultCommand;
use BedWars\game\Game;
use BedWars\game\GameListener;
use BedWars\game\Team;


class BedWars extends PluginBase
{

    const PREFIX = TextFormat::BOLD . TextFormat::DARK_RED . "BedWars " . TextFormat::RESET;

    /** @var Game[] $games */
    public $games = array();

    /** @var array $signs */
    public $signs = array();

    /** @var array $bedSetup */
    public $bedSetup = array();

    /** @var string $serverWebsite */
    public $serverWebsite;

    /** @var int $staticStartTime */
    public $staticStartTime;

    /** @var int $staticRestartTime */
    public $staticRestartTime;

    const TEAMS = [
        'blue' => "§1",
        'red' => "§c",
        'yellow' => "§e",
        "green" => "§a",
        "aqua" => "§b",
        "orange" => "§6",
        "white" => "§f",
        "pink" => "§d"
    ];

    const GENERATOR_PRIORITIES = [
        'gold' => ['item' => Item::GOLD_INGOT, 'spawnText' => false, 'spawnBlock' => false, 'refreshRate' => 10],
        'iron' => ['item' => Item::IRON_INGOT, 'spawnText' => false, 'spawnBlock' => false, 'refreshRate' => 3],
        'diamond' => ['item' => Item::DIAMOND, 'spawnText' => true, 'spawnBlock' => true, 'refreshRate' => 30],
        'emerald' => ['item' => Item::EMERALD, 'spawnText' => true, 'spawnBlock' => true, 'refreshRate' => 60]
    ];

    public function onEnable() : void
    {
        $formAPI = $this->getServer()->getPluginManager()->getPlugin('FormAPI');
        if($formAPI->getDescription()->getAuthors()[0] !== "jojoe77777"){
                $this->getLogger()->error("Invalid dependency author | FormAPI");
                $this->setEnabled(false);
        }

        $this->saveDefaultConfig();
        $this->serverWebsite = $this->getConfig()->get('website');

        @mkdir($this->getDataFolder() . "games");
        @mkdir($this->getDataFolder() . "skins");
        $this->saveResource("skins/264.png");
        $this->saveResource("skins/388.png");

        $this->getScheduler()->scheduleRepeatingTask(
            new SignUpdater($this), 20
        );
        $this->getServer()->getPluginManager()->registerEvents(new GameListener($this), $this);

        foreach(glob($this->getDataFolder() . "games/*.json") as $location){
            $fileContents = file_get_contents($location);
            $jsonData = json_decode($fileContents, true);

            if(!$this->validateGame($jsonData)){
                continue;
            }

            if(count($jsonData['signs']) > 0){
                $this->signs[$jsonData['name']] = $jsonData['signs'];
            }

            $this->games[$jsonData['name']] = $game = new Game($this, $jsonData);
        }

        $this->getServer()->getCommandMap()->register("bedwars", new DefaultCommand($this));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getGameData(string $id) : ?array{
        if(!$this->gameExists($id))return null;

        $location = $this->gamePath($id);

        $file = file_get_contents($location);
        return json_decode($file, true);
    }

    /**
     * @param string $id
     * @param int $minPlayers
     * @param int $playersPerTeam
     * @param int $startTime
     */
    public function createGame(string $id, int $minPlayers, int $playersPerTeam, int $startTime, string $world) : void{
        $dataStructure = [
            'name' => $id,
            'minPlayers' => $minPlayers,
            'playersPerTeam' => $playersPerTeam,
            'world' => $world,
            'startTime' => $startTime,
            'signs' => [],
            'teamInfo' => [],
            'generatorInfo' => []
        ];
        file_put_contents($this->gamePath($id), json_encode($dataStructure));
    }

    /**
     * @param string $id
     */
    public function deleteGame(string $id) : void{
        unlink($this->gamePath($id));
    }

    /**
     * @param string $id
     * @param int $x
     * @param int $y
     * @param int $z
     * @param string $levelName
     */
    public function setLobby(string $id, int $x, int $y, int $z, string $levelName, int $voidY) : void{
        $file = file_get_contents($path = $this->gamePath($id));
        $json = json_decode($file, true);

        $json['lobby'] = implode(":", [$x, ($y + 1.5), $z,$levelName]);
        $json['void_y'] = $voidY;
        file_put_contents($path, json_encode($json));
    }

    /**
     * @param string $id
     * @param string $team
     * @param string $keyPos
     * @param int $x
     * @param int $y
     * @param int $z
     */
    public function setTeamPosition(string $id, string $team, string $keyPos, int $x, int $y, int $z, float $yaw, float $pitch) : void{
        $file = file_get_contents($path = $this->gamePath($id));
        $json = json_decode($file, true);
        $key = ['shop', 'upgrade', 'spawn'];

        $json['teamInfo'][$team][$key[$keyPos] . "Pos"] = implode(":", [$x, $y, $z, $yaw, $pitch]);
        file_put_contents($path, json_encode($json));
    }

    /**
     * @param string $id
     * @return array
     */
    public function getTeams(string $id) : array{
        $file = file_get_contents($this->gamePath($id));
        $json = json_decode($file, true);

        $teams = array();
        foreach($json['teamInfo'] as $name => $data){
            $teams[] = $name;
        }
        return $teams;
    }

    /**
     * @param string $id
     * @param string $team
     */
    public function addTeam(string $id, string $team){
        $file = file_get_contents($path = $this->gamePath($id));
        $json = json_decode($file, true);
        $json['teamInfo'][$team] = ['spawnPos' => '', 'bedPos' => '', 'shopPos'];
        file_put_contents($path, json_encode($json));
    }

    /**
     * @param string $id
     * @param string $team
     * @return bool
     */
    public function teamExists(string $id, string $team) : bool{
        $file = file_get_contents($this->gamePath($id));
        if($file == null){
            foreach($this->getServer()->getOnlinePlayers() as $p){
         $p->sendMessage("null file");
        }
            return false;
        }
        foreach($this->getServer()->getOnlinePlayers() as $p){
         $p->sendMessage(strtolower($team));
        }
        $json = json_decode($file, true);
        return isset($json['teamInfo'][strtolower($team)]);
    }

    /**
     * @param string $gameID
     * @return bool
     */
    public function gameExists(string $gameID) : bool {
        if(!is_file($this->gamePath($gameID))){
            return false;
        }
        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function isGameLoaded(string $id) : bool{
        return isset($this->games[$id]);
    }
    
    public function loadArena(string $id) : string{
        $location = $this->getDataFolder() . "games/" . $id . ".json";
        if(!is_file($location)){
            return "Game doesn't exist";
        }
        
        
        $file = file_get_contents($location);
        $jsonData = json_decode($file, true);
        if(!$this->validateGame($jsonData)){
            return "Failed to validate arena";
        }
        $this->games[$jsonData['name']] = $game = new Game($this, $jsonData);
        return null;
    }

    /**
     * @param string $id
     * @return string
     */
    public function gamePath(string $id) : string{
        return $this->getDataFolder() . "games/" . $id . ".json";
    }

    /**
     * @param array $arenaData
     * @return bool
     */
    public function validateGame(array $gameID) : bool{
        $requiredParams = [
            'name',
            'minPlayers',
            'playersPerTeam',
            'lobby',
            'world',
            'teamInfo',
            'generatorInfo',
            'void_y'
        ];

        $error = 0;
        foreach($requiredParams as $param){
            if(!in_array($param, array_keys($gameID))){
                $error ++;
            }
        }

        return !$error > 0;
    }

    /**
     * @param Player $player
     * @param bool $isSpectator
     * @return Game|null
     */
    public function getPlayerGame(Player $player, bool $isSpectator = false) : ?Game{
        $isSpectator = false;
        foreach($this->games as $game){
            if(isset($game->players[$player->getRawUniqueId()]))return $game;
            if(isset($game->spectators[$player->getRawUniqueId()]))return $game;
        }
        return null;
    }

    /**
     * @param Player $player
     * @return Team|null
     */
    public function getPlayerTeam(Player $player) : ?Team{
        $game = $this->getPlayerGame($player);
        if($game == null)return null;

        foreach($game->teams as $team){
            if(in_array($player->getRawUniqueId(), array_keys($team->getPlayers()))){
                return $team;
            }
        }
        return null;
    }

    public function writeGameData(string $id, array $gameID) : void{
        $location = $this->getDataFolder() . "games/" . $id . ".json";

        file_put_contents($location, json_encode($id, true));
    }
}