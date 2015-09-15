<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('catalog'))
	return;


class CatalogCondCtrlUserProps extends \CCatalogCondCtrlComplex
{
    public static function GetClassName()
    {
        return __CLASS__;
    }
    /**
     * @return string|array
     */
    public static function GetControlID()
    {
        return array('CondUser', 'CondUserDestinationStore');
    }
    public static function GetControlShow($arParams)
    {
        $arControls = static::GetControls();
        $arResult = array(
            'controlgroup' => true,
            'group' =>  false,
            'label' => Loc::getMessage("IT_USER_COUND_GROUP"),
            'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
            'children' => array()
        );
        foreach ($arControls as &$arOneControl)
        {
            $arResult['children'][] = array(
                'controlId' => $arOneControl['ID'],
                'group' => false,
                'label' => $arOneControl['LABEL'],
                'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
                'control' => array(
                    array(
                        'id' => 'prefix',
                        'type' => 'prefix',
                        'text' => $arOneControl['PREFIX']
                    ),
                    static::GetLogicAtom($arOneControl['LOGIC']),
                    static::GetValueAtom($arOneControl['JS_VALUE'])
                )
            );
        }
        if (isset($arOneControl))
            unset($arOneControl);
        return $arResult;
    }
    /**
     * @param bool|string $strControlID
     * @return bool|array
     */
    public static function GetControls($strControlID = false)
    {
        $arControlList = array(
            'CondUser' => array(
                'ID' => 'CondUser',
                'FIELD' => 'ID',
                'FIELD_TYPE' => 'int',
                'LABEL' => Loc::getMessage('IT_USER_COUND_ID_NAME'),
                'PREFIX' => Loc::getMessage('IT_USER_COUND_ID_NAME_PREFIX'),
                'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
                'JS_VALUE' => array(
                    'type' => 'input'
                ),
                'PHP_VALUE' => ''
            ),
            'CondUserDestinationStore' => array(
                'ID' => 'CondUserDestinationStore',
                'FIELD' => 'UF_COUPON',
                'FIELD_TYPE' => 'string',
                'LABEL' => 'UF_COUPON Пользователя',
                'PREFIX' => 'поле UF_USER_FIELD Пользователя',
                'LOGIC' => static::GetLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
                'JS_VALUE' => array(
                    'type' => 'input'
                ),
                'PHP_VALUE' => ''
            ),
        );
        foreach ($arControlList as &$control)
        {
            if (!isset($control['PARENT']))
                $control['PARENT'] = true;
            $control['EXIST_HANDLER'] = 'Y';
            $control['MODULE_ID'] = 'sale';
            $control['MULTIPLE'] = 'N';
            $control['GROUP'] = 'N';
        }
        unset($control);
        if ($strControlID === false)
        {
            return $arControlList;
        }
        elseif (isset($arControlList[$strControlID]))
        {
            return $arControlList[$strControlID];
        }
        else
        {
            return false;
        }
    }

    public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
    {
        $strResult = '';
        $resultValues = array();
        $arValues = false;
        if (is_string($arControl))
        {
            $arControl = static::GetControls($arControl);
        }
        $boolError = !is_array($arControl);
        if (!$boolError)
        {
            $arValues = static::Check($arOneCondition, $arOneCondition, $arControl, false);
            $boolError = ($arValues === false);
        }
        if (!$boolError)
        {
            $boolError = !isset($arControl['MULTIPLE']);
        }
        if (!$boolError)
        {
            $arLogic = static::SearchLogic($arValues['logic'], $arControl['LOGIC']);
            if (!isset($arLogic['OP'][$arControl['MULTIPLE']]) || empty($arLogic['OP'][$arControl['MULTIPLE']]))
            {
                $boolError = true;
            }
            else
            {
                $strField = "\\CatalogCondCtrlUserProps::checkUserField('{$arControl['FIELD']}', '{$arLogic['OP'][$arControl['MULTIPLE']]}', '{$arValues['value']}')";
                switch ($arControl['FIELD_TYPE'])
                {
                    case 'int':
                    case 'double':
                        if (is_array($arValues['value']))
                        {
                            if (!isset($arLogic['MULTI_SEP']))
                            {
                                $boolError = true;
                            }
                            else
                            {
                                foreach ($arValues['value'] as &$value)
                                {
                                    $resultValues[] = str_replace(
                                        array('#FIELD#', '#VALUE#'),
                                        array($strField, $value),
                                        $arLogic['OP'][$arControl['MULTIPLE']]
                                    );
                                }
                                unset($value);
                                $strResult = '('.implode($arLogic['MULTI_SEP'], $resultValues).')';
                                unset($resultValues);
                            }
                        }
                        else
                        {
                            $strResult = str_replace(
                                array('#FIELD#', '#VALUE#'),
                                array($strField, $arValues['value']),
                                $arLogic['OP'][$arControl['MULTIPLE']]
                            );
                        }
                        break;
                    case 'char':
                    case 'string':
                    case 'text':
                        if (is_array($arValues['value']))
                        {
                            $boolError = true;
                        }
                        else
                        {
//                            $strResult = str_replace(
//                                array('#FIELD#', '#VALUE#'),
//                                array($strField, '"'.EscapePHPString($arValues['value']).'"'),
//                                $arLogic['OP'][$arControl['MULTIPLE']]
//                            );
                            $strResult = $strField;
                        }
                        break;
                    case 'date':
                    case 'datetime':
                        if (is_array($arValues['value']))
                        {
                            $boolError = true;
                        }
                        else
                        {
                            $strResult = str_replace(
                                array('#FIELD#', '#VALUE#'),
                                array($strField, $arValues['value']),
                                $arLogic['OP'][$arControl['MULTIPLE']]
                            );
                        }
                        break;
                }
            }
        }
        return (!$boolError ? $strResult : false);
    }
    public static function checkUserField($strUserField, $strCond, $strValue)
    {
        global $USER;
        $arUser = $USER->GetByID($USER->GetID())->Fetch();
        $field = $arUser[$strUserField];
        $res = str_replace(array('#FIELD#', '#VALUE#'), array($field, $strValue), $strCond);
        return $res;
    }

}

if (!Loader::includeModule('sale'))
	return;

class CSaleActionCtrlBasketMain extends CGlobalCondCtrlGroup
{
	public static function GetClassName()
	{
		return __CLASS__;
	}

	public static function GetControlID()
	{
		return 'ActSaleBsktMain';
	}

	public static function GetControlShow($arParams)
	{
		$arAtoms = static::GetAtomsEx(false, false);
		$boolCurrency = false;
		if (static::$boolInit)
		{
			if (isset(static::$arInitParams['CURRENCY']))
			{
				$arAtoms['Unit']['values']['CurEach'] = str_replace('#CUR#', static::$arInitParams['CURRENCY'], $arAtoms['Unit']['values']['CurEach']);
				$arAtoms['Unit']['values']['CurAll'] = str_replace('#CUR#', static::$arInitParams['CURRENCY'], $arAtoms['Unit']['values']['CurAll']);
				$boolCurrency = true;
			}
			elseif (isset(static::$arInitParams['SITE_ID']))
			{
				$strCurrency = CSaleLang::GetLangCurrency(static::$arInitParams['SITE_ID']);
				if (!empty($strCurrency))
				{
					$arAtoms['Unit']['values']['CurEach'] = str_replace('#CUR#', $strCurrency, $arAtoms['Unit']['values']['CurEach']);
					$arAtoms['Unit']['values']['CurAll'] = str_replace('#CUR#', $strCurrency, $arAtoms['Unit']['values']['CurAll']);
					$boolCurrency = true;
				}
			}
		}
		if (!$boolCurrency)
		{
			unset($arAtoms['Unit']['values']['CurEach']);
			unset($arAtoms['Unit']['values']['CurAll']);
		}
		return array(
			'controlId' => static::GetControlID(),
			'group' => true,
			'label' => Loc::getMessage('BT_SALE_ACT_MAIN_BASKET_LABEL'),
			'defaultText' => Loc::getMessage('BT_SALE_ACT_MAIN_BASKET_DEF_TEXT'),
			'showIn' => static::GetShowIn($arParams['SHOW_IN_GROUPS']),
			'visual' => static::GetVisual(),
			'control' => array(
				Loc::getMessage('BT_SALE_ACT_MAIN_BASKET_PREFIX'),
				$arAtoms['Type'],
				$arAtoms['Value'],
				$arAtoms['Unit'],
				$arAtoms['NPosition'],
				Loc::getMessage('BT_SALE_ACT_MAIN_BASKET_SUFFIX'),
				Loc::getMessage('BT_SALE_ACT_MAIN_BASKET_DESCR'),
				$arAtoms['All'],
			),
			'mess' => array(
				'ADD_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_ADD_CONTROL'),
				'SELECT_CONTROL' => Loc::getMessage('BT_SALE_SUBACT_SELECT_CONTROL')
			)
		);
	}

	public static function GetShowIn($arControls)
	{
		return array(CSaleActionCtrlGroup::GetControlID());
	}

	public static function GetAtoms()
	{
		return static::GetAtomsEx(false, false);
	}

	public static function GetAtomsEx($strControlID = false, $boolEx = false)
	{
		$boolEx = (true === $boolEx ? true : false);
		$arAtomList = array(
			'Type' => array(
				'JS' => array(
					'id' => 'Type',
					'name' => 'extra',
					'type' => 'select',
					'values' => array(
						'Discount' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TYPE_DISCOUNT'),
						'Extra' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TYPE_EXTRA')
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_TYPE_DEF'),
					'defaultValue' => 'Discount',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'Type',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'Value' => array(
				'JS' => array(
					'id' => 'Value',
					'name' => 'extra_size',
					'type' => 'input'
				),
				'ATOM' => array(
					'ID' => 'Value',
					'FIELD_TYPE' => 'double',
					'MULTIPLE' => 'N',
					'VALIDATE' => ''
				)
			),
			'Unit' => array(
				'JS' => array(
					'id' => 'Unit',
					'name' => 'extra_unit',
					'type' => 'select',
					'values' => array(
						'Perc' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_PERCENT'),
						'CurEach' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_CUR_EACH'),
						//'CurAll' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_CUR_ALL')
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_UNIT_DEF'),
					'defaultValue' => 'Perc',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'Unit',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'All' => array(
				'JS' => array(
					'id' => 'All',
					'name' => 'aggregator',
					'type' => 'select',
					'values' => array(
						'AND' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_ALL'),
						'OR' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_ANY')
					),
					'defaultText' => Loc::getMessage('BT_SALE_ACT_GROUP_BASKET_SELECT_DEF'),
					'defaultValue' => 'AND',
					'first_option' => '...'
				),
				'ATOM' => array(
					'ID' => 'All',
					'FIELD_TYPE' => 'string',
					'FIELD_LENGTH' => 255,
					'MULTIPLE' => 'N',
					'VALIDATE' => 'list'
				)
			),
			'NPosition' => array(
				'JS' => array(
					'id' => 'Position',
					'name' => 'basket_position',
					'type' => 'input'
				),
				'ATOM' => array(
					'ID' => 'Position',
					'FIELD_TYPE' => 'double',
					'MULTIPLE' => 'N',
					'VALIDATE' => ''
				)
			),
		);

		if (!$boolEx)
		{
			foreach ($arAtomList as &$arOneAtom)
			{
				$arOneAtom = $arOneAtom['JS'];
			}
				if (isset($arOneAtom))
					unset($arOneAtom);
		}

		return $arAtomList;
	}

	public static function Generate($arOneCondition, $arParams, $arControl, $arSubs = false)
	{
		$mxResult = '';
		$boolError = false;

		if (!isset($arSubs) || !is_array($arSubs))
		{
			$boolError = true;
		}

		if (!$boolError)
		{
			$arOneCondition['Value'] = doubleval($arOneCondition['Value']);
			$arOneCondition['Position'] = doubleval($arOneCondition['Position']);
			$dblVal = ('Extra' == $arOneCondition['Type'] ? $arOneCondition['Value'] : -$arOneCondition['Value']);
			$strUnit = CSaleDiscountActionApply::VALUE_TYPE_PERCENT;
			if ('CurEach' == $arOneCondition['Unit'])
			{
				$strUnit = CSaleDiscountActionApply::VALUE_TYPE_FIX;
			}
			elseif ('CurAll' == $arOneCondition['Unit'])
			{
				$strUnit = CSaleDiscountActionApply::VALUE_TYPE_SUMM;
			}

			if (!empty($arSubs))
			{
				$strFuncName = '$saleact'.$arParams['FUNC_ID'];
				$strLogic = ('AND' == $arOneCondition['All'] ? '&&' : '||');

				$mxResult = $strFuncName.'=function($row){';
				$mxResult .= 'return ('.implode(') '.$strLogic.' (', $arSubs).');';
				$mxResult .= '};';
				$mxResult .= 'ItSaleDiscountActionApply::ApplyBasketDiscount('.$arParams['ORDER'].', '.$strFuncName.', '.$dblVal.', "'.$strUnit.'","'.$arOneCondition['Position'].'");';
			}
			else
			{
				$mxResult = 'ItSaleDiscountActionApply::ApplyBasketDiscount('.$arParams['ORDER'].', "", '.$dblVal.', "'.$strUnit.'","'.$arOneCondition['Position'].'");';
			}
		}
		return (!$boolError ? $mxResult : false);
	}

	public static function Parse($arOneCondition)
	{
		if (!isset($arOneCondition['controlId']))
			return false;
		if ($arOneCondition['controlId'] != static::GetControlID())
			return false;
		$arControl = array(
			'ID' => $arOneCondition['controlId'],
			'ATOMS' => static::GetAtomsEx(false, true)
		);

		return static::CheckAtoms($arOneCondition, $arOneCondition, $arControl, false);
	}

	public static function GetConditionShow($arParams)
	{
		if (!isset($arParams['ID']))
			return false;
		if ($arParams['ID'] != static::GetControlID())
			return false;
		$arControl = array(
			'ID' => $arParams['ID'],
			'ATOMS' => static::GetAtomsEx(false, true)
		);

		return static::CheckAtoms($arParams['DATA'], $arParams, $arControl, true);
	}

}
?>
