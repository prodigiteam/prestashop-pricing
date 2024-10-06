<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class DynamicPrice extends Module
{
    public function __construct()
    {
        $this->name = 'dynamicprice';
        $this->author = 'ProdigiService';
	$this->url = 'https://api.prodigiservice.fr/api-rest/get-request-json?requestId=';
	$this->privateKey = 'xxxxx-xxxxx-xxxxx-xxxxx-xxxx';
	$this->userName = 'xxxxxx.prodigiservice.fr';
	$this->version = '1.0.0';
        parent::__construct();
	$this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];
        $this->displayName = $this->l('Dynamic price');
        $this->description = $this->l('Dynamic price vous permet de définir dynamiquement vos prix en fonction de l’offre concurrente la plus basse');
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
		try{
		    	$url = $this->url.''.$mpn;
			$get_data = "";
			$get_data = $this->callAPI('GET', $url, false);
			if(!empty($get_data)) {
				$response = json_decode($get_data, true);
				$errorApi = $response['errorApi'];
				if($errorApi == null){
					$offreConcurrentePlusBasse = 0;
					foreach ($response['comparators'] as $row) {
						//Les éléments relatifs à l’offre concurrente la plus basse
						//$row['seller'];     -- nom du vendeur
						//$row['price'];      -- prix produit sans livraison
						//$row['totalPrice']; -- prix produit plus livraison
						$offreConcurrentePlusBasse = $row['totalPrice'];
						break;
					}
					
					if($offreConcurrentePlusBasse > 0){
						
						$offreConcurrentePlusBasse =  $offreConcurrentePlusBasse - 5; //l’offre concurrente la plus basse moins 5€
						//cette condition peut être activée si on souhaite définir un niveau de prix en dessous duquel on ne veut pas descendre. C'est une sorte de prix plancher.
						//if($params['price'] < $offreConcurrentePlusBasse){
							$params['price'] = $offreConcurrentePlusBasse; // surcharger le prix par l’offre concurrente la plus basse moins 5€
						//}
					
					}
				 	
				}

			}

		} catch(Exception $e){
		}

	}

    }

}

