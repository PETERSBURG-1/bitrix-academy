<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
function is404Page(): bool
{
	global $APPLICATION;
	$curPage = $APPLICATION->GetCurPage();

	return $curPage === '/404.php';
}
