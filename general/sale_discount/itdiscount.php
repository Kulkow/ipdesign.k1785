<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('sale'))
	return;
if(class_exists('ItSaleDiscountActionApply')){
  return;
}
class ItSaleDiscountActionApply extends CSaleDiscountActionApply{

	private static $resetFields = array('DISCOUNT_PRICE', 'PRICE', 'VAT_VALUE', 'PRICE_DEFAULT');
	public static function ApplyBasketDiscount2(&$order, $func = NULL, $value, $unit, $basketposition = 0)
  {

	}
	public static function ApplyBasketDiscount(&$order, $func = NULL, $value, $unit, $basketposition = 0)
  {

			if (CSaleDiscountActionApply::$getPercentFromBasePrice === null)
            CSaleDiscountActionApply::$getPercentFromBasePrice = (string)Option::get('sale', 'get_discount_percent_from_base_price') == 'Y';

        if (empty($order['BASKET_ITEMS']) || !is_array($order['BASKET_ITEMS']))
            return;


        $manualMode = CSaleDiscountActionApply::isManualMode($order);

        if ($manualMode)
            $discountBasket = array_filter($order['BASKET_ITEMS'], 'CSaleDiscountActionApply::filterApplied');
        else
            $discountBasket = (is_callable($func) ? array_filter($order['BASKET_ITEMS'], $func) : $order['BASKET_ITEMS']);

        if (empty($discountBasket))
            return;


		$clearBasket = array_filter($discountBasket, 'CSaleDiscountActionApply::ClearBasket');
        if (empty($clearBasket))
            return;
        unset($discountBasket);

        $unit = (string)$unit;
        $value = (float)$value;

		if ($value != 0)
        {
            /**
            * развертка по штучно
            * и сортируем по цене, что бы продать самый дешевый товар
            *
            **/
			$_explandBasket = $_sortBasket = array();
			foreach ($clearBasket as $key => $arOneRow)
            {
				$_price = (float)$arOneRow['PRICE'];
				$_sortBasket[$_price] = $arOneRow;
			}
			ksort($_sortBasket);
			//unset($clearBasket);

			$index = 1;
			foreach ($_sortBasket as $key => $arOneRow)
            {
				$QUANTITY = $arOneRow['QUANTITY'];
				while($QUANTITY > 0){
					if($index % $basketposition == 0){
						/**
						 * Скидка на ед. товара
						*/
						$dblCurValue = $value;
						if ($unit == CSaleDiscountActionApply::VALUE_TYPE_PERCENT)
						{
							if (self::$getPercentFromBasePrice)
							{
								if (!isset($arOneRow['DISCOUNT_PRICE']))
									$arOneRow['DISCOUNT_PRICE'] = 0;
								$dblCurValue = (isset($arOneRow['BASE_PRICE'])
									? $arOneRow['BASE_PRICE']
									: $arOneRow['PRICE'] + $arOneRow['DISCOUNT_PRICE']
								)*$value/100;
							}
							else
							{
								$dblCurValue = ($arOneRow['PRICE']*$value)/100;
							}
						}
						$dblResult = $arOneRow['PRICE'] + $dblCurValue;
						if ($dblResult >= 0 && (!$manualMode || $arOneRow[CSaleDiscountActionApply::BASKET_APPLIED_FIELD] == 'Y'))
						{
							$arOneRow['PRICE'] = $dblResult;
							if (isset($arOneRow['PRICE_DEFAULT']))
								$arOneRow['PRICE_DEFAULT'] = $dblResult;
							if (isset($arOneRow['DISCOUNT_PRICE']))
							{
								$arOneRow['DISCOUNT_PRICE'] = (float)$arOneRow['DISCOUNT_PRICE'];
								$arOneRow['DISCOUNT_PRICE'] -= $dblCurValue;
							}
							else
							{
								$arOneRow['DISCOUNT_PRICE'] = -$dblCurValue;
							}
							if ($arOneRow['DISCOUNT_PRICE'] < 0){
								//$arOneRow['DISCOUNT_PRICE'] = 0;
								$arOneRow['DISCOUNT_PRICE'] = $arOneRow['PRICE'];
							}
							if (isset($arOneRow['VAT_RATE']))
							{
								$dblVatRate = (float)$arOneRow['VAT_RATE'];
								if ($dblVatRate > 0)
									$arOneRow['VAT_VALUE'] = (($arOneRow['PRICE'] / ($dblVatRate + 1)) * $dblVatRate);
							}

							foreach (self::$resetFields as &$fieldName)
							{
								if (isset($arOneRow[$fieldName]) && !is_array($arOneRow[$fieldName]))
									$arOneRow['~'.$fieldName] = $arOneRow[$fieldName];
							}
							unset($fieldName);
							//$order['BASKET_ITEMS'][$key] = $arOneRow;
						}

					}
					$QUANTITY--;
					if(! isset($_explandBasket[$arOneRow['PRODUCT_ID']])){
						$_explandBasket[$arOneRow['PRODUCT_ID']] = array();
					}
					$_explandBasket[$arOneRow['PRODUCT_ID']][] = $arOneRow;
					$index++;
				}
			}
			//unset($clearBasket);
		}

		foreach ($clearBasket as $key => $arOneRow)
        {
			if(! empty($_explandBasket[$arOneRow['PRODUCT_ID']])){
				$Total = 0.0;
				$Total_discount = 0.0;
				$_QUANTITY = count($_explandBasket[$arOneRow['PRODUCT_ID']]);
				foreach($_explandBasket[$arOneRow['PRODUCT_ID']] as $ItemProduct){
					$Total += $ItemProduct['PRICE'];
					$Total_discount -= $ItemProduct['DISCOUNT_PRICE'];
				}
				$dblResult = ($Total/$_QUANTITY);
				$dblCurValue = $Total_discount/$_QUANTITY;
				$arOneRow['PRICE'] = $dblResult;
				if (isset($arOneRow['PRICE_DEFAULT']))
					$arOneRow['PRICE_DEFAULT'] = $dblResult;
				if (isset($arOneRow['DISCOUNT_PRICE']))
				{
					$arOneRow['DISCOUNT_PRICE'] = (float)$arOneRow['DISCOUNT_PRICE'];
					$arOneRow['DISCOUNT_PRICE'] -= $dblCurValue;
				}
				else
				{
					$arOneRow['DISCOUNT_PRICE'] = -$dblCurValue;
				}
				if (isset($arOneRow['VAT_RATE']))
				{
					$dblVatRate = (float)$arOneRow['VAT_RATE'];
					if ($dblVatRate > 0)
						$arOneRow['VAT_VALUE'] = (($arOneRow['PRICE'] / ($dblVatRate + 1)) * $dblVatRate);
				}
			}
			foreach (self::$resetFields as &$fieldName)
			{
				if (isset($arOneRow[$fieldName]) && !is_array($arOneRow[$fieldName]))
					$arOneRow['~'.$fieldName] = $arOneRow[$fieldName];
			}
			unset($fieldName);
			$order['BASKET_ITEMS'][$key] = $arOneRow;
		}
	}
}
?>
