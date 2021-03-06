<?php

namespace Etu\Module\BuckUTTBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\BuckUTTBundle\Layer\BuckUTTLayer;
use Etu\Module\BuckUTTBundle\Soap\SoapManager;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
	/**
	 * @Route("/buckutt/history", name="buckutt_history")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if (! $this->get('session')->get(SoapManager::cookie_name)) {
			return $this->redirect($this->generateUrl('buckutt_connect'));
		}

		/** @var BuckUTTLayer $buckutt */
		$buckutt = $this->get('etu.buckutt.layer');
		if (! $buckutt->getSessionStatus()) {
			return $this->redirect($this->generateUrl('buckutt_connect', array('action' => 'disconnect')));
		}

		return array(
			'credit' => $buckutt->getUserCredit(),
			'history' => $buckutt->getLastYearHistory()
		);
	}

	/**
	 * @Route("/buckutt/connect/{action}", name="buckutt_connect", defaults={"action" = "connect"})
	 * @Template()
	 */
	public function connectAction($action)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if ($action == 'disconnect'){
			$this->get('session')->remove(SoapManager::cookie_name);
			return $this->redirect($this->generateUrl('buckutt_history'));
		} elseif ($action == 'lostPin'){
			$SADMIN = new SoapManager('SADMIN', $this->get('session'));
			$state = $SADMIN->resetKey($this->getUser()->getStudentId(), 2, $this->getUser()->getMail());

			if ($state == 0){
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'success',
					'message' => 'buckutt.main.connect.newPinSent'
				));
			}
			else {
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => 'buckutt.main.connect.cantSendMail'
				));
			}
		}

		$form = $this->createFormBuilder()
			->add('pin', 'password', array('required' => true, 'max_length' => 4))
			->getForm();

		if ($form->submit($this->getRequest())->isValid()) {
			$SBUY = new SoapManager('SBUY', $this->get('session'));
			$SADMIN = new SoapManager('SADMIN', $this->get('session'));

			$idetu = $this->getUser()->getStudentId();
			$data = $form->getData();
			$pin = (int)$data['pin'];

			if ($SADMIN->_login($idetu, $pin) == 1 && $SBUY->_login($idetu, $pin) == 1) {
				return $this->redirect($this->generateUrl('buckutt_history'));
			} else {
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => 'buckutt.main.connect.invalidPin'
				));
			}
		}

		return array(
			'form' => $form->createView()
		);

	}

	/**
	 * @Route("/buckutt/reload/{step}", name="buckutt_reload", defaults={"step" = 0})
	 * @Template()
	 */
	public function reloadAction($step)
	{
		/*
		 * Step 0 -> form where we choose how many we charge
		 * Step 1 -> check amount is ok and conform from user
		 * Step 2 -> make transaction thrue the server
		 * */
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if (!$this->get('session')->get(SoapManager::cookie_name)) {
			return $this->redirect($this->generateUrl('buckutt_connect', array('action' => 'disconnect')));
		}

		define('MAX_AMOUNT', 10000);

		$clientSBUY = new SoapManager('SBUY', $this->get('session'));

		if (! $clientSBUY->getSessionStatus()) {
			return $this->redirect($this->generateUrl('buckutt_connect'));
		}

		$credit = $clientSBUY->getCredit();
		$possible_amount = MAX_AMOUNT - $credit;

		$name = $this->getUser()->getFullName();
		$form = $this->createFormBuilder()
			->add('amount', 'money', array('required' => true, 'max_length' => 5))
			->getForm();

		if ($step == 1) {
			if ($form->submit($this->getRequest())->isValid()) {
				if ($this->getRequest()->server->has('HTTP_X_FORWARDED_FOR')) {
					$ip = $this->getRequest()->server->get('HTTP_X_FORWARDED_FOR');
				} else {
					$ip = $this->getRequest()->getClientIp();
				}

				$data = $form->getData();
				$amount = $data['amount']*100;

				$param = '';
				$param .=' normal_return_url=https://'.$this->container->getParameter('etu.domain').'/buckutt/reload/2';
				$param .=' cancel_return_url=https://'.$this->container->getParameter('etu.domain').'/buckutt/reload';
				$param .=' automatic_response_url=https://'.$this->container->getParameter('etu.domain').'/buckutt/sherlocks/return';
				$param .=' language=fr';
				$param .=' payment_means=CB,2,VISA,2,MASTERCARD,2';
				$param .=' header_flag=yes';
				$param .=' target=_self';
				$param .=' customer_ip_address='.$ip;

				$table = $clientSBUY->transactionEncode($amount, $param);

				return array(
					'name' => $name,
					'step' => $step,
					'form' => $form->createView(),
					'amount' => number_format($amount/100, 2),
					'amount_total' => number_format(($credit+$amount)/100, 2),
					'credit' => number_format($credit/100, 2),
					'htmlForm' => base64_decode($table[0][1])
				);
			} else {
				return $this->redirect($this->generateUrl('buckutt_reload'));
			}
		} elseif ($step == 2) { // step 2, final step
			$credit = $clientSBUY->getCredit();
			$possible_amount = MAX_AMOUNT - $credit;
		}

		return array(
			'name' => $name,
			'step' => $step,
			'form' => $form->createView(),
			'credit' => number_format($credit/100, 2),
			'possible_amount' => number_format($possible_amount/100, 2),
			'max_amount' => number_format(MAX_AMOUNT/100, 2)
		);
	}

	/**
	 * @Route("/buckutt/admin", name="buckutt_admin")
	 * @Template()
	 */
	public function adminAction()
	{
		$formPin = $this->createFormBuilder()
			->add('oldpin', 'password', array('required' => true, 'max_length' => 4))
			->add('newpin', 'password', array('required' => true, 'max_length' => 4))
			->add('newpin2', 'password', array('required' => true, 'max_length' => 4))
			->getForm();

		if ($formPin->submit($this->getRequest())->isValid()) {
			$data = $formPin->getData();
			$oldPin = (int)$data['oldpin'];
			$newPin = (int)$data['newpin'];
			$newPin2 = (int)$data['newpin2'];

			$SADMIN = new SoapManager('SADMIN', $this->get('session'));
			if (! $SADMIN->getSessionStatus()) {
				return $this->redirect($this->generateUrl('buckutt_connect', array('action' => 'disconnect')));
			}

			$state = $SADMIN->changeKeySecure($oldPin, $newPin, $newPin2);

			if ($state == 1){
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'success',
					'message' => 'buckutt.main.admin.changePin.results.confirm'
				));
			} else {
				$msg = '';

				switch($state){
					case -1:
						$msg = 'buckutt.main.admin.changePin.results.fillAllFields';
						break;
					case -2:
						$msg = 'buckutt.main.admin.changePin.results.newDifferents';
						break;
					case -3:
						$msg = 'buckutt.main.admin.changePin.results.invalids';
						break;
					case -4:
						$msg = 'buckutt.main.admin.changePin.results.newInvalid';
						break;
					case -5:
						$msg = 'buckutt.main.admin.changePin.results.oldInvalid';
						break;
					default:
						$state = 430;
				}

				if ($state == 430) {
					$msg = 'buckutt.main.unknownError';
				}

				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => $msg
				));
			}
		}

		$formBlock = $this->createFormBuilder()
			->add('pin', 'password', array('required' => true, 'max_length' => 4))
			->getForm();

		if ($formBlock->submit($this->getRequest())->isValid()) {
			$data = $formBlock->getData();
			$pin = (int)$data['pin'];
			$SADMIN = new SoapManager('SADMIN', $this->get('session'));
			if (! $SADMIN->getSessionStatus()) {
				return $this->redirect($this->generateUrl('buckutt_connect', array('action' => 'disconnect')));
			}

			$state = $SADMIN->blockMe($pin);

			if ($state == 1){
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'success',
					'message' => 'buckutt.main.admin.block.confirm'
				));
			} else {
				$errInfo = $SADMIN->getErrorDetail($state);

				if ($errInfo == 430) {
					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'error',
						'message' => 'buckutt.main.unknownError'
					));
				} else {
					$errInfo = $errInfo[0];

					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'error',
						'message' => 'Error '.$errInfo[0].' - '.$errInfo[1]." - ".$errInfo[2]
					));
				}
			}
		}

		return array(
			'formPin' => $formPin->createView(),
			'formBlock' => $formBlock->createView()
		);
	}

	/**
	 * @Route("/buckutt/sherlocks/return", name="buckutt_sherlocks")
	 * @Template()
	 */
	public function sherlocksAction()
	{
		/*
		 * Cette page est appelé par le serveur de sherlocks pour confirmer un rechargement
		 */
		$clientSBUY = new SoapManager('SBUY', $this->get('session'));
		$clientSBUY->transactionDecode(base64_encode($_POST['DATA']));

		return new Response('');
	}
}
