<?php


namespace BedWars\game\shop;


use BedWars\BedWars;
use BedWars\game\Game;
use BedWars\utils\Utils;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

class ItemShop
{

    const PURCHASE_TYPE_IRON = 0;
    const PURCHASE_TYPE_GOLD = 1;
    const PURCHASE_TYPE_EMERALD = 2;

    /**
     * @var array $shopWindows
     */
    public static $shopWindows = [
        1 => ["name" =>"§l§aArmor"],
        2 => ["name" =>"§l§aWeapons"],
        3 => ["name" =>"§l§aBlocks"],
        4 => ["name" =>"§l§aTools"],
        5 => ["name" => "§l§aBows"],
        6 => ["name" => "§l§aPotions"],
        7 => ["name" => "§l§aSpecials"]
    ];

    /**
     * @var array $shopPages
     */
    public static $shopPages = [
        0 => ["§6Chainmal Armor\n§l§e40 IRON" => [],
            "§6Iron Armor §c[PERMANENT]\n§l§e12 GOLD" => [],
            "§6Diamond Armor §c[PERMANENT]\n§l§e6 EMERALD" => []],
        1 => ["§6Stone Sword\n§l§e10 IRON" => [],
            "§6Iron Sword\n§l§e7 GOLD" => [],
            "§6Diamond Sword\n§l§e7 EMERALD" => [],
            "§6Knockback Stick\n§l§e2 EMERALD" => []],
        2 => ["§6Wool 16x\n§l§e4 IRON" => [],
            "§6Sandstone 16x\n§l§e12 IRON" => [],
            "§6Endstone 12x\n§l§e24 IRON" => [],
            "§6Ladder 16x\n§l§e4 IRON" => [],
            "§6Oak Wood 16x\n§l§e4 GOLD" => [],
            "§6Obsidian 4x\n§l§e4 EMERALD" => []],
        3 => ["§6Shears\n§l§e10 IRON" => [],
            "§6Wooden Axe\n§l§e10 IRON" => [],
            "§6Wooden Pickaxe\n§l§e10 IRON" => [],
            "§6Golden Axe\n§l§e20 IRON" => [],
            "§6Golden Pickaxe\n§l§e20 IRON" => [],
            "§6Diamond Axe\n§l§e20 GOLD" => [],
            "§6Diamond Pickaxe\n§l§e20 GOLD" => []],
        4 => ["§6Normal Bow\n§l§e12 GOLD" => [],
            "§6Bow §b(Power 1)\n§l§e24 GOLD" => [],
            "§6Bow §b(Power 1, Punch 1)\n§l§e6 EMERALD" => [],
            "§6Arrow x10 §b\n§l§e10 IRON"=> []],
        5 => ["§6Speed II Potion (45 sec.)\n§l§e1 EMERALD" => [],
            "§6Jump V Potion (45 sec.)\n§l§e1 EMERALD" => []],
        6 => ["§6Golden Apple\n§l§e3 GOLD" => [],
            "§6Enderpearl\n§l§e4 EMERALD" => [],
            "§6Water Bucket\n§l§e1 EMERALD" => []
        ],
    ];

    /**
     * @var array $itemData
     */
    public static $itemData = [
        0 => [0 => ["name" => "Chainmal Armor", "type" => self::PURCHASE_TYPE_IRON, "amount" => 0, "price" => 40, "item" => ["id" => Item::CHAIN_LEGGINGS, "damage" => 0]],
            1 => ["name" => "Iron Armor", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 0, "price" => 12, "item" => ["id" => Item::IRON_LEGGINGS, "damage" => 0]],
            2 => ["name" => "Diamond Armor ", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 0, "price" => 6, "item" => ["id" => ITEM::DIAMOND_LEGGINGS, "damage" => 0]]
        ],
        1 => [0 => ["name" => "Stone Sword", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 10, "item" => ["id" => Item::STONE_SWORD, "damage" => 0]],
            1 => ["name" => "Iron Sword", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 7, "item" => ["id" => Item::IRON_SWORD, "damage" => 0]],
            2 => ["name" => "Diamond Sword", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 7, "item" => ["id" => Item::DIAMOND_SWORD, "damage" => 0]],
            3 => ["name" => "Knockback Stick", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 2, "item" => ["id" => Item::STICK, "damage" => 0]]
        ],
        2 => [0 => ["name" => "Wool 16x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 16, "price" => 4, "item" => ["id" => Item::WOOL, "damage" => "depend"]],
            1 => ["name" => "Sandstone 16x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 16, "price" => 12, "item" => ["id" => Item::SANDSTONE, "damage" => 0]],
            2 => ["name" => "Endstone 12x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 12, "price" => 24,"item" => ["id" => Item::END_STONE, "damage" => 0]],
            3 => ["name" => "Ladder 16x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 16, "price" => 4,"item" => ["id" => Item::LADDER, "damage" => 0]],
            4 => ["name" => "Oak Wood 16x", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 16, "price" => 4, "item" => ["id" => 5, "damage" => 0]],
            5 => ["name" => "Obsidian 4x", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 4, "price" => 4, "item" => ["id" => Item::OBSIDIAN, "damage" =>0]]
        ],
        3 => [0 => ["name" => "Shears", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 10, "item" => ["id" => Item::SHEARS, "damage" => 0]],
            1 => ["name" => "Wooden Axe", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 10, "item" => ["id" => Item::WOODEN_AXE, "damage" => 0]],
            2 => ["name" => "Wooden Pickaxe", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 10, "item" => ["id" => Item::WOODEN_PICKAXE, "damage" => 0]],
            3 => ["name" => "Golden Axe", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 20, "item" => ["id" => Item::GOLDEN_AXE, "damage" => 0]],
            4 => ["name" => "Golden Pickaxe", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" =>20, "item" => ["id" => Item::GOLDEN_PICKAXE, "damage" => 0]],
            5 => ["name" => "Diamond Axe", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 20, "item" => ["id" => Item::DIAMOND_AXE, "damage" => 0]],
            6 => ["name" => "Diamond Pickaxe", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 20, "item" => ["id" => Item::DIAMOND_PICKAXE, "damage" => 0]]
        ],
        4 => [0 => ["name" => "Bow 1", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 12, "item" => ["id" => Item::BOW, "damage" => 0]],
            1 => ["name" => "Bow 2", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 24, "item" => ["id" => Item::BOW, "damage" => 0]],
            2 => ["name" => "Bow 3", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 6, "item" => ["id" => Item::BOW, "damage" => 0]],
                 ["name" => "Arrow", "type" => self::PURCHASE_TYPE_IRON, "amount" => 10, "price" => 10, "item" => ["id" => Item::ARROW, "damage" => 0]]
        ],
        5 => [0 => ["name" => "Potion of Speed", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => Item::POTION, "damage" => 8194]],
            1 => ["name" => "Jump Potion", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => Item::POTION, "damage" => 8203]],
            2 => ["name" => "Invisibility Potion", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => Item::POTION, "damage" => 8206]]
        ],
        6 => [0 => ["name" => "Golden Apple", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 3, "item" => ["id" => Item::GOLDEN_APPLE, "damage" => 0]],
            1 => ["name" => "Fireball", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 50, "item" => ["id" => Item::FIRE_CHARGE, "damage" => 0]],
            2 => ["name" => "Enderpearl", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 4, "item" => ["id" => Item::ENDER_PEARL, "damage" => 0]],
            3 => ["name" => "Water Bucket", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => 326, "damage" => 0]]
        ]
    ];

    /**
     * @param int $category
     * @return mixed
     */
    public static function getCategory(int $category){
        return self::$shopWindows[$category];
    }

    /**
     * @param int $id
     * @param $data
     * @param Player $p
     * @param BedWars $plugin
     */
    public static function handleTransaction(int $id, $data, Player $p, BedWars $plugin){
        if(is_null($data)){
            return;
        }
        $itemData = self::$itemData[$id][$data];
        $amount = $itemData['amount'];
        $price = $itemData['price'];
        $id = $itemData['item']['id'];
        $damage = (int)$itemData['item']['damage'];
        $p->sendMessage($itemData["amount"] . " & " . $itemData["price"]);
        $check = "";
        $type = $itemData['type'];
        $typeString = "";
        $removeItem = null;
        switch($type){
            case self::PURCHASE_TYPE_IRON;
                $typeString = "iron";
                $removeItem = Item::get(Item::IRON_INGOT, 0, $price);
                $check = $p->getInventory()->contains(Item::get(Item::IRON_INGOT, $damage, $price));
                break;
            case self::PURCHASE_TYPE_GOLD;
                $typeString = "gold";
                $removeItem = Item::get(Item::GOLD_INGOT, 0 , $price);
                $check = $p->getInventory()->contains(Item::get(Item::GOLD_INGOT, $damage, $price));
                break;
            case self::PURCHASE_TYPE_EMERALD;
                $typeString = "emerald";
                $removeItem = Item::get(Item::EMERALD, 0, $price);
                $check = $p->getInventory()->contains(Item::get(Item::EMERALD, $damage, $price));
                break;
        }

        if(!$check){
            $p->sendMessage("§cYou don't have enough " . strtolower(ucfirst($typeString)) . " to purchase this item!");
            return;
        }

        $playerTeam = $plugin->getPlayerTeam($p);
        if($playerTeam == null)return;


        if($id == Item::WOOL){
            $damage = Utils::colorIntoWool($playerTeam->getColor());
        }elseif(Item::get($id) instanceof Armor){
            self::handleArmorTransaction($data, $p);
            return;
        }
        $item = Item::get($id, $damage, $amount);
        $wasPurchased = false;

        //handle custom sword transactions
        foreach($p->getInventory()->getContents() as $index => $content){
            if(self::isSword($content->getId()) && self::isSword($id)) {
                $wasPurchased = true;
                if ($id !== $content->getId()) {
                    $p->getInventory()->removeItem($content);
                    $p->getInventory()->setItem($index, $item);
                }else{
                    $p->sendMessage("§cYou already have this sword!");
                    return;
                }
            }
        }
        $p->sendMessage("§aYou have sucesfully purchased §e" . $itemData['name'] . " §afor §e" . $price . " " .  ucfirst($typeString));
        if($wasPurchased){
            return;
        }

        if($id == Item::BOW){
            self::handleBowTransaction($data, $item);
        }

        $p->getInventory()->removeItem($removeItem);
        $p->getInventory()->addItem($item);
    }

    /**
     * @param int $data
     * @param Player $p
     */
    public static function handleArmorTransaction(int $data, Player $p){
        $data = intval($data);
        $boots = "";
        $leggings = "";
        switch ($data){
            case 0;
                $boots = Item::get(Item::CHAIN_BOOTS, 0, 1);
                $leggings = Item::get(Item::CHAIN_LEGGINGS, 0, 1);
                break;
            case 1;
                $boots = Item::get(Item::IRON_BOOTS);
                $leggings = Item::get(Item::IRON_LEGGINGS);
                break;
            case 2;
                $boots = Item::get(Item::DIAMOND_BOOTS);
                $leggings = Item::get(Item::DIAMOND_LEGGINGS);
        }
        $p->getArmorInventory()->setBoots($boots);
        $p->getArmorInventory()->setLeggings($leggings);
    }

    /**
     * @param int $data
     * @param Item $item
     */
    public static function handleBowTransaction(int $data, Item $item){
        switch ($data){
            case 1;
                $enchantment = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::POWER), 1);
                $item->addEnchantment($enchantment);
                break;
            case 2;
                $enchantment = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::POWER), 1);
                $enchantment1 = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PUNCH), 1);
                foreach([$enchantment, $enchantment1] as $eIns){
                     $item->addEnchantment($eIns);
                }
        }
    }

    /**
     * @param int $itemId
     * @return bool
     */
    public static function isSword(int $itemId){
        $swords = [Item::IRON_SWORD, Item::STONE_SWORD, Item::WOODEN_SWORD, Item::DIAMOND_SWORD];
        if(in_array($itemId, $swords)){
            return true;
        }
        return false;
    }

    /**
     * @param int $itemId
     * @return bool
     */
    public static function isArmor(int $itemId){
        $armors = [Item::CHAIN_BOOTS, Item::CHAIN_BOOTS, Item::IRON_BOOTS, Item::IRON_LEGGINGS, Item::DIAMOND_BOOTS, Item::DIAMOND_LEGGINGS];
        if(in_array($itemId, $armors)){
            return true;
        }
        return false;
    }

    /**
     * @param Player $p
     */
    public static function sendDefaultShop(Player $p){
        $data['title'] = "§cSHOP";
        $data['type'] = "form";
        $data['content'] = "";
        foreach(self::$shopWindows as $windows){
            $button =  ['text' => $windows['name']];
            $data['buttons'][] = $button;
        }

        $packet = new ModalFormRequestPacket();
        $packet->formId = 50;
        $packet->formData = json_encode($data);
        $p->dataPacket($packet);

    }


    /**
     * @param Player $p
     * @param int $page
     */
    public static function sendPage(Player $p, int $page){
        $formId = $page;
        $data['title'] = 'Page ' . $page;
        $data['type'] = 'form';
        $page = self::$shopPages[$page];
        $data['content'] = "";
        foreach($page as $itemsToBuy => $key){
            $string = strval($itemsToBuy);
            $button = ['text' => $string];
            $data['buttons'][] = $button;
        }

        $packet = new ModalFormRequestPacket();
        $packet->formId = $formId;
        $packet->formData = json_encode($data);
        $p->dataPacket($packet);

    }



}