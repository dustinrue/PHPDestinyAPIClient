<?php
  namespace PHPDestinyAPIClient;
  
  use PHPDestinyAPIClient\DestinyCommunication;
  
  define('DESTINY_PLATFORM_XBOX', 1);
  define('DESTINY_PLATFORM_PS', 2);
  define('DESTINY_ACTIVITY_NONE', 1);
  define('DESTINY_ACTIVITY_STORY', 2);
  define('DESTINY_ACTIVITY_STRIKE', 3);
  define('DESTINY_ACTIVITY_RAID', 4);
  define('DESTINY_ACTIVITY_ALLPVP', 5);
  define('DESTINY_ACTIVITY_PATROL', 6);
  define('DESTINY_ACTIVITY_ALLPVE', 7);
  define('DESTINY_ACTIVITY_PVPINTRODUCTION', 8);
  define('DESTINY_ACTIVITY_THREEVSTHREE', 9);
  define('DESTINY_ACTIVITY_CONTROL', 10);
  define('DESTINY_ACTIVITY_LOCKDOWN', 11);
  define('DESTINY_ACTIVITY_TEAM', 12);
  define('DESTINY_ACTIVITY_FREEFORALL', 13);
  define('DESTINY_ACTIVITY_TRIALS', 14);
  define('DESTINY_ACTIVITY_DOUBLES', 15);
  define('DESTINY_ACTIVITY_ALLSTRIKES', 16);
  define('DESTINY_ACTIVITY_NIGHTFALL', 17);
  define('DESTINY_ACTIVITY_STRIKE_OTHER', 18);
  define('DESTINY_ACTIVITY_IRONBANNER', 19);
  define('DESTINY_ACTIVITY_ARENA', 20);
  define('DESTINY_ACTIVITY_ARENA_OTHER', 21);
  define('DESTINY_ACTIVITY_ARENA_CHALLENGE', 22);
  define('DESTINY_ACTIVITY_ELIMINATION', 23);
  define('DESTINY_ACTIVITY_RIFT', 24);
  define('DESTINY_ACTIVITY_MAYHEM_CLASH', 26);
  define('DESTINY_ACTIVITY_MAYHEM_RUMBLE', 27);
  define('DESTINY_ACTIVITY_ZONE_CONTROL', 28);
  define('DESTINY_ACTIVITY_SRL', 29);


  class DestinyClient extends DestinyCommunication {

    /*
     * @return \DestinyClient
     */
    public function __construct($api_key) {
      parent::__construct($api_key);
    }

    public function fetchManifestById($type, $id) {
      $url = sprintf("https://www.bungie.net/Platform/Destiny/Manifest/%s/%s/", $type, $id);

      return $this->fetchData($url);
    }

    public function fetchPlayerDetails($username, $platform = DESTINY_PLATFORM_XBOX) {
      $username = str_replace(' ', '%20', $username);
      $url = sprintf("http://www.bungie.net/Platform/Destiny/SearchDestinyPlayer/%s/%s/", $platform, $username);

      return $this->fetchData($url);
    }

    public function fetchMembershipId($displayName, $membershipType = DESTINY_PLATFORM_XBOX) {
      $displayName = str_replace(' ', '%20', $displayName);
      $url = sprintf("http://www.bungie.net/Platform/Destiny/%s/Stats/GetMembershipIdByDisplayName/%s/", $membershipType, $displayName);

      return $this->fetchData($url);
    }

    public function fetchCharacters($membershipId, $platform = DESTINY_PLATFORM_XBOX) {
      $url = sprintf("http://www.bungie.net/Platform/Destiny/%s/Account/%s/", $platform, $membershipId);

      return $this->fetchData($url);
    }

    public function fetchActivity($membershipId, $characterId, $page = 0, $activityType = 'None', $definitions = 'false', $platform = DESTINY_PLATFORM_XBOX) {
      $url = sprintf("http://www.bungie.net/Platform/Destiny/Stats/ActivityHistory/%s/%s/%s/?mode=%s&page=%s&definitions=%s", $platform, $membershipId, $characterId, $activityType, $page, $definitions);

      return $this->fetchData($url);
    }

    public function fetchPostGameCarnageReport($activityId) {
      $url = sprintf("http://www.bungie.net/Platform/Destiny/Stats/PostGameCarnageReport/%s/", $activityId);

      return $this->fetchData($url);
    }
    
    public function fetchCharacterStats($membershipId, $characterId, $mode = NULL, $platform = DESTINY_PLATFORM_XBOX) {
      if ($mode)
        $url = sprintf("http://www.bungie.net/Platform/Destiny/Stats/%s/%s/%s/?modes=%s", $platform, $membershipId, $characterId, $mode);
      else
        $url = sprintf("http://www.bungie.net/Platform/Destiny/Stats/%s/%s/%s/", $platform, $membershipId, $characterId);
        
      return $this->fetchData($url);
    }
  }
