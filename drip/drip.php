<?php
class EDDDripApi
{
	var $api_url 		= 'https://api.getdrip.com/v2';
	var $api_key 		= 'zp7isdxrswdabrpts1vedh1z11ejkr2t';
	var $api_account_id = '6214945';
	var $api_campaign   = '1182943';
	
	function add_subscriber($email, $custom_fields = array())
	{
		$url = sprintf('/%s/campaigns/%s/subscribers', $this->api_account_id, $this->api_campaign);
		$payload = array(
			'status' => 'active',
			'subscribers' => array (
				array(
					'email' => $email,
					'utc_offset' => 660,
					'double_optin' => false,
					'starting_email_index' => 0,
					'reactivate_if_subscribed' => true,
					'custom_fields' => $custom_fields
				)
			)
		);
		
	      return	$this->execute_query($url, $payload);
               
	}
	
        //update infor of subscriber
        function update_subscriber($email, $custom_fields = array())
	{
		$url = sprintf('/%s/subscribers', $this->api_account_id);
		$payload = array(
			'subscribers' => array (
				array(
					'email' => $email,
					'utc_offset' => 660,
					'custom_fields' => $custom_fields
				)
			)
		);
		
	      return	$this->execute_query($url, $payload);
               
	}
        
        
        function fire_event($email, $action,$properties = array())
	{
		$url = sprintf('/%s/events', $this->api_account_id);
		$payload = array(
			
			'events' => array (
				array(
					'email' => $email,
					'action' => $action,					
					'properties' => $properties
				)
			)
		);
		
	      return	$this->execute_query($url, $payload);
               
	}
        
        
	private function execute_query($url_path, $payload = array())
	{
		$url = $this->api_url . $url_path;
		$data = json_encode($payload);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC) ; 
		curl_setopt($ch, CURLOPT_USERPWD, $this->api_key.':'); 
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if (count($payload) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.api+json'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, array('status' => 'active'));
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}
?>