<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
 $ajax_url = $componentPath.'/ajax.php';
 $objects = array(15 => array('vote' => 2),
									2 => array('vote' => 1));
?>
<? foreach($objects as $object_id => $rating): ?>
<div class="rating" data-vote="<? echo $rating['vote'] ?>" data-max="5" data-allow="1" data-object="<? echo $object_id ?>">
  <form action="<? echo $ajax_url ?>" method="POST" autocomplete="off" onsubmit="return false;">
    <div class="rating_wrapper">
      <div class="rating_vote">
        <div class="star" data-index="1"></div>
        <div class="star" data-index="2"></div>
        <div class="star" data-index="3"></div>
        <div class="star" data-index="4"></div>
        <div class="star" data-index="5"></div>
      </div>
      <div class="rating_send">
        <button class="button"><? echo GetMessage('IT_RATING_BUTTON') ?></button>
      </div>
    </div>
  </form>
</div>
<? endforeach ?>
