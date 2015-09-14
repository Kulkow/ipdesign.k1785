<?	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$vote = ! empty($_REQUEST['vote']) ? intval($_REQUEST['vote']) : 0;
$object = ! empty($_REQUEST['object']) ? intval($_REQUEST['object']) : 0;
exit(json_encode(array('result' => 1, 'vote' => $vote,'object' => $object)));
?>
