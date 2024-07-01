<?php

namespace MyCompany\Custom\EventHandlers;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\UserTable;

class Main
{

	protected static $oldGroups = [];

	static function showDetailButton(&$items)
	{
		$request = Application::getInstance()->getContext()->getRequest();

		if ('/bitrix/admin/iblock_element_edit.php' == $request->getRequestedPage() && Loader::includeModule('iblock'))
		{
			$elements = \CIBlockElement::getList(
				[],
				[
					'ID' => $request->get('ID')
				],
				false,
				false,
				['ID', 'DETAIL_PAGE_URL']
			);

			if ($elem = $elements->getNext())
			{
				$items[] = [
					'TEXT' => Loc::getMessage('MY_COMPANY_CUSTOM_TO_SITE_BTN'),
					'LINK' => $elem['DETAIL_PAGE_URL']
				];
			}
		}

	}

	static function fillOldGroups(&$arParams)
	{
		$groups = UserGroupTable::getList([
			'filter' => [
				'USER_ID' => $arParams['ID']
			],
			'select' => ['GROUP_ID']
		]);

		static::$oldGroups = [];

		while ($group = $groups->fetch())
		{
			static::$oldGroups[] = $group['GROUP_ID'];
		}
	}

	static function notifyAdmins(&$arParams)
	{
		$newGroups = array_column($arParams['GROUP_ID'], 'GROUP_ID');
		$isAddedToAdmin = !in_array(ADMIN_GROUP_ID, static::$oldGroups) && in_array(ADMIN_GROUP_ID, $newGroups);

		if ($isAddedToAdmin)
		{
			$rsUsers = UserTable::getList([
				'filter' => [
					'GROUP_ID' => ADMIN_GROUP_ID,
					'!ID' => $arParams['ID']
				],
				'select' => ['EMAIL', 'GROUP_ID' => 'GROUPS.GROUP_ID']
			]);

			$adminEmails = [];
			while ($user = $rsUsers->fetch())
			{
				$adminEmails[] = $user['EMAIL'];
			}

			Event::send([
				'EVENT_NAME' => 'NEW_ADMIN',
				'LID' => MY_SITE_ID,
				'C_FIELDS' => [
					'NAME' => $arParams['LAST_NAME']." ".$arParams['NAME'],
					'EMAIL' => $arParams['EMAIL'],
					'EMAIL_TO' => implode(',', $adminEmails)
				]
			]);
		}
	}

	static function redirectFromTestPage(): void
	{
		global $USER, $APPLICATION;
		$curPage = $APPLICATION->GetCurPage();
		if (str_ends_with($curPage, 'test.php') && !$USER->IsAdmin())
		{
			LocalRedirect('/');
		}
	}

	static function setIsDevServerConstant()
	{
		$isDevServ = \Bitrix\Main\Config\Option::get('main', 'update_devsrv');
		if ($isDevServ === 'Y')
		{
			if (!defined('IS_DEV_SERVER'))
			{
				define('IS_DEV_SERVER', true);
			}
		}
	}

	static function clearDebugForNonAdmins(&$content)
	{
		global $USER;
		if (!$USER->IsAdmin())
		{
			$content = preg_replace('/console\.log\(.*\)/U', '', $content);
		}
	}

}