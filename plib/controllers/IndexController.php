<?php

class IndexController extends pm_Controller_Action
{
	public function init()
	{
		parent::init();

		$this->view->pageTitle = 'Halon Anti-spam';

		$this->view->tabs = array(
			array(
				'title' => 'Settings',
				'action' => 'settings',
			),
			array(
				'title' => 'Test',
				'action' => 'test',
			),
			array(
				'title' => 'Help',
				'action' => 'help',
			),
		);
	}
	public function indexAction()
	{
		if (pm_Session::getClient()->isAdmin() and !isset($_GET['site_id'])) {
			$this->_forward('settings');
		} else {
			$this->_forward('login');
		}
	}
	public function loginAction()
	{
		$enduser = pm_Settings::get('enduserURL');
		$apikey = pm_Settings::get('apiKey');

		if (!$enduser or !$apikey) {
			throw new pm_Exception('Required extension settings are not configured.');
		}

		$session = new pm_Session();
		$client = $session->getClient();

		if (isset($_GET['site_id'])) {
			$domain = new pm_Domain($_GET['site_id']);
			$domain = $domain->getName();
			if (!$client->hasAccessToDomain($_GET['site_id'])) {
				throw new pm_Exception('Permission denied.');
			}
		} else {
			throw new pm_Exception('Missing domain.');
		}

		// Get timezone offset from client
		if (isset($_GET['timezone'])) {
			$timezone = intval($_GET['timezone']);
		} else {
			die('<script>window.location.href = "?timezone=" + new Date().getTimezoneOffset() + "&site_id='.$_GET['site_id'].'"</script>');
		}

		if ($client->isClient() or $client->isAdmin() or $client->isReseller()) {
			$username = $client->GetProperty('login');
			$access = array('domain' => array($domain));
		} else {
			throw new pm_Exception('Account type is not supported.');
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
			'description' => "The URL to your sp-enduser web application",
			'value' => pm_Settings::get('enduserURL'),
			'required' => true,
			'validators' => array(
				array('NotEmpty', true),
			),
		));
		$form->addElement('text', 'apiKey', array(
			'label' => 'API key',
			'description' => 'The API key for your sp-enduser web application',
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
	public function testAction()
	{
		if (!pm_Session::getClient()->isAdmin()) {
			throw new pm_Exception('Permission denied.');
		}

		$form = new pm_Form_Simple();
		$form->setMethod('post')->setAttrib('name', 'form');
		$form->addElement('button', 'test', array(
			'label' => 'Test',
			'onclick' => "document.form.submit();",
		));

		if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
			$enduser = pm_Settings::get('enduserURL');
			$apikey = pm_Settings::get('apiKey');

			$get = http_build_query(
				array(
					'username' => 'test',
					'api-key' => $apikey
				)
			);
			$opts = array(
				'http' => array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => http_build_query(array('access' => ['domain' => ['example.com']]))
				)
			);
			$context = stream_context_create($opts);
			$result = json_decode(@file_get_contents($enduser.'/session-transfer.php?'.$get, false, $context));

			if (!$result || !isset($result->session)) {
				$this->_status->addMessage('error', 'Transfer failed.');
			} else {
				$this->_status->addMessage('info', 'Transfer was successful.');
			}
			$this->_helper->json(array('redirect' => pm_Context::getActionUrl('index', 'test')));
		}
		
		$this->view->form = $form;
	}
	public function helpAction()
	{
		if (!pm_Session::getClient()->isAdmin()) {
			throw new pm_Exception('Permission denied.');
		}
	}
}
