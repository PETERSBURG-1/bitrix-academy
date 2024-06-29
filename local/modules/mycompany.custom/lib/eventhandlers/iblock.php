<?php

namespace MyCompany\Custom\EventHandlers;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CEventLog;
use Bitrix\Main\Type\DateTime;

class Iblock
{

	const NO_DEACTIVATE_NEWS_DAYS = 3;

	static function stopDeactivateNews(&$arFields)
	{
		global $APPLICATION;

		if ($arFields['IBLOCK_ID'] == IBLOCK_NEWS_ID && $arFields['ACTIVE'] == 'N')
		{
			$currentValues = ElementTable::getList([
				'filter' => [
					'ID' => $arFields['ID']
				],
				'select' => ['ACTIVE', 'DATE_CREATE']
			])->fetch();

			if ($currentValues['ACTIVE'] == 'Y')
			{
				/** @var DateTime $createdDate **/
				$createdDate = $currentValues['DATE_CREATE'];
				$currentDate = new DateTime();
				$diffDateTime = $currentDate->getDiff($createdDate);

				if ($diffDateTime->d < static::NO_DEACTIVATE_NEWS_DAYS)
				{
					$APPLICATION->ThrowException(Loc::getMessage
					('MY_COMPANY_CUSTOM_NO_DEACTIVATE_NEWS_DAYS', [
						'#NO_DEACTIVATE_NEWS_DAYS#' =>
						static::NO_DEACTIVATE_NEWS_DAYS,
						'#DAYS_LEFT#' => $diffDateTime->d,
					]));

					return false;
				}
			}
		}
	}

	static function onNewsAdd($arFields): void
	{
		if ($arFields['IBLOCK_ID'] !== IBLOCK_NEWS_ID)
		{
			return;
		}

		if (!$arFields['RESULT'])
		{
			return;
		}

		$userId = (int)$arFields['CREATED_BY'];
		$user = \Bitrix\Main\UserTable::getById($userId)->fetch();
		if (empty($user))
		{
			return;
		}

		$newsId = (int)$arFields['ID'];
		$newsName = $arFields['NAME'];
		$author = "{$user['LAST_NAME']} {$user['NAME']} [$userId]";
		CEventLog::Add([
			'SEVERITY' => 'INFO',
			'AUDIT_TYPE_ID' => 'ON_NEWS_ADD',
			'MODULE_ID' => '',
			'ITEM_ID' => $newsId,
			'DESCRIPTION' => "Добавлена новость [$newsId]: $newsName.\nАвтор: $author",
		]);
	}

}