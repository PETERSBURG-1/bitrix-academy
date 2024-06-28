<?php

namespace MyCompany\Custom\EventHandlers;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Main
{

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
				var_dump($items['TEXT']);
			}
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

}