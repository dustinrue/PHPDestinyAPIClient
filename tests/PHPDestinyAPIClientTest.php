<?php

class PHPDestinyAPIClientTest extends PHPUnit_Framework_TestCase {
  public function testFetchMembershipId() {
    $destiny = new PHPDestinyAPIClient\DestinyClient(getenv('BUNGIEAPIKEY'));
    $raw_json = $destiny->fetchMembershipId('RealAngryMonkey');
    $this->assertJson($raw_json);
    $this->assertAttributeEquals("Success", "ErrorStatus", json_decode($raw_json));
  }
  
  public function testFetchMembershipIdTwoSystems() {
    $destiny = new PHPDestinyAPIClient\DestinyClient(getenv('BUNGIEAPIKEY'));
    $raw_json = $destiny->fetchMembershipId('RealAngryMonkey', DESTINY_PLATFORM_PS);
    $this->assertJson($raw_json);
    $this->assertAttributeNotEquals("Success", "ErrorStatus", json_decode($raw_json));
  }
  
  public function testFetchCharacters() {
    $destiny = new PHPDestinyAPIClient\DestinyClient(getenv('BUNGIEAPIKEY'));
    $membershipId = json_decode($destiny->fetchMembershipId('RealAngryMonkey'))->Response;
    $characters_raw = $destiny->fetchCharacters($membershipId);
    $this->assertJson($characters_raw);
    $this->assertAttributeEquals("Success", "ErrorStatus", json_decode($characters_raw));
  }
}
