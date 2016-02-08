Destiny API Client in PHP
=========================

This simplistic API client provides simplified, raw access to Bungie's Destiny API 
allowing you to build upon it however you need. The key feature in this API client is 
its ability to perform requests in parallel by enabling batch mode, creating a
number of requests, performing the batch and dealing with the data. Example below.

Currently in development but you're free to try it using composer

```
    {
        "require": {
            "phpdestinyapiclient/phpdestinyapiclient": "dev-master"
        }
    }
```

in composer.json

Example
-------

```
#!/usr/bin/php
<?php
  // displayes number of times a player has played with or against
  // another player

  $use_batch = false;

  require_once 'vendor/autoload.php';

  $destiny = new PHPDestinyAPIClient\DestinyClient(<your Bungie API key>);

  // on UNIX/Linux we can log to syslog, this is quite primitive but it'll display
  // what requests were formed and how long they took or in the case of a batch
  // how long the batch took
  $destiny->logLevel(\PHPDestinyAPIClient\DestinyLogger::debug);

  // fetch my player details
  $player = json_decode($destiny->fetchPlayerDetails('RealAngryMonkey'));

  $membershipId = $player->Response[0]->membershipId;

  $results = array();
  function pullInstanceIds($results, $item) {
    $results[] = $item->activityDetails->instanceId;
    return $results;
  }

  // fetch all activity for my Hunter
  $page = 0;
  $instanceIds = array();
  while(true) {
    $data = $destiny->fetchActivity($membershipId, '2305843009320446325', $page, DESTINY_ACTIVITY_TRIALS);
    $activity = json_decode($data);
    if (!property_exists($activity->Response->data, "activities"))
      break;

    $instanceIds = array_merge($instanceIds, array_reduce($activity->Response->data->activities, "pullInstanceIds"));
    $page++;
  }

  $people = array();

  // using the batch method
  if ($use_batch) {
    $results = array();
    $destiny->batch(1);
    foreach($instanceIds AS $instanceId) {
      $destiny->fetchPostGameCarnageReport($instanceId);
    }
    $results = $destiny->performBatch(); 


    // $results is an array of Guzzle http responses
    // so we walk that array and pull out the body for each.
    // It also contains what URL caused the response so if you 
    // manually track the requests are you sending then you can
    // determine where the data goes. In this example it's very 
    // straight forward, we simply deal with the data
    foreach($results AS $result) {
      $data  = json_decode(sprintf("%s", $result['body']));
      foreach($data->Response->data->entries AS $player) {
        $gt = $player->player->destinyUserInfo->displayName;
        $id = $player->player->destinyUserInfo->membershipId;
        if (!array_key_exists($gt, $people))
          $people[$gt] = 0;

        $people[$gt]++;
      }
    }
  }
  // not the batch method
  else {
    foreach($instanceIds AS $instanceId) {
      $data = json_decode($destiny->fetchPostGameCarnageReport($instanceId));
      foreach($data->Response->data->entries AS $player) {
        // notice here that our processing is exactly the same
        // as above
        $gt = $player->player->destinyUserInfo->displayName;
        $id = $player->player->destinyUserInfo->membershipId;
        if (!array_key_exists($gt, $people))
          $people[$gt] = 0;

        $people[$gt]++;

      }
    }
  }

  arsort($people);
  print_r($people);
```



Results
-------

**Batched**
```
time ./test.php
real	0m1.169s
user	0m0.123s
sys	0m0.046s
```

**Not batched**
```
time ./test.php
real	0m5.739s
user	0m0.073s
sys	0m0.040s
```