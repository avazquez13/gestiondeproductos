<?php

use PHPMailer\PHPMailer\Exception;
/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */

class SendEmail {
	// LOGGING CONFIG - ini file
	private $sendTo;
	private $sendFrom;
	private $replyTo;
	private $nameFrom;
	private $subject;
	private $htmlURL;
	private $attachment;
	private $host;
	private $user;
	private $pass;
	
	protected $_Logger;
	
	public function __construct() {
		$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
		$this->setSendTo($ini['email_sendTo']);
		$this->setSendFrom($ini['email_sendFrom']);
		$this->setReplyTo($ini['email_replyTo']);
		$this->setNameFrom($ini['email_nameFrom']);
		$this->setHtmlURL($ini['email_template_url']);
		$this->setHost($ini['email_host']);
		$this->setUser($ini['email_username']);
		$this->setPass($ini['email_password']);
		
		$this->_Logger = new Logger();
	}
	
	public function getSendTo() {
		return $this->sendTo;
	}
	
	public function setSendTo($to) {
		$this->sendTo = $to;
	}
	
	public function getSendFrom() {
		return $this->sendFrom;
	}
	
	public function setSendFrom($from) {
		$this->sendFrom = $from;
	}
	
	public function getReplyTo() {
		return $this->replyTo;
	}
	
	public function setReplyTo($reply) {
		$this->replyTo = $reply;
	}
	
	public function getNameFrom() {
		return $this->nameFrom;
	}
	
	public function setNameFrom($name) {
		$this->nameFrom = $name;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	public function getHtmlURL() {
		return $this->htmlURL;
	}
	
	public function setHtmlURL($url) {
		$this->htmlURL = $url;
	}
	
	public function getAttachment() {
		return $this->attachment;
	}
	
	public function setAttachment($file) {
		$this->attachment = $file;
	}
	
	public function getHost() {
		return $this->host;
	}
	
	public function setHost($emailHost) {
		$this->host = $emailHost;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function setUser($username) {
		$this->user = $username;
	}
	
	public function getPass() {
		return $this->pass;
	}
	
	public function setPass($password) {
		$this->pass = $password;
	}
	
	public function sendEmailNotification() {
		$response = false;
		$mail = new PHPMailer\PHPMailer\PHPMailer();
		
		
		try {
			// Parameters
			$mail->Host = $this->getHost();  	// Specify main and backup SMTP servers
			$mail->Username = $this->getUser(); // SMTP username
			$mail->Password = $this->getPass(); // SMTP password
			
			//Server settings
			//$mail->SMTPDebug = 2;                                 // Enable verbose debug output
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			//$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
			//$mail->Port = 465;                                    // TCP port to connect to
			$mail->CharSet="UTF-8";
			$mail->setLanguage('es', 'PHPMailer/language/');
				
		
			//Recipients
			$mail->setFrom($this->getSendFrom(), $this->getNameFrom());
			$mail->addAddress($this->getSendTo());
			$mail->addReplyTo($this->getReplyTo(), $this->getNameFrom());
			//$mail->addBCC('');
		
			//Attachments
			$mail->addAttachment($this->getAttachment());
			
			//Content
			$mail->isHTML(true);
			$mail->Subject = $this->getSubject();
			$mail->Body    = file_get_contents($this->getHtmlURL(), true);
			// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
			
			$mail->Send();
			$response = true;
			$this->_Logger->writeLogFile("[DEBUG] - [sendEmail] sendEmailNotification() - Email Notification Sent OK");
		} catch (phpmailerException $mailEx) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [sendEmail] sendEmailNotification() - PHPMailer Error: " . $mailEx->errorMessage());
			}
			$response = false;
		} catch (Exception $e) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [sendEmail] sendEmailNotification() - Mail Exception Error: " . $e->getMessage());
			}
			$response = false;
		} 
			
		return $response;
	}
}

?>