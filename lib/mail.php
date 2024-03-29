<?php
/**
 * Copyright (c) 2012 Frank Karlitschek <frank@owncloud.org>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * OC_Mail
 *
 * A class to handle mail sending.
 */

require_once 'class.phpmailer.php';

class OC_Mail {

	/**
	 * send an email
	 *
	 * @param string $toaddress
	 * @param string $toname
	 * @param string $subject
	 * @param string $mailtext
	 * @param string $fromaddress
	 * @param string $fromname
	 * @param bool|int $html
	 * @param string $altbody
	 * @param string $ccaddress
	 * @param string $ccname
	 * @param string $bcc
	 * @throws Exception
	 */
	public static function send($toaddress, $toname, $subject, $mailtext, $fromaddress, $fromname,
		$html=0, $altbody='', $ccaddress='', $ccname='', $bcc='',$attach='') {

		$SMTPMODE = OC_Config::getValue( 'mail_smtpmode', 'sendmail' );
		$SMTPHOST = OC_Config::getValue( 'mail_smtphost', '127.0.0.1' );
		$SMTPPORT = OC_Config::getValue( 'mail_smtpport', 25 );
		$SMTPAUTH = OC_Config::getValue( 'mail_smtpauth', false );
		$SMTPAUTHTYPE = OC_Config::getValue( 'mail_smtpauthtype', 'LOGIN' );
		$SMTPUSERNAME = OC_Config::getValue( 'mail_smtpname', '' );
		$SMTPPASSWORD = OC_Config::getValue( 'mail_smtppassword', '' );
		$SMTPDEBUG    = OC_Config::getValue( 'mail_smtpdebug', false );
		$SMTPTIMEOUT  = OC_Config::getValue( 'mail_smtptimeout', 10 );
		$SMTPSECURE   = OC_Config::getValue( 'mail_smtpsecure', '' );


		$mailo = new PHPMailer(true);
		if($SMTPMODE=='sendmail') {
			$mailo->IsSendmail();
		}elseif($SMTPMODE=='smtp') {
			$mailo->IsSMTP();
		}elseif($SMTPMODE=='qmail') {
			$mailo->IsQmail();
		}else{
			$mailo->IsMail();
		}


		$mailo->Host = $SMTPHOST;
		$mailo->Port = $SMTPPORT;
		$mailo->SMTPAuth = $SMTPAUTH;
		$mailo->SMTPDebug = $SMTPDEBUG;
		$mailo->SMTPSecure = $SMTPSECURE;
		$mailo->AuthType = $SMTPAUTHTYPE;
		$mailo->Username = $SMTPUSERNAME;
		$mailo->Password = $SMTPPASSWORD;
		$mailo->Timeout  = $SMTPTIMEOUT;

		$mailo->From = $fromaddress;
		$mailo->FromName = $fromname;;
		$mailo->Sender = $fromaddress;
		$a=explode(' ', $toaddress);
		try {
			foreach($a as $ad) {
				$mailo->AddAddress($ad, $toname);
			}

			if($ccaddress<>'') $mailo->AddCC($ccaddress, $ccname);
			if($bcc<>'') $mailo->AddBCC($bcc);

			$mailo->AddReplyTo($fromaddress, $fromname);

			$mailo->WordWrap = 50;
			if($html==1) $mailo->IsHTML(true); else $mailo->IsHTML(false);

			$mailo->Subject = $subject;
			if($altbody=='') {
				$mailo->Body    = $mailtext.OC_MAIL::getfooter();
				$mailo->AltBody = '';
			}else{
				$mailo->Body    = $mailtext;
				$mailo->AltBody = $altbody;
			}
			$mailo->CharSet = 'UTF-8';
            //NEW Now with Attachment
            if($attach<>''){
            	$mailo->AddAttachment($attach['path'], $attach['name']);
            }
	
			$mailo->Send();
			unset($mailo);
			OC_Log::write('mail',
				'Mail from '.$fromname.' ('.$fromaddress.')'.' to: '.$toname.'('.$toaddress.')'.' subject: '.$subject,
				OC_Log::DEBUG);
		} catch (Exception $exception) {
			OC_Log::write('mail', $exception->getMessage(), OC_Log::ERROR);
			throw($exception);
		}
	}

	/**
	 * return the footer for a mail
	 *
	 */
	public static function getfooter() {

		$defaults = new OC_Defaults();

		$txt="\n--\n";
		$txt.=$defaults->getName() . "\n";
		$txt.=$defaults->getSlogan() . "\n";
		return($txt);

	}

	/**
	 * @param string $emailAddress a given email address to be validated
	 * @return bool
	 */
	public static function ValidateAddress($emailAddress) {
		return PHPMailer::ValidateAddress($emailAddress);
	}
}
