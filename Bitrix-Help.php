<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); ?>
<?php 


use Bitrix\Main\Loader; 
Loader::includeModule("highloadblock");
use \Bitrix\Highloadblock as HL; 
use \Bitrix\Main\Entity; 
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;


use \Bitrix\Iblock\InheritedProperty;
use \Bitrix\Iblock\PropertyIndex;

//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (isset($_COOKIE['seodev']) || isset($_COOKIE['dev'])) {
///////////////////////////////////////////////////////
/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

echo "<pre>";

CModule::IncludeModule('iblock');

/* ===========================================
Получает свойство элемента по коду

Принимает:
> id инфоблока
> id элемента
> код свойства (без PROPERTY_)
=========================================== */
function GetElementProperty ($iblock_id, $element_id, $code)
{
	$res = CIBlockElement::GetProperty($iblock_id, $element_id, Array(), Array("CODE"=>$code));
	while ($ob = $res->GetNext())
	{
	    $VALUES[] = $ob['VALUE'];
	};

	return $VALUES;
}





/* ===========================================
Задает свойство элемента по коду

Принимает:
> id элемента
> код свойства (без PROPERTY_)
> значение свойства
=========================================== */
function SetElementProperty ($element_id, $code, $val)
{
	/*$rrr = CIBlockElement::SetPropertyValueCode($element_id, $code, $val );
	return $rrr;*/

	// Установим новое значение для данного свойства данного элемента
	CIBlockElement::SetPropertyValuesEx($element_id, false, array($code => $val));
	
	$el = new CIBlockElement; 
	$res = $el->Update($element_id, array());

	return $res;
	
}




/* ===========================================
Возвращает все элементы инфоблока

Принимает:
> id инфоблока
> масив того что нужно выбрать
> дополнительный фильтр
=========================================== */
function GetAllElement ($iblock_id, $arSelect=Array("ID", "CODE", "DETAIL_PAGE_URL", "NAME"), $arFilterAdd=Array(/* "SECTION_ID"=> */) )
{	
	$ret = array();
	$arFilter = array_merge($arFilterAdd, Array("IBLOCK_ID"=>$iblock_id));
	$res = CIBlockElement::GetList(Array("SORT"=>"ASC"), $arFilter, false, false, $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$ret[$arFields["ID"]] = $arFields;
	}

	return $ret;
}





/* ===========================================
Возвращает все разделы инфоблока

Принимает:
> id инфоблока
> масив того что нужно выбрать
> дополнительный фильтр
=========================================== */
function GetAllSection ($iblock_id, $arSelect=Array("ID", "CODE", "LIST_PAGE_URL", "NAME"), $arFilterAdd=Array() )
{	
	$ret = array();
	$arFilter = array_merge($arFilterAdd, Array("IBLOCK_ID"=>$iblock_id));
	$rsSect = CIBlockSection::GetList(Array("SORT"=>"ASC"), $arFilter, false, $arSelect, false);
	while ($arSect = $rsSect->GetNext())
	{
	    $ret[$arSect["ID"]] = $arSect;
	}

	return $ret;
}




/* ===========================================
Возвращает SEO поля раздела

Принимает:
> id инфоблока
> id раздела
=========================================== */
function GetSectionSEO ($IBLOCK_ID, $SECTION_ID)
{	
	$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($IBLOCK_ID,$SECTION_ID);
	return $ipropValues->getValues();
}

/* ===========================================
Возвращает SEO шаблоны раздела

Принимает:
> id инфоблока
> id раздела
=========================================== */
function GetSectionTemplatesSEO ($IBLOCK_ID, $SECTION_ID)
{	
	$ipropElementTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($IBLOCK_ID,$SECTION_ID);
	return $ipropElementTemplates->findTemplates();
}


/* ===========================================
Устанавливает SEO шаблоны раздела

Принимает:
> id инфоблока
> id раздела
=========================================== */
function SetSectionTemplatesSEO ($IBLOCK_ID, $SECTION_ID, $newTemplates=array())
{	

	$ipropElementTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($IBLOCK_ID,$SECTION_ID);
	$ipropElementTemplates->set($newTemplates);

	$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($IBLOCK_ID,$SECTION_ID);
	$ipropValues->clearValues();

	return true;
}






/* ===========================================
Возвращает SEO поля элемента

Принимает:
> id инфоблока
> id элемента
=========================================== */
function GetElementSEO ($IBLOCK_ID, $Element_ID)
{	
	$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID,$Element_ID);
	return $ipropValues->getValues();
}

/* ===========================================
Возвращает SEO шаблоны элемента

Принимает:
> id инфоблока
> id элемента
=========================================== */
function GetElementTemplatesSEO ($IBLOCK_ID, $Element_ID)
{	
	$ipropElementTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($IBLOCK_ID,$Element_ID);
	return $ipropElementTemplates->findTemplates();
}

/* ===========================================
Устанавливает SEO шаблоны элемента

Принимает:
> id инфоблока
> id элемента
=========================================== */
function SetElementTemplatesSEO ($IBLOCK_ID, $Element_ID, $newTemplates=array())
{	

	$ipropElementTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($IBLOCK_ID,$Element_ID);
	$ipropElementTemplates->set($newTemplates);

	$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID,$Element_ID);
	$ipropValues->clearValues();

	return true;
}





/* ===========================================
Возвращает разбитый url или послений слаг

Принимает:
> url
> только последний?
=========================================== */
function SplitUrl ($url, $only_last = flase)
{
	$data = explode('/',$url);
	if ($data[count($data)-1]=='') { unset($data[count($data)-1]); }; 
	if ($data[0]=='') { unset($data[0]); }; 

	$data[0] = trim($data[0]);
	$data[count($data)-1] = trim($data[count($data)-1]);

	if ($only_last) {
		return $data[count($data)-1];
	} else {
		return $data;
	}
}




/* ===========================================
Привязывает товар к категориям

Принимает:
> id товара
> масив категорий
> если требуется обновить фасетный индекс - id инфоблока
> Код (ID) инфоблока, к которому принадлежит элемент. Параметр обязателен в случае включенных расширенных прав, иначе - необязателен.
=========================================== */
function SetElementSection ($element_id, $sec_id_ar = array(), $faset_updata_iblockId = false, $main_id_iblockId = false)
{

	if ($main_id_iblockId===false) {

		CIBlockElement::SetElementSection($element_id, $sec_id_ar);

	} else {

		CIBlockElement::SetElementSection($element_id, $sec_id_ar, false, $main_id_iblockId);

	};

	if ($faset_updata_iblockId===false) {

	} else {

		//Начиная с версии 15.0.1 модуля Информационные блоки, добавлен фасетный (т.е. предопределенный) поиск по товарам торгового каталога. После использовании функции CIBlockElement::SetElementSection() необходимо осуществить: 
		\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($faset_updata_iblockId, $element_id);

	};
	
	
}



/* --- Работа с HL блоками --- */

function GetEntityDataClass($HlBlockId) {
    if (empty($HlBlockId) || $HlBlockId < 1)
    {
        return false;
    }
    $hlblock = HLBT::getById($HlBlockId)->fetch();   
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    return $entity_data_class;
}


/* ===========================================
Возвращает записи данные из HL блока

Принимает:
=========================================== */
function GetHL ($hlbl, $filter_custom=array(), $select_custom=array("*"))
{	

	$res=[]; 
	$entity_data_class = GetEntityDataClass($hlbl);
	$rsData = $entity_data_class::getList(array(
	   'select' => $select_custom,
	   "order" => array("ID" => "ASC"),
   	   "filter" => $filter_custom
	));
	while($el = $rsData->fetch()){
	   $res[] = $el;
	}
	return $res;
}

/* ===========================================
Обновляет поля записи из HL блока

Принимает:
>
> 
> Массив данных для установки вида array('UF_NAME' => 'Фиолетовый');
=========================================== */
function SetHL ($hlbl, $idForUpdate, $data)
{
	CModule::IncludeModule('highloadblock');
	$entity_data_class = GetEntityDataClass($hlbl);
	$result = $entity_data_class::update($idForUpdate, $data);
	return $result;
}


/////////////////////////////////////////////////////////////////



// PROPERTY_ _VALUE

// $arParams и $arResult


echo "</pre>";
/*
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
*/
///////////////////////////////////////////////////////
}

?>
