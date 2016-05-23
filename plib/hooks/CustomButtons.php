<?php
class Modules_HalonEnduser_CustomButtons extends pm_Hook_CustomButtons
{
	public function getButtons()
	{
		$buttons = [[
			'place' => self::PLACE_DOMAIN_PROPERTIES,
			'title' => 'Halon Anti-spam',
			'description' => "End-user web application for Halon's email gateway",
			'icon' => pm_Context::getBaseUrl().'images/64x64.png',
			'link' => pm_Context::getBaseUrl().substr(self::PLACE_DOMAIN, 1),
			'contextParams' => true,
			'newWindow' => true
		]];
		return $buttons;
	}
}
