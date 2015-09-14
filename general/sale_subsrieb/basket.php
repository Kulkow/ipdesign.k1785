<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
//use Bitrix\Main\Config\Option;

//Loc::loadMessages(__FILE__);

if (!Loader::includeModule('catalog'))
	return;
if (!Loader::includeModule('sale'))
	return;

class ItbasketSubsrieb{
    /**
     *
    * return array() DELAY BasketList - experation No send Order
    **/
    public static function GetAbandonedBasket($experation = NULL,$sort = array("ID" => "ASC"), $limit = NULL){
        global $DB;
        if(! $experation){
            $experation = 30*24*60*60;
        }
        $arItems = array();
        $time = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), time() - $experation);
        $arFilter = array('>=DATE_UPDATE' => $time,
                          "ORDER_ID" => false,
                          //"CAN_BUY" => "Y",
                          "DELAY"   => "Y"
                          );
        if(! empty($limit)){
            $arPages = array('nTopCount' =>  $limit);
        }
        $arSelect = array("ID", "USER_ID", "PRODUCT_ID", "CAN_BUY", "DELAY", "SUBSCRIBE", "QUANTITY","DATE_UPDATE","DETAIL_PAGE_URL");
        $dbBasketList = CSaleBasket::GetList(
            $sort,
            $arFilter,
            false,
            $arPages,
            $arSelect
        );
        while($_arItems = $dbBasketList->Fetch()){
            $arItems[] = $_arItems;
        }
        return $arItems;
    }

    /**
     *
    * return array() BasketList - experation - Send Order
    **/
    public static function OrderBasket($experation = NULL,$sort = array("ID" => "ASC"), $limit = NULL){
        global $DB;
        $arItems = array();
        if(! $experation){
            $experation = 30*24*60*60;
        }
        $time = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), time() - $experation);
        $arPages = false;
        if(! empty($limit)){
            $arPages = array('nTopCount' =>  $limit);
        }
        $arFilter = array('>=DATE_UPDATE' => $time,
                          "ORDER_ID" => true,
                          "CAN_BUY" => "Y");
        $arSelect = array("ID", "USER_ID", "PRODUCT_ID", "CAN_BUY", "DELAY", "SUBSCRIBE", "QUANTITY","DATE_UPDATE","DETAIL_PAGE_URL");
        $dbBasketList = CSaleBasket::GetList(
				$sort,
				$arFilter,
				false,
				$arPages,
				$arSelect
			);
			while($_arItems = $dbBasketList->Fetch()){
				$arItems[] = $_arItems;
			}
        return $arItems;
    }

    /**
    * return array('FUSER' => array('PRODUCT_ID1','PRODUCT_ID1',));
    * Send Order Basket - Experation
    *
    */

    public static function OrderBasketProduct($experation = NULL,$sort = array("ID" => "ASC"), $limit = 1000){
        $arProducts = self::OrderBasket($experation = NULL,$sort = array("ID" => "ASC"), $limit = 1000);

    }


    protected static function prepateList($experation = NULL,$sort = array("ID" => "ASC"), $limit = NULL){
        $arAbandonedBasket = self::GetAbandonedBasket($experation,$sort,$limit);
        $arSendBasket = self::OrderBasket($experation,$sort);
        $sendProductUser = $UserProducts = array();
        foreach($arSendBasket as $arItem){
            $USER_ID = $arItem['USER_ID'];
            if(! isset($sendProductUser[$USER_ID])){
                $sendProductUser[$USER_ID] = array();
            }
            $sendProductUser[$USER_ID][] = $arItem['PRODUCT_ID'];
        }
        unset($arItem);
        $UserProducts['PRODUCTS'] = array();
        $arProducts = array();
        foreach($arAbandonedBasket as $arItem){
            $USER_ID = $arItem['USER_ID'];
            if(! isset($UserProducts[$USER_ID])){
               $UserProducts[$USER_ID] = array();
            }
            if(! empty($sendProductUser[$USER_ID])){
                if(! in_array($arItem['PRODUCT_ID'],$sendProductUser[$USER_ID])){
                    $UserProducts[$USER_ID][] = $arItem['PRODUCT_ID'];
                    $arProducts[] = $arItem['PRODUCT_ID'];
                }
            }
        }
        $arProducts = array_unique($arProducts);
        $UserProducts['PRODUCTS'] = $arProducts;
        return $UserProducts;
    }

    public static function getList($experation = NULL,$sort = array("ID" => "ASC"), $limit = NULL){
        $UserProducts = self::prepateList($experation,$sort,$limit);
        $_arProducts = $UserProducts['PRODUCTS'];
        unset($UserProducts['PRODUCTS']);

        $arProducts = array();
        $arSelect = array("ID", "NAME", "DETAIL_PAGE_URL","ACTIVE");
        $_arFilter = array("ID" => $_arProducts, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
        $rsProduct = CIBlockElement::GetList(array(), $_arFilter, false, false, $arSelect);
        while($oProduct = $rsProduct->GetNextElement()){
            $_Product = $oProduct->GetFields();
            $arProducts[$_Product['ID']] = $_Product;
        }
        $arUserIds = array_keys($UserProducts);
        $arUsers = array();
        $rsUsers = CUser::GetList(($by = "id"), ($order = "desc"), array('ID' => $arUserIds));
        while($arUser = $rsUsers->Fetch()){
            $arUsers[$arUser['ID']] = $arUser;
        }
        $NewUserProducts = array();
        foreach($UserProducts as $user_id => $product_ids){
            $NewUserProduct = array();
            if(! empty($arUsers[$user_id])){
                $NewUserProduct['USER'] = $arUsers[$user_id];
            }
            $NewUserProduct['PRODUCTS'] = array();
            foreach($product_ids as $product_id){
                if(! empty($arProducts[$product_id])){
                    $NewUserProduct['PRODUCTS'][] = $arProducts[$product_id];
                }
            }
            $NewUserProducts[$user_id] = $NewUserProduct;
        }
        return $NewUserProducts;
    }

    public static function Send($EventMessage = NULL,$experation = NULL,$sort = array("ID" => "ASC"), $limit = NULL){
        if(! $EventMessage){
            //return FALSE;
        }
        $siteUrl = 'http://'.SITE_SERVER_NAME;
        $arError = array();
        $send = 0;
        $UserProducts = self::getList($experation,$sort,$limit);
        foreach($UserProducts as $UserProduct){
            if(! empty($UserProduct['PRODUCTS']) AND $UserProduct['USER']){
                $strListProduct = '<ul>';
                foreach($UserProduct['PRODUCTS'] as $arProduct){
                    $_strListProduct = '<li><a href="#SITE_URL#/#DETAIL_PAGE_URL#">#NAME#</a></li>';

                    $strListProduct .= str_replace(array("#SITE_URL#", "#DETAIL_PAGE_URL#","#NAME#"),
                                                   array($siteUrl, $arProduct['DETAIL_PAGE_URL'], $arProduct['NAME']),
                                                   $_strListProduct);
                }
                $strListProduct .= '</ul>';
                $arMessageFields = array("USER_ID" => $UserProduct['USER']['ID'],
                                     "USER_NAME" => $UserProduct['USER']["NAME"].' '.$UserProduct['USER']["LAST_NAME"],
                                     "USER_EMAIL" => $UserProduct['USER']["EMAIL"],
                                     "PRODUCTS_LIST" => $strListProduct);
                $arrSITE = array(SITE_ID);
                if(CEvent::Send("SENDWISHLIST", $arrSITE, $arMessageFields)){
                    $send++;
                }else{
                    $arError[] = 'NOSEND USER_ID:'.$UserProduct['USER']['ID'];
                }
                if(! empty($arError)){

                }
            }
        }

    }

}

?>
