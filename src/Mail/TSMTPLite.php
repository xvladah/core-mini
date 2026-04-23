<?php


/**
 * TSMTP for OAuthorization
 *
 * @package Utils
 * @category Mail
 * @version 1.0
 * @author Vladimir Horky <vladimir.horky@tedom.com>
 * @copyright 2017 TEDOM a.s.
 * @licence Vladimir Horky, TEDOM a.s.
 */

class TSMTPLite
{
    const server = 'smtp.email.cz';
	const localhost = 'localhost';
	const login = 'email@email.cz';
    const password = '';
    const port = 25;
    const timeout = 1;
    const newline = "\r\n";

    private string $from = 'email@email.cz';
    private string $namefrom = 'From';
	private string $to = '';
	private string $subject = 'Undefined';
	private string $message = '';
    private array $logger = [];

    public function __construct()
    {
    }

    public function setFrom(string $form, string $fromname)
    {
    	$this->form = $form;
    	$this->fromname = $fromname;
    	return $this;
    }

    public function setTo(string $to)
    {
    	$this->to = $to;
    	return $this;
    }

	public function setSubject(string $subject)
	{
		$this->subject = $subject;
		return $this;
	}

	public function setMessage(string $message)
	{
		$this->message = $message;
		return $this;
	}

	public function addMessageLine(string $line = '')
	{
		if($this->message != '')
			$this->message .= '<br />';

		$this->message .= $line;
		return $this;
	}

	public function getLogger()
	{
		return $this->logger;
	}

    function send()
	{
          $conn = fsockopen(self::server, self::port, $errno, $errstr, self::timeout);
          $log = fgets($conn, 515);
          if(empty($conn)) {
             $this->logger['connection'] = 'Failed to connect: '.$log;
             return false;
          } else
             $this->logger['connection'] = 'Connected: '.$log;

          //Request Auth Login
          fputs($conn, 'AUTH LOGIN' . self::newline);
          $this->logger['authrequest'] = fgets($conn, 515);

          //Send username
          fputs($conn, base64_encode(self::login) . self::newline);
          $this->logger['authusername'] = fgets($conn, 515);

          //Send password
          fputs($conn, base64_encode(self::password) . self::newline);
          $this->logger['authpassword'] = fgets($conn, 515);

          //Say Hello to SMTP
          fputs($conn, 'HELO '.self::localhost . self::newline);
          $this->logger['heloresponse'] = fgets($conn, 515);

          //Email From
          fputs($conn, 'MAIL FROM: '.$this->from . self::newline);
          $this->logger['mailfromresponse'] = fgets($conn, 515);

          //Email To
          fputs($conn, 'RCPT TO: '.$this->to . self::newline);
            $this->logger['mailtoresponse'] = fgets($conn, 515);

          //The Email
          fputs($conn, 'DATA' . self::newline);
          $this->logger['data1response'] = fgets($conn, 515);

          //Construct Headers
          $headers  = 'MIME-Version: 1.0' . self::newline;
          $headers .= 'Content-type: text/html; charset=windows-1250' . self::newline;
          // $headers .= "To: ".$nameto.' <'.$to.'>' . $newLine;
          $headers .= 'From: '.$this->namefrom." <".$this->from.">" . self::newline;

          fputs($conn, 'To: '.$this->to."\nFrom: ".$this->from."\nSubject: ".$this->subject."\n".$headers."\n\n".$this->message."\n.\n");
          $this->logger['data2response'] = fgets($conn, 515);

          // Say Bye to SMTP
          fputs($conn,'QUIT' . self::newline);
          $this->logger['quitresponse'] = fgets($conn, 515);

          return true;
    }

  }
