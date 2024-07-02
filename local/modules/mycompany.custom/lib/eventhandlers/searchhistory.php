<?php

namespace MyCompany\Custom\EventHandlers;

use Bitrix\Main\Entity\Event;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Type\DateTime;

class SearchHistory
{
	public static function saveQuerySearchInfo(Event $event): EventResult
	{
		global $USER;

		$fields = $event->getParameters('fields');

		$changedFields = [];
		$userId = (int)$USER->getId();
		if (!isset($fields['UF_USER_ID']) && $userId > 0)
		{
			$changedFields['UF_USER_ID'] = (int)$USER->getId();
		}
		if (!isset($fields['UF_DATETIME']))
		{
			$changedFields['UF_DATETIME'] = new DateTime();
		}

		$result = new EventResult();
		if (!empty($changedFields));
		{
			$result->modifyFields($changedFields);
		}

		return $result;

	}

}