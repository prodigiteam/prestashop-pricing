<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomPrice extends Module
{
    public function __construct()
    {
        $this->name = 'customprice';
        $this->author = 'ProdigiService';
	$this->url = 'https://www.prodigiservice.fr/api-rest/get-request-json?requestId=';
	$this->privateKey = 'xxxx-xxxxx-xx-xxx-xxxxxx-xxxxxxx';
	$this->userName = 'xxxxxxx.prodigiservice.fr';
	$this->version = '1.0.0';
        parent::__construct();
	$this->ps_versions_compliancy = ['min' => '8.0.1', 'max' => _PS_VERSION_];
        $this->displayName = $this->l('Custom Price');
        $this->description = $this->l('Call ProdigiService API and calculate the average price for each product');
    }

    public function install()
    {
        return parent::install() && $this->registerHook('actionProductPriceCalculation');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

   public function callAPI($method, $url, $data){
	   $curl = curl_init();
	   switch ($method){
	      case "POST":
		 curl_setopt($curl, CURLOPT_POST, 1);
		 if ($data)
		    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		 break;
	      case "PUT":
		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		 if ($data)
		    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		 break;
	      default:
		 if ($data)
		    $url = sprintf("%s?%s", $url, http_build_query($data));
	   }
	   // OPTIONS:
	   curl_setopt($curl, CURLOPT_URL, $url);
	   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	      'Private-Key: '.$this->privateKey,
	      'User-Name:'.$this->userName,
	   ));
	   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	   // EXECUTE:
	   $result = curl_exec($curl);
	   //if(!$result){die("Connection Failure");}
	   curl_close($curl);
	   return $result;
   }


    public function hookActionProductPriceCalculation($params)
    {
	$mpn = '';
	$sql = 'SELECT  pa.`mpn` FROM `' . _DB_PREFIX_ . 'product` pa WHERE pa.`id_product` = ' . (int) $params['id_product'] . '';

	foreach (Db::getInstance()->executeS($sql) as $row) {
		  $mpn = $row['mpn'];
	}

	if(!empty($mpn)) {
		$temp = [];
		try{
		    	$url = $this->url.''.$mpn;
			$get_data = "";
			$get_data = $this->callAPI('GET', $url, false);
			if(!empty($get_data)) {
				$response = json_decode($get_data, true);
				$errorApi = $response['errorApi'];
				if($errorApi == null){

					foreach ($response['comparators'] as $row) {
						array_push($temp, $row['price']);
					}

					$sum = array_sum($temp);
					$count =count($temp)+0.0001;

				 	$average =  $sum/$count;

					$minimalPrice = $response['minimalPrice'];
					if($minimalPrice == null){
						$params['price'] = $average;
					}else{
						$minimalPrice = (float) $minimalPrice;
						if($average < $minimalPrice){
							$params['price'] = $minimalPrice;
						}else{
							$params['price'] = $average;
						}
					}
				}

			}

		} catch(Exception $e){
		}

	}

    }

}

