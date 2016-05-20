<?php

class IndexController extends pm_Controller_Action
{
	public function init()
	{
		parent::init();

		$this->view->pageTitle = 'Halon Anti-spam';
	}
	public function indexAction()
	{
		if (pm_Session::getClient()->isAdmin()) {
			$this->_forward('settings');
		} else {
			$this->_forward('login');
		}
	}
	public function loginAction()
	{
		// Get timezone offset from client
		if (isset($_GET['timezone'])) {
			$timezone = intval($_GET['timezone']);
		} else {
			die('<script>window.location.href = "?timezone=" + new Date().getTimezoneOffset();</script>');
		}

		$enduser = pm_Settings::get('enduserURL');
		$apikey = pm_Settings::get('apiKey');
		$session = new pm_Session();
		$client = $session->getClient();
		$username = $client->GetProperty('login');

		if (!$enduser or !$apikey) {
			throw new pm_Exception('Required extension settings are not configured.');
		}

		if ($client->isClient()) {
			$domain = pm_Session::getCurrentDomain()->getName();
			$access = array('domain' => array($domain));
		} else {
			throw new pm_Exception('Only clients can use this extension.');
		}

		$get = http_build_query(
			array(
				'username' => $username,
				'api-key' => $apikey,
				'timezone' => $timezone
			)
		);
		$opts = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query(array('access' => $access))
			)
		);
		$context = stream_context_create($opts);
		$result = json_decode(@file_get_contents($enduser.'/session-transfer.php?'.$get, false, $context));
		if (!$result || !isset($result->session)) {
			throw new pm_Exception('Transfer failed.');
		}

		$this->_helper->redirector->gotoUrlAndExit($enduser.'/session-transfer.php?session='.$result->session);
	}
	public function settingsAction()
	{
		if (!pm_Session::getClient()->isAdmin()) {
			throw new pm_Exception('Permission denied.');
		}

		$form = new pm_Form_Simple();
		$form->addElement('text', 'enduserURL', array(
			'label' => 'URL',
			'value' => pm_Settings::get('enduserURL'),
			'required' => true,
			'validators' => array(
				array('NotEmpty', true),
			),
		));
		$form->addElement('text', 'apiKey', array(
			'label' => 'API key',
			'value' => pm_Settings::get('apiKey'),
			'required' => true,
			'validators' => array(
				array('NotEmpty', true),
			),
		));
		$form->addControlButtons(array(
			'cancelLink' => pm_Context::getModulesListUrl(),
		));

		if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
			pm_Settings::set('apiKey', $form->getValue('apiKey'));
			pm_Settings::set('enduserURL', $form->getValue('enduserURL'));
			$this->_status->addMessage('info', 'Data was successfully saved.');
			$this->_helper->json(array('redirect' => pm_Context::getBaseUrl()));
		}
		$this->view->form = $form;
	}
}
