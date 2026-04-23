<?php

/**
 * TMailer for sending e-mail
 *
 * @package Utils
 * @name TMailer
 * @category Mail
 * @version 1.0
 * @author Vladimir Horky <vladimir.horky@tedom.com>
 * @copyright 2017 TEDOM a.s.
 * @licence Vladimir Horky, TEDOM a.s.
 */

declare(strict_types=1);

class TMailer extends PHPMailer
{
	private string $style 		=
		'h1{font:normal 14pt Arial,Verdana,sans-serif;color:#000;padding:18px 0 3px 0;margin:0 0 30px;border-bottom:dotted 1px #000;}'.
		'p{font:normal 10pt Arial,Verdana,sans-serif;color:#000;padding:3px 0 2px 0;margin:0 0 5px;}'.
		'.b{font-weight:bold}.red{color:#f00}.l{text-align:left}'.
		'table{border-collapse:collapse;margin:0;padding:0;font:9pt Arial,sans-serif;border:1px solid #eee;clear:left;text-align:left;}'.
		'table tr th{height:32px;font-weight:bold;background:#ddd;color:#fff;vertical-align:middle;padding:3px 5px;margin:0;text-align:left}'.
		'table tr td{height:32px;border-bottom:1px solid #eee;padding:3px 5px;margin:0}'.
		'table tr td.t{vertical-align:top}'.
		'table tr td.button{height:20px;padding:3px 30px;background:#ddd;}'.
		'a.button,a.button:visited,a.button:hover,a.button:active{font:normal 10pt Arial,sans-serif;color:#fff;text-decoration:none}';

	private string $SubjectText = '';

	public function __construct(string $host, int $port = 25, bool $exceptions = true)
	{
		parent::__construct($exceptions);

		$this->CharSet = 'UTF-8';
		$this->isSMTP();

		$this->Host = $host;
		$this->Port = $port;

		$this->SMTPOptions =  [
			'ssl' => [
				'allow_self_signed' => true,
				'verify_peer'		=> false,
				'verify_peer_name'	=> false
			]
		];
	}

	public function setStyle(string $style) :TMailer
	{
		$this->style = $style;
		return $this;
	}

	public function setTo(string $email) :TMailer
	{
		$this->AddAddress($email, $email);
		return $this;
	}

	public function setSubject(string $subject) :TMailer
	{
		$this->SubjectText = $subject;
		$this->Subject = stripslashes($subject);
		return $this;
	}

	public function setHTML(string $html) :TMailer
	{
		$this->msgHTML(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
				'<html lang="cs" xmlns="http://www.w3.org/1999/xhtml">'.
					'<head>'.
						'<title>'.$this->SubjectText.'</title>'.
						'<style type="text/css">'.$this->style.'</style>'.
					'</head>'.
					'<body>'.
						$html.
					'</body>'.
				'</html>'
		);

		return $this;
	}

	public function setText(string $text) :TMailer
	{
		$this->AltBody = stripslashes($text);
		return $this;
	}

	public function ClearAll() :TMailer
	{
		$this->ClearAddresses();
		$this->ClearAttachments();
		$this->ClearAllRecipients();
		$this->ClearBCCs();
		$this->ClearCCs();
		$this->ClearReplyTos();
		$this->ClearCustomHeaders();

		return $this;
	}
}