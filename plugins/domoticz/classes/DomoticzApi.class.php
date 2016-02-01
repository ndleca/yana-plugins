<?php

class DomoticzApi {

	protected $conf;
	
	//0 = Integer, e.g. -1, 1, 0, 2, 10 
	//1 = Float, e.g. -1.1, 1.2, 3.1
	//2 = String
	//3 = Date in format DD/MM/YYYY
	//4 = Time in 24 hr format HH:MM
	protected $variableType = array('Integer','Float','String','Date','Time');

	function get($url){
		try {
				$ch = curl_init();
				$credentials = $this->getCredentials();
				$timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_USERPWD, $credentials);
				$data = curl_exec($ch);
				curl_close($ch);
				$infos = json_decode($data, true);
				return $infos;
		}
		catch (Exception $e) {
			return array();
		}
	}
	
	function __construct($conf){
		$this->conf = $conf;
	}

	function getCredentials(){
		$credentials =	$this->conf->get('user','domoticz');
		if($this->conf->get('pswd','domoticz')){
			$credentials .=	':'.$this->conf->get('pswd','domoticz');
		}
		return $credentials;
	}
	
	function getUrl(){
		$url = 'http://';
		$url .=	$this->conf->get('ip','domoticz').':'.$this->conf->get('port','domoticz');
		return $url;
	}
	
	function getDevices(){
			$lights = $this->getSwitches();
			$scenes = $this->getScenes();
			$temps = $this->getTemp();
			$utility = $this->getUtility();
			$variables = $this->getUserVariables();
			$devices = array_merge($lights,$scenes,$temps,$utility,$variables);
			return $devices;
		
	}
	
	function getSunRise(){
		$url =  $this->getUrl();
		$url .=	'/json.htm?type=command&param=getSunRiseSet';
		$infos = $this->get($url);
		return $infos;
	}
	
	
	function getInfo($id){
		$url =  $this->getUrl();
		$url .=	'/json.htm?type=devices&rid='.$id;
		$infos = $this->get($url);
		return $infos['result'][0];
	}

	function getSwitches(){
		// Get all lights/switches
		$lights_url =  $this->getUrl();
		$lights_url .=	'/json.htm?type=command&param=getlightswitches';
		$lights = $this->get($lights_url);
		$devices = array();
		if (is_array($lights)){
			foreach($lights as $row){
				if (is_array($row)){
					foreach($row as $row2){
						$row2['categorie']="switch";
						$devices[] =$row2;
		}}}}
		
		return $devices;
	}
	
	function setState($type,$id,$state){
		if($type == 'switchscene'){
			$idx = substr($id,1,strlen ($id));
		}else{$idx =$id;}
	
		$url = $this->getUrl();
		$url .= '/json.htm?type=command&param='.$type.'&idx='.urlencode($idx).'&switchcmd='.$state.'&level=0';
		$this->get($url);
	}
	
	function getScenes(){
		// Get all scenes/groups
		$scenes_url =  $this->getUrl();
		$scenes_url .= '/json.htm?type=scenes';
		$scenes = $this->get($scenes_url);
		$devices = array();
		if (is_array($scenes)){
			foreach($scenes as $row){
				if (is_array($row)){
					foreach($row as $row2){
						$row2['categorie']="scene";
						$row2['idx']="s".$row2['idx'];
						$devices[] =$row2;
		}}}}
		
		return $devices;
	}
	
	
	function getTemp(){
		// Get all temps
		$temps_url =  $this->getUrl();
		$temps_url .=	'/json.htm?type=devices&filter=temp&used=true&order=Name';
		$temps = $this->get($temps_url);
		$devices = array();
		if (is_array($temps)){
			foreach($temps as $row){
				if (is_array($row)){
					foreach($row as $row2){
						$row2['categorie']="mesure";
						$devices[] =$row2;
		}}}}
		
		return $devices;

	}
	
	function getUtility(){
		// Get all utility
		$url =  $this->getUrl();
		$url .=	'/json.htm?type=devices&filter=utility&used=true&order=Name';
		$temps = $this->get($url);
		$devices = array();
		if (is_array($temps)){
			foreach($temps as $row){
				if (is_array($row)){
					foreach($row as $row2){
						$row2['categorie']="utility";
						$devices[] =$row2;
		}}}}
		
		return $devices;

	}
	
	function getUserVariables(){
		// Get all uservariables
		$url =  $this->getUrl();
		$url .=	'/json.htm?type=command&param=getuservariables';
		$variables = $this->get($url);
		$devices = array();
		if (is_array($variables)){
			foreach($variables as $row){
				if (is_array($row)){
					foreach($row as $row2){
						$row2['categorie']="variable";
						$row2['idx']="v".$row2['idx'];
						$row2['Type']=$this->variableType[$row2['Type']];
						$devices[] =$row2;
		}}}}
		
		return $devices;

	}
	
	function getUserVariable($id){
		$idx = substr($id,1,strlen ($id));
		$url =  $this->getUrl();
		$url .=	'/json.htm?type=command&param=getuservariable&idx='.$idx;
		$infos = $this->get($url);
		return $infos['result'][0];
	}

}



?>
