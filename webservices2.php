<?php

require_once('PSWebServiceLibrary.php');

try {

    $host = 'https://preprod.hattaa.com';
    $apiKey = 'BY5IW66U8DJYVUTIASZ7X587X5KF959E';
    $apiKey2 = "44L9NA5AGJW5GJ3WGMPN4JXVB9UP5D9A";
    $webService = new PrestaShopWebservice($host, $apiKey, false);
    $webService2 = new PrestaShopWebservice($host, $apiKey2, false);
    /**
     * On stocke ici les variables communes aux commandes créés via l'api
     */

      $customerphone = '0668770504'; 
      $carrierName = 'My carrier'; 
      $customeremail = 'test@test.com'; 
                  
    /**
     * Liste des produits qu'on souhaite ajouter au panier
     */
    $products = [
       
        [
            'reference' => '8013207718',
            'qty' => 2,
            'combinaison'=>'8013207718_L'
        ],
      
        
    ];
    $searchCustomerXml = $webService->get([
        'resource' => 'customers',
        'filter' => ['email' => $customeremail],
    ]);
   
    
    $customerDatas = [
           
        'email' => $customeremail, // Replace with actual email
        'firstname' => 'rma',
        'lastname' => 'test',
        'active' => 1,
        'passwd' =>password_hash("GFG@123", PASSWORD_DEFAULT),
        'id_default_group' =>3,
        // Add other customer data as needed (refer to PrestaShop documentation)
      ];
  
    //Si il existe on récupère l'identifiant
    if (!empty($searchCustomerXml->children()->children())) {
       
        $customerId = (int)$searchCustomerXml->children()->children()[0]->attributes()['id'][0];
    } //Si il n'existe pas on le créé
    else {
        
       
      
        function generateCustomerXml($customerData) {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $prestashop = $xml->createElement('prestashop');
            $prestashop->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        
            $customer = $xml->createElement('customer');
        
            // Add customer data elements with CDATA sections
            $customerFields = [
                'id',
                'id_default_group',
                'id_lang',
                'newsletter_date_add',
                'ip_registration_newsletter',
                'last_passwd_gen',
                'secure_key',
                'deleted',
                'passwd',
                'lastname',
                'firstname',
                'email',
                'id_gender',
                'birthday',
                'newsletter',
                'optin',
                'website',
                'company',
                'siret',
                'ape',
                'outstanding_allow_amount',
                'show_public_prices',
                'id_risk',
                'max_payment_days',
                'active',
                'note',
                'is_guest',
                'id_shop',
                'id_shop_group',
                'date_add',
                'date_upd',
                'reset_password_token',
                'reset_password_validity',
            ];
        
            foreach ($customerFields as $key) {
                $element = $xml->createElement($key);
                $value = isset($customerData[$key]) ? $customerData[$key] : '';
                $cdata = $xml->createCDATASection($value);
                $element->appendChild($cdata);
                $customer->appendChild($element);
            }
        
            // Add empty "associations" element
            $associations = $xml->createElement('associations');
            $groups = $xml->createElement('groups');
            $group = $xml->createElement('group');
            $id = $xml->createElement('id');
            $cdata = $xml->createCDATASection('3');
            $id->appendChild($cdata);
            $group->appendChild($id);
            $groups->appendChild($group);
            $associations->appendChild($groups);
            $customer->appendChild($associations);
        
            $prestashop->appendChild($customer);
            $xml->appendChild($prestashop);
        
            return $xml->saveXML();
        }
          
          // Example usage
          
          
          $customerXml = generateCustomerXml($customerDatas);
          $customerArray = [
            'resource' => 'customers',
            'postXml' => $customerXml,
        ];
        $resultXml = $webService->add( $customerArray);
     //   var_dump($resultXml);die;
        $customerId = (int)$resultXml->children()[0]->id;
    }
    //var_dump($customerId);die;
    //Paramètres du client
    

    //Paramètres de l'adresse
    $addressDatas = [
        'alias' => 'Adresse API',
        'id_customer' => $customerId,
        'firstname' => 'rma',
        'lastname' => 'test',
        'address1' => '15 rue laarbi ben mhidi',
        'address2' => '',
        'postcode' => '16000', 
        'city' => 'Alger',
        'phone' => $customerphone,
        'id_country' => 38,
    ];
    


    //On regarde si le client existe
    

    //Récupération des adresses on part du principe qu'on récupère la première adresse du client si elle existe
    //On regarde si le client existe
    $searchAddressXml = $webService->get([
        'resource' => 'addresses',
        'filter' => ['id_customer' => $customerId],
    ]);
    //var_dump($searchAddressXml);die;
    //Si il existe on récupère l'identifiant de la première adresse
    if (!empty($searchAddressXml->children()->children())) {
        $addressId = (int)$searchAddressXml->children()->children()[0]->attributes()['id'][0];
        
    } //Si il n'existe pas on le créé
    else {

        //Création d'un client
        $addressXml = $webService->get(['url' => $host . '/api/addresses?schema=blank']);
       
        foreach ($addressXml->address[0] as $nodeKey => $node) {
            if (array_key_exists($nodeKey, $addressDatas)) {
                $addressXml->children()[0]->$nodeKey = $addressDatas[$nodeKey];
            }
        }

        $opt = array('resource' => 'addresses');
        $opt['postXml'] = $addressXml->asXML();
        $resultXml = $webService->add($opt);
        $addressId = (int)$resultXml->children()[0]->id;
    }

    //Création d'un panier
    $cartXml = $webService->get(['url' => $host . '/api/carts?schema=blank']);

    //Définition des paramètres par défaut du panier
    //Tout ces paramètres pourront être récupérés de l'api en une fois et stockés
    $cartXml->cart->id_customer = $customerId;
    $cartXml->cart->id_address_delivery = $addressId;
    $cartXml->cart->id_address_invoice = $addressId;
    $cartXml->cart->id_currency = 2; //@Todo récupérer via api
    $cartXml->cart->id_lang = 1; //@Todo récupérer via api
    $cartXml->cart->id_shop = 1; //@Todo récupérer via api
    $cartXml->cart->id_shop_group = 1;

    //Identifiant du transporteur à récupérer dynamiquement ( peut bouger en étant édité dans le backoffice )
    $carrierXml = $webService->get(['resource' => 'carriers', 'filter' => ['name' => $carrierName]]);
    
    if (!empty($carrierXml->children()->children()[0]->attributes()['id'][0])) {
        $id_carrier = (int)$carrierXml->children()->children()[0]->attributes()['id'][0];
    } //Si il n'existe pas on le créé
    else {
        var_dump('Mode de livraison existe pas');exit;
    }

    $cartXml->cart->id_carrier = $id_carrier;

    //Récupération de identifiants des produits
    $productIds = array();
    foreach ($products as $cartProduct) {
       
        $productSearchXml = $webService->get([
            'resource' => 'products',
            'filter' => ['reference' => $cartProduct['reference']],
        ]);
        
        if ($productSearchXml->children()->children()[0]==null){
            var_dump("ce produit n'existe pas ") ;exit;
        }
       
        if (!empty($productSearchXml->children()->children()[0]->attributes()['id'][0])) {
            $id_product = (int)$productSearchXml->children()->children()[0]->attributes()['id'][0];
            $product_active = $webService->get([
                'resource' => 'products/'.$id_product,
               
            ]);
            $id_stock_available=intval($product_active->product->associations->stock_availables->stock_available[0]->id[0]) ;
            $stock_availables = $webService2->get([
                'resource' => 'stock_availables/'.$id_stock_available,
               
            ]);
            $qte_produit = $cartProduct["qty"];
          $current_stock =intval($stock_availables->stock_available->quantity);
           // var_dump(intval($stock_availables->stock_available->quantity));die;
           
                      if ($current_stock < $cartProduct['qty']) {
                var_dump("Ce produit ".$cartProduct['reference']." a que ".$current_stock." en stock");
                exit;
            }
            
        } else {
            //Si pas de produit on ne peut pas continuer
            continue;
        }

        if (isset($cartProduct['combinaison'])) {
            $combinationSearchXml = $webService->get([
                'resource' => 'combinations',
                'filter' => ['reference' => $cartProduct['combinaison']],
            ]);
            
            if ($combinationSearchXml->children()->children()==null||empty($combinationSearchXml->children()->children())){
                var_dump("cette combinations n'existe pas ") ;exit;
            }
            if (!empty($combinationSearchXml->children()->children()[0]->attributes()['id'][0])) {
                $id_product_attribute = (int)$combinationSearchXml->children()->children()[0]->attributes()['id'][0];
               $stocks = $product_active->product->associations->stock_availables->stock_available;
               foreach($stocks as $stock){
              //  var_dump($id_product_attribute);die;
               
                if(intval($stock->id_product_attribute[0])==$id_product_attribute){
                   
                    $stock_availables = $webService2->get([
                        'resource' => 'stock_availables/'.$stock->id[0],
                       
                    ]);
                    if (intval($stock_availables->stock_available->quantity) < $cartProduct['qty']) {
                        var_dump("Cette declinaison de ce produit ".$cartProduct['combinaison']. " a que ".intval($stock_availables->stock_available->quantity)." en stock");
                        exit;
                    }
                    
                }
               }
            } else {
                $id_product_attribute = 0;
            }
        } else {
            $id_product_attribute = 0;
        }
        $productIds[] = [
            'id_product' => $id_product,
            'id_product_attribute' => $id_product_attribute,
            'qty' => $cartProduct['qty']
        ];
    }
    
    //Insertion des lignes de produits
    foreach ($productIds as $productId) {
        $child = $cartXml->cart->associations->cart_rows->addChild('cart_row');
        $child->id_product = $productId['id_product'];
        $child->id_product_attribute = $productId['id_product_attribute'];
        $child->quantity = $productId['qty'];
        $child->id_adddress_delivery = $addressId;
    }
    
    //Création du panier
    $opt = array('resource' => 'carts');
    $opt['postXml'] = $cartXml->asXML();
    $addCartXml = $webService->add($opt);
   

    $orderXml = '<?xml version="1.0" encoding="UTF-8"?>';



$orderXml .= '<prestashop>';
$orderXml .= '<order>';
$orderXml .= '<id_address_delivery>'.$addCartXml->cart->id_address_delivery.'</id_address_delivery>';
$orderXml .= '<id_address_invoice>'.$addCartXml->cart->id_address_invoice.'</id_address_invoice>';
$orderXml .= '<id_cart>'.$addCartXml->cart->id.'</id_cart>';
$orderXml .= '<id_currency>1</id_currency>';
$orderXml .= '<id_lang>1</id_lang>';
$orderXml .= '<id_customer>'.$addCartXml->cart->id_customer.'</id_customer>';
$orderXml .= '<id_carrier>'.$addCartXml->cart->id_carrier.'</id_carrier>';
$orderXml .= '<module>ps_cashondelivery</module>';
$orderXml .= '<payment>kimland</payment>';
$orderXml .= '<current_state>20</current_state>';


$orderXml .= '<associations>';
$orderXml .= '<order_rows>';
$orderTotal=0;
foreach ( $addCartXml->cart->associations->cart_rows->cart_row as $cartRow)
{
    if ( $cartRow->id_product == 0 || $cartRow->id_product =="")
            continue;
    $orderXml .= '<order_row>';
$orderXml .= '<id>null</id>';
$orderXml .= '<product_id>'.$cartRow->id_product.'</product_id>'; 

$productXml = $webService->get(['url' => $host . '/api/products/'.$cartRow->id_product]);
$orderXml .= '<product_quantity>'.$cartRow->quantity.'</product_quantity>'; // Remplacez 2 par la quantité du produit
$orderXml .= '<product_name>'.$productXml->product->name.'</product_name>'; // Remplacez "Nom du produit" par le nom du produit

$orderXml .= '<product_reference>'.$productXml->product->reference.'</product_reference>'; // Remplacez "REF123" par la référence du produit

$orderTotal += ( (float)$productXml->product->price * (int)$cartRow->quantity ) * 1.9;
$orderXml .= '<product_price>'.$orderTotal.'</product_price>'; // Remplacez 25.99 par le prix du produit
$orderXml .= '<id_customization>null</id_customization>'; // Laissez null si aucune personnalisation n'est appliquée
$orderXml .= '<unit_price_tax_incl>'.$productXml->product->price_ttc.'</unit_price_tax_incl>'; // Remplacez 25.99 par le prix unitaire TTC du produit
$orderXml .= '<unit_price_tax_excl>'.$productXml->product->price_ttc.'</unit_price_tax_excl>'; // Remplacez 21.99 par le prix unitaire HT du produit
$orderXml .= '</order_row>';
}
$orderXml .= '</order_rows>';
$orderXml .= '</associations>';
$orderXml .= '<total_paid>'.$orderTotal.'</total_paid>';

$orderXml .= '<total_paid_real>'.$orderTotal.'</total_paid_real>';
$orderXml .= '<total_products>'.$orderTotal.'</total_products>';
$orderXml .= '<total_products_wt>'.$orderTotal.'</total_products_wt>';
$orderXml .= '<conversion_rate>1</conversion_rate>';
$orderXml .= '</order>';
$orderXml .= '</prestashop>';


    
    
    $ch = curl_init($host . '/api/orders?output_format=XML&ws_key=' . $apiKey);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $orderXml);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($orderXml)
    ));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $startIndex = strpos($response, '<id><![CDATA[') + strlen('<id><![CDATA['); // Find the start of CDATA content
    $endIndex = strpos($response, ']]>', $startIndex); // Find the end of CDATA content
    $orderId = substr($response, $startIndex, $endIndex - $startIndex);
  
  $xml = $webService->get(array('url' => $host .'/api/order_histories/?schema=blank'));
  $xml->order_history->id_order = intval($orderId); // id order webservice
  $xml->order_history->id_order_state = 20;

  $opt = array( 'resource' => 'order_histories' );
  $opt['postXml'] = $xml->asXML();
  $xmloc = $webService->add( $opt );
    /************************* */
  var_dump("new order created ID : $orderId");
  /********************** */ 
    
    //var_dump($addOrderXml);die;
    
} catch (PrestaShopWebserviceException $e) {
    echo $e->getMessage();
}