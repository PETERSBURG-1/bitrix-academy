<?php B_PROLOG_INCLUDED === true || die();

/**
 * @var array $arResult
 * @var array $arParams
 */

if ($arResult["FIELDS"]["DETAIL_PICTURE"])
{
	$img = CFile::resizeImageGet(
		$arResult["FIELDS"]["DETAIL_PICTURE"],
		[
			"width" => $arParams["RESIZE_IMG_WIDTH"],
			"height" => $arParams["RESIZE_IMG_HEIGHT"],
		],
		BX_RESIZE_IMAGE_EXACT,
		true
	);

	$arResult["FIELDS"]["DETAIL_PICTURE"]["WIDTH"] = $img["width"];
	$arResult["FIELDS"]["DETAIL_PICTURE"]["HEIGHT"] = $img["height"];
	$arResult["FIELDS"]["DETAIL_PICTURE"]["SRC"] = $img["src"];
}

$arResult["DISPLAY_DATE"] = FormatDate($arParams["ACTIVE_DATE_FORMAT"], MakeTimeStamp($arResult["ACTIVE_FROM"]));

if ($arResult["ID"]) {
	$order = ['DATE_ACTIVE_FROM' => 'ASC', 'ID' => 'ASC'];
	$filter = [
		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
		'ACTIVE' => 'Y',
		'CHECK_PERMISSIONS' => 'Y',
	];
	if ($arParams['CHECK_DATES']) {
		$filter['ACTIVE_DATE'] = 'Y';
	}

	$select = ['ID', 'IBLOCK_ID', "NAME", 'DETAIL_PAGE_URL'];
	$navParams = [
		'nElementID' => $arResult['ID'],
		'nPageSize' => 1,
	];
	$result = CIBlockElement::GetList(
		$order,
		$filter,
		false,
		$navParams,
		$select
	);

	$foundCurrent = false;

	while ($item = $result->GetNext()) {
		if ($item['ID'] == $arResult['ID']){
			$foundCurrent = true;
			continue;
		}
		$item['SHORT_NAME'] = TruncateText($item['NAME'], 50);
		if (!$foundCurrent) {
			$arResult['PREVIOUS'] = $item;
		} else {
			$arResult['NEXT'] = $item;
		}
	}
}