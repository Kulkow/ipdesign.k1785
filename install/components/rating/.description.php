<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage('IT_RATING_NAME'),
	"DESCRIPTION" => GetMessage('IT_RATING_DESC'),
	"ICON" => "/images/icon.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "it",
		"NAME" => GetMessage("IT_GROUP")
	),
	"COMPLEX" => "N",
);

?>
