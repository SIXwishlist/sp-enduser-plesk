<?php
class Modules_HalonEnduser_Promo_CustomerHome extends pm_Promo_CustomerHome
{
	public function getTitle()
	{
		return 'Halon Anti-spam';
	}
	public function getText()
	{
		return "End-user web application for Halon's email gateway";
	}
	public function getButtonText()
	{
		return 'Open';
	}
	public function getButtonUrl()
	{
		return '/modules/halon-enduser/';
	}
    public function getIconUrl()
	{
		pm_Context::init('halon-enduser');
		return pm_Context::getBaseUrl().'/images/icon-48x48.png';
	}
}
?>
