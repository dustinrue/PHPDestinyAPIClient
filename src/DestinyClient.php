<?php
  namespace PHPDestinyAPIClient;
  
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
  

  // Builds the raw requests for DestintyCommunication and returns
  // the raw JSON. Minimal error handling.
  // All methods can be batched (batch(true)) but it's up to the calling application
  // to determine if it makes sense.
  class DestinyClient extends DestinyCommunication {
    /* @var Url Bungie API root */
    private $apiRoot;
    
    /* @var string Bungie API Key used for all requests */
    private $apiKey;
    
    
    /*
     * @return \PHPDestinyAPIClient\DestinyClient
     */
    public function __construct($apiKey) {
      parent::__construct();
      
      if (!$apiKey) {
        throw new \Exception("You must provide your Bungie API key from https://www.bungie.net/en/User/API");
      }
      
      $this->apiKey = $apiKey;
      $this->setApiRoot("https://www.bungie.net/Platform/Destiny");
    }
    
    /**
     * Set the apiRoot
     * @param string apiRoot new API root URL, no trailing slash "https://www.bungie.net/Platform/Destiny"
     */
    public function setApiRoot($apiRoot) {
      $this->apiRoot = $apiRoot;
    }

    /**
     * Returns the current version of the manifest as a json object.
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchManifest() {
      $url = sprintf("%s%s", $this->apiRoot, "/Manifest/");

      return $this->fetchData($url);
    }
    
    public function fetchExplorerItems() {
      $url = sprintf("%s%s", $this->apiRoot, "/Explorer/Items/?definitions=true");
      
      return $this->fetchData($url);
    }
    
    /**
     * Fetches all items that a user has
     * @param string $membershipId
     * @param string $platform
     */
    public function fetchMemberItems($membershipId, $platform = DESTINY_PLATFORM_XBOX) {
      $url = sprintf("%s/%s/Account/%s/Items/", $this->apiRoot, $platform, $membershipId);
      
      return $this->fetchData($url);
    }
    /**
     * Returns the specific item from the current manifest a json object.
     * @param string $type
     * @param string $id
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchManifestById($type, $id) {
      $url = sprintf("%s/Manifest/%s/%s/", $this->apiRoot, $type, $id);

      return $this->fetchData($url);
    }

    /**
     * Fetches player details for given Gamertag or PSN ID
     * @param string $gamertag Gamertag or PSN id
     * @param string $platform (\PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX | \PHPDestinyAPIClient\DESTINY_PLATFORM_PS). Defaults to \PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX.
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchPlayerDetails($gamertag, $platform = DESTINY_PLATFORM_XBOX) {
      $gamertag = str_replace(' ', '%20', $gamertag);
      $url = sprintf("%s/SearchDestinyPlayer/%s/%s/", $this->apiRoot, $platform, $gamertag);

      return $this->fetchData($url);
    }

    /**
     * Fetches membership id for given Gamertag or PSN ID
     * @param string $gamertag Gamertag or PSN ID
     * @param string $platform (\PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX | \PHPDestinyAPIClient\DESTINY_PLATFORM_PS). Defaults to \PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX.
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchMembershipId($gamertag, $membershipType = DESTINY_PLATFORM_XBOX) {
      $gamertag = str_replace(' ', '%20', $gamertag);
      $url = sprintf("%s/%s/Stats/GetMembershipIdByDisplayName/%s/", $this->apiRoot, $membershipType, $gamertag);

      return $this->fetchData($url);
    }

    /**
     * Fetches character data for given membership id
     * @param string $membershipId membership id of user to fetch characters for
     * @param string $platform (\PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX | \PHPDestinyAPIClient\DESTINY_PLATFORM_PS). Defaults to \PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX.
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchCharacters($membershipId, $platform = DESTINY_PLATFORM_XBOX) {
      $url = sprintf("%s/%s/Account/%s/Summary/", $this->apiRoot, $platform, $membershipId);

      return $this->fetchData($url);
    }

    /**
     * Fetches activity data for given membership id, character id and activity type
     * @param string $membershipId membership id
     * @param string $characterId character id
     * @param string $activityType activity type id, use DESTINY_ACTIVITY_ALLPVP, etc
     * @param string $page what page number to return
     * @param string $definitions set to true to get activity definitions back
     * @param string $platform (\PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX | \PHPDestinyAPIClient\DESTINY_PLATFORM_PS). Defaults to \PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX.
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchActivity($membershipId, $characterId, $activityType = 'None', $page = 0, $platform = DESTINY_PLATFORM_XBOX, $definitions = 'false') {
      $url = sprintf("%s/Stats/ActivityHistory/%s/%s/%s/?mode=%s&page=%s&definitions=%s", $this->apiRoot, $platform, $membershipId, $characterId, $activityType, $page, $definitions);

      return $this->fetchData($url);
    }

    /**
     * Fetches post game carnage report for an activity id
     * @param string $activityId the activity id
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchPostGameCarnageReport($activityId) {
      $url = sprintf("%s/Stats/PostGameCarnageReport/%s/", $this->apiRoot, $activityId);

      return $this->fetchData($url);
    }
    
    /**
     * Fetches character stats for givin membership and character id
     * @param string $membershipId the membership id of the user
     * @param string $characterId the character id for the character you want to get stats for
     * @param string $activityType activity type id, use DESTINY_ACTIVITY_ALLPVP, etc
     * @param string $platform (\PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX | \PHPDestinyAPIClient\DESTINY_PLATFORM_PS). Defaults to \PHPDestinyAPIClient\DESTINY_PLATFORM_XBOX.
     * @return string The raw JSON as returned from Bungie's API
     */
    public function fetchCharacterStats($membershipId, $characterId, $activityType = NULL, $platform = DESTINY_PLATFORM_XBOX) {
      if ($mode)
        $url = sprintf("%s/Stats/%s/%s/%s/?modes=%s", $this->apiRoot, $platform, $membershipId, $characterId, $mode);
      else
        $url = sprintf("%s/Stats/%s/%s/%s/", $this->apiRoot, $platform, $membershipId, $characterId);
        
      return $this->fetchData($url);
    }
    
    /**
     * Overrides parent to ensure X-API-Key header is set for each request
     * @param string $url Url for GET or POST request. 
     * @param string $json JSON string that might be posted to service
     * @return string The raw JSON as returned from Bungie's API
     */
    protected function fetchData($url, $json = null) {
      $this->setHeader("X-API-Key", $this->apiKey);
      return parent::fetchData($url, $json);
    }
  }
