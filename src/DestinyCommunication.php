<?php
  namespace PHPDestinyAPIClient;
  
  // Primarily handles the actual communcation with Bungie's Destiny API
  // including single requests as well as batched requests
  class DestinyCommunication {
    
    /* @var object curl handle used throughout */
    private $ch;
    
    /* @var */
    private $logger;
    
    /* @var bool sets batch behavior */
    private $batch;
    
    /* @var Url Last URL used for a request */
    var $url; //last url created
    
    /* @var array Tracks list of all batch items */
    var $batch_items;
    
    public function __construct() {
      $headers = $this->clearHeaders();
      $this->logger = new DestinyLogger();
      $this->logLevel(DestinyLogger::error);
    }
    
    public function logLevel($level) {
      $this->logger->level = $level;
    }
    
    
    public function request($url, $post_data = null, $use_header = 0) {
      
      $this->ch = curl_init();
      curl_setopt($this->ch, CURLOPT_URL, $url);
      curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array();
     
      if (count($this->headers) > 0) {
        foreach($this->headers AS $header_name => $header_value) {
          $this->logger->log(sprintf("Adding header: %s", $header_name), DestinyLogger::debug_with_headers);
          $headers[] = sprintf("%s: %s", $header_name, $header_value);
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
      }
      else {
        $this->logger->log("No headers to set", DestinyLogger::debug_with_headers);
      }
      
      if ($post_data) {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
        
        if (is_array($post_data)) {
          curl_setopt($this->ch, CURLOPT_POST, count($post_data));
        }
      }
      
      curl_setopt($this->ch, CURLOPT_VERBOSE, 0);
      curl_setopt($this->ch, CURLOPT_HEADER, $use_header);
      
      
      if ($this->logger->level == DestinyLogger::debug_with_headers) {
        foreach($headers AS $header) {
          $this->logger->log(sprintf("    %s", $header), DestinyLogger::debug_with_headers);
        }
        $this->logger->log(sprintf(" \n\n"), DestinyLogger::debug_with_headers);
      }
      
      $time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $start = $time;$time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $start = $time;
      
      if ($this->batch) {
        // add to a batch list
        $request = new \stdClass();
        $request->xlcObject = clone $this;
        $request->url = $url;
        $request->post_data = $post_data;
        $this->batch_items[] = clone $request;
        unset($request);
        $this->url = $url;
        return;
      }
      else {
        // perform the request now
        $this->logger->log(sprintf("Accessing %s", $url), DestinyLogger::debug);
        $results = curl_exec($this->ch);
      }
      
      $time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $finish = $time;
      $total_time = round(($finish - $start), 3);
      
      $this->logger->log(sprintf("Accessing: '%s' took %sms", $url, $total_time), DestinyLogger::debug);
      
      
      if ($this->logger->level == DestinyLogger::debug_with_headers) {
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        printf("%s\n\n", curl_exec($this->ch));
      }
      $this->clearHeaders();
      //curl_close($this->ch);  
      return $results;
    }
    
    public function performBatch() {
      $client = new \GuzzleHttp\Client(array(), array());
      $time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $start = $time;$time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $start = $time;
      // does the batch operations
      $responses = array();
      $requests = array();
      $this->logger->log(sprintf("starting batch"), DestinyLogger::debug);
      foreach($this->batch_items AS $xlc) {
        $headers = $xlc->xlcObject->headers;
        $url = $xlc->url;
        $post_data = $xlc->post_data;
        
        if ($post_data) {
          $this->logger->log(sprintf("adding post '%s' to batch", $url), DestinyLogger::debug);

          $req = $client->createRequest('POST', $url, 
            array(
              'future' => false,
              'debug' => false,
              'body' => $post_data,
            ) 
          );   
        }
        else {
          $this->logger->log(sprintf("adding get '%s' to batch", $url), DestinyLogger::debug);
          $req = $client->createRequest('GET', $url, 
            array(
              'future' => false,
              'debug' => false,
            )
          );
        }
        
        foreach($headers AS $header => $value) {
          $req->setHeader($header, $value);
        }
        
        
        $requests[] = $req;
        
      }
      $time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $start = $time;$time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $start = $time;
      
      $results = \GuzzleHttp\Pool::batch($client, $requests);
     
      
      $time = microtime();
      $time = explode(' ', $time);
      $time = $time[1] + $time[0];
      $finish = $time;
      $total_time = round(($finish - $start), 3);
      $this->logger->log(sprintf("batch of %s took %ss", count($requests), $total_time), DestinyLogger::debug);
      
      foreach ($results->getSuccessful() as $response) {
        $responses[] = array(
          'body' => $response->getBody(),
          'request' => $response->getEffectiveUrl(),
        );
      }
      
      foreach($results->getFailures() AS $failures) {
        dpm($failures);
      }
      unset($this->batch_items);
      return $responses;
    }
    
    public function setHeader($header_name, $header_value) {
      $this->headers[$header_name] = $header_value;
    }
    
    private function clearHeaders() {
      $this->headers = array();
    }
    
    
    
    protected function fetchData($url, $json = null) {
      //$this->setHeader('Content-Type', 'application/json');
      //$this->setHeader('Accept', 'application/json');
      
      
      if ($json) {
        $this->setHeader('Content-Length', strlen($json));
      }
      
      return $this->request($url, $json);
    }
    
    public function buildParameterString($params = array()) {
      
      $output = "";
      foreach($params AS $key => $value) {
        $output .= sprintf("%s=%s&", $key, $value);
      }

      return substr($output, 0, strlen($output) - 1);
    }
    
    public function sendData($url, $json) {
      print_r($this->fetchData($url, $json));
    }
    
    public function batch($is_batch = 0) {
      $this->batch = $is_batch;
    }
  }
  
  
  
  class DestinyCookieJar {
    public $cookiejar;
    
    public function setCookieJar($id = null) {
      if (!$id) {
        $this->cookiejar = DestinyCookieJar::generateCookieJarName();
      }
      else {
        $cookiejar = DestinyCookieJar::generateCookieJarName($id);
        
        // if cookiejar is already defined and the new file doesn't exist
        // then we're tranisitioning from authorization to doing work
        if ($this->cookiejar && !file_exists($cookiejar))
          rename($this->cookiejar, $cookiejar);
        else
          unlink($this->cookiejar);
        
        $this->cookiejar = $cookiejar;
      }
      
    }
    
    private static function generateCookieJarName($id = null) {
      if ($id)
        return sprintf("%s/destiny_cookies_%s", realpath('/tmp'), $id);
      else
        return realpath('/tmp') .  '/destiny_cookies';
    }
  }
  
  class DestinyLogger {
    var $level;
    
    const none = 0;
    const error = 1;
    const debug = 2;
    const debug_with_headers = 3;
    

    public function log($message, $level) {
      if (isset($this->level) && $level <= $this->level) {
        syslog(LOG_ERR, sprintf("%s - %s", __NAMESPACE__, $message));
      }
    }
  }
  
