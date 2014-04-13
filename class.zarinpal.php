<?php
/**
 * @company      :  Masoud Amini
 * @created by   :  Masoud Amini
 * @contact      :  Me@MasoudAmini.ir
 * @created on   :  13 April 2014
 * @file name    :  gateways/class.zarinpal.php
 * @copyright    :  Copyright (C) 2014. All rights reserved.
 * @license      :  Copyright (C) 2014. All rights reserved.
 * @author       :  Masoud Amini
 * @description  :  zarinpal Gateway Payment for the component (jblance)
 */

defined('_JEXEC') or die('Restricted access');

class zarinpal_class {
	var $fields = array();
	var $payconfig = array();
	var $details = array();
	
	function zarinpal_class($payconfig, $details){
		$this->zarinpal_url       = 'https://de.zarinpal.com/pg/services/WebGate/wsdl';
		$this->payconfig        = $payconfig;
		$this->details          = $details;
	}
	
	function zarinpalPayment(){
		$user 	 =& JFactory::getUser();
		
		$details   = $this->details;
		$amount    = intval($details['amount']) * 10; //number_format($details['amount'], 2);
		$taxrate   = $details['taxrate'];
		$orderid   = $details['orderid'];
		$itemname  = $details['itemname'];
		$item_num  = $details['item_num'];
		$invoiceNo = $details['invoiceNo'];
	
		$link_status = JURI::base().'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=zarinpal';
	
	
	
	
	
	$client = new SoapClient($this->zarinpal_url, array('encoding' => 'UTF-8')); 
	if ($err = $client->getError()) { die('System Error 1'); }
	
	$tmp = explode('-',$invoiceNo);
	$orderID = $tmp[2];
	$result = $client->PaymentRequest(
						array(
								'MerchantID' 	=> $this->payconfig->zpMerchantID,
								'Amount' 	=> $amount,
								'Description' 	=> 'پرداخت سفارش شماره : ' . $orderID,
								'Email' 	=> $Email,
								'Mobile' 	=> $Mobile,
								'CallbackURL' 	=> $link_status.'&in='.$invoiceNo
							)
	);

	//Redirect to URL You can do it also by creating a form
	if($result->Status == 100)
	{
		session_start();
		$_SESSION["am"] = $amount;
		
		Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority);
	} else {
		echo'ERR: '.$result->Status;
	}







	}
   
	function zarinpalReturn($data)
	{
		if (!isset($data['Authority'])) { die('Oops. No Access'); }
		if ($data['Status'] != 'OK') return array('success' => false);
		
		$Authority       = $data['Authority'];
		$invoiceNo       = $data['in'];
		

		$client = new SoapClient($this->zarinpal_url, array('encoding' => 'UTF-8')); 
		if ($err = $client->getError()) { die('System Error 1'); }
		$Am = $_SESSION["am"] ;

		$result = $client->PaymentVerification(
						  	array(
									'MerchantID'	 => $this->payconfig->zpMerchantID,
									'Authority' 	 => $Authority,
									'Amount'	 	 => $Am
								)
		);

		if($result->Status == 100){
			return array('success' => true, 'invoice_num' => $invoiceNo);
			echo 'Transation success. RefID:'. $result->RefID;
		} else {
			return array('success' => false);
			echo 'Transation failed. Status:'. $result->Status;
		}

	}
}
?>