<?php
/**
 * Supernova Framework
 */
/**
 * Mail handler
 * 
 * @package MVC_Controller_Mail
 * 
 */
/* 
@example

	include "libmail.php";
	
	$m= new Mail; // create the mail
	$m->From( "leo@isp.com" );
	$m->To( "destination@somewhere.fr" );
	$m->Subject( "the subject of the mail" );	

	$message= "Hello world!\nthis is a test of the Mail class\nplease ignore\nThanks.";
	$m->Body( $message);	// set the body
	$m->Cc( "someone@somewhere.fr");
	$m->Bcc( "someoneelse@somewhere.fr");
	$m->Priority(4) ;	// set the priority to Low 
	$m->Attach( "/home/leo/toto.gif", "image/gif" ) ;	// attach a file of type image/gif
	$m->Send();	// send the mail
	echo "the mail below has been sent:<br><pre>", $m->Get(), "</pre>";
*/
class Mail {
	
	/**
	 * List of To addresses
	 * @var Array
	 */
	var $sendto = array();
	
	/**
	 * Array with CC
	 * @var Array
	 */
	var $acc = array();
	
	/**
	 * Array with BCC
	 * @var Array
	 */
	var $abcc = array();
	
	/**
	 * Array with Attachments path files
	 * @var Array
	 */
	var $aattach = array();
	
	/**
	 * List of message headers
	 * @var Array
	 */
	var $xheaders = array();
	
	/**
	 * Message priorities referential
	 * @var Array
	 */
	var $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
	
	/**
	 * Character set of message
	 * @var String
	 */
	var $charset = "utf-8";

	/**
	 * Character encoding
	 * @var String
	 */
	var $ctencoding = "7bit";
	
	/**
	 * Receipt
	 * @var Boolean
	 */
	var $receipt = 0;
	

	/**
	 * Mail constructor
	 */
	function Mail(){
		$this->autoCheck( true );
		$this->boundary= "--" . md5( uniqid("myboundary") );
	}

	/**
	 * Activate or desactivate the email address validator
	 * @param boolean $bool Set to true to turn on the auto validation
	 */
	function autoCheck( $bool ){
		if( $bool )
			$this->checkAddress = true;
		else
			$this->checkAddress = false;
	}
	
	/**
	 * Define the subject line of the email
	 * @param string $subject Any monoline string
	 */
	function Subject( $subject )
	{
		$this->xheaders['Subject'] = strtr( $subject, "\r\n" , "  " );
	}
	
	/**
	 * Set the sender of the mail
	 * @param string $from Should be an email address
	 */
	function From( $from )
	{
		if( ! is_string($from) ) {
			echo "Class Mail: error, From is not a string";
			exit;
		}
		$this->xheaders['From'] = $from;
	}
	
	/**
	 * Set the Reply-To header
	 * @param string $address Should be an email address
	 */
	function ReplyTo( $address )
	{
		if( ! is_string($address) ) 
			return false;
		
		$this->xheaders["Reply-To"] = $address;		
	}
	
	/**
	 * Add Receipt
	 * 
	 * Add a receipt to the email ie. a confirmation is returned to the "From" address
	 * (or "ReplyTo" if defined) when the receiver open the message
	 * 
	 * @warning this functionality is *not* a standard, thus only some mail clients are compliants
	 * 
	 * @return type
	 */
	function Receipt()
	{
		$this->receipt = 1;
	}
	
	/**
	 * Set the mail recipient
	 * @param string $to Email address, accept both a single address or an array of addresses
	 */
	function To( $to )
	{
	
		if( is_array( $to ) )
			$this->sendto= $to;
		else 
			$this->sendto[] = $to;
	
		if( $this->checkAddress == true )
			$this->CheckAdresses( $this->sendto );
	
	}
	
	/**
	 * Set the CC headers
	 * 
	 * CC or Carbon copy
	 * 
	 * @param mixed $cc Accept both array and string of email addresses
	 */
	function Cc( $cc )
	{
		if( is_array($cc) )
			$this->acc= $cc;
		else 
			$this->acc[]= $cc;
			
		if( $this->checkAddress == true )
			$this->CheckAdresses( $this->acc );
		
	}
	
	
	/**
	 * Set the BCC headers
	 * 
	 * BCC or Blank carbon copy
	 * 
	 * @param mixed $bcc email addresses, accept both array and string
	 */
	function Bcc( $bcc )
	{
		if( is_array($bcc) ) {
			$this->abcc = $bcc;
		} else {
			$this->abcc[]= $bcc;
		}
	
		if( $this->checkAddress == true )
			$this->CheckAdresses( $this->abcc );
	}
	
	
	/**
	 * Set the Body message of the mail
	 * 
	 * Set the text message asnd define the charset if the message contains extended characters (accents)
	 * By default it uses us-ascii
	 * 
	 * @param string $body 
	 * @param string $charset 
	 */
	function Body( $body, $charset="" )
	{
		$this->body = $body;
		
		if( $charset != "" ) {
			$this->charset = strtolower($charset);
			if( $this->charset != "us-ascii" )
				$this->ctencoding = "8bit";
		}
	}
	
	
	/**
	 * Organization header
	 * @param string $org 
	 */
	function Organization( $org )
	{
		if( trim( $org != "" )  )
			$this->xheaders['Organization'] = $org;
	}
	
	
	/**
	 * Set the mail Priority
	 * 
	 * @param int $priority Integer taket between 1 (highest) and 5 (lowest)
	 */
	function Priority( $priority )
	{
		if( ! intval( $priority ) )
			return false;
			
		if( ! isset( $this->priorities[$priority-1]) )
			return false;
	
		$this->xheaders["X-Priority"] = $this->priorities[$priority-1];
		
		return true;
		
	}
	

	/**
	 * Attach a file to the mail
	 * 
	 * @param string $filename Path of the file to attach
	 * @param string $filetype MIME-type of the file. Default to 'application/x-unknown-content-type'
	 * @param string $disposition Instruct the Mailclient to display the file if possible ("inline") or always as a link ("attachment") possible values are "inline", "attachment"
	 */
	function Attach( $filename, $filetype = "", $disposition = "inline" )
	{
		if( $filetype == "" )
			$filetype = "application/x-unknown-content-type";
			
		$this->aattach[] = $filename;
		$this->actype[] = $filetype;
		$this->adispo[] = $disposition;
	}
	
	/**
	 * Build the email message
	 * @ignore
	 */
	function BuildMail()
	{
	
		// build the headers
		$this->headers = "";
	//	$this->xheaders['To'] = implode( ", ", $this->sendto );
		
		if( count($this->acc) > 0 )
			$this->xheaders['CC'] = implode( ", ", $this->acc );
		
		if( count($this->abcc) > 0 ) 
			$this->xheaders['BCC'] = implode( ", ", $this->abcc );
		
	
		if( $this->receipt ) {
			if( isset($this->xheaders["Reply-To"] ) )
				$this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
			else 
				$this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
		}
		
		if( $this->charset != "" ) {
			$this->xheaders["Mime-Version"] = "1.0";
			$this->xheaders["Content-Type"] = "Text/HTML; charset=UTF-8";
			$this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
		}
	
		$this->xheaders["X-Mailer"] = "Php/libMailv1.3";
		
		// include attached files
		if( count( $this->aattach ) > 0 ) {
			$this->_build_attachement();
		} else {
			$this->fullBody = $this->body;
		}
	
		reset($this->xheaders);
		while( list( $hdr,$value ) = each( $this->xheaders )  ) {
			if( $hdr != "Subject" )
				$this->headers .= "$hdr: $value\n";
		}
		
	
	}
	

	/**
	 * Format and send the email
	 * @return boolean
	 */
	function Send(){
		$this->BuildMail();
		
		$this->strTo = implode( ", ", $this->sendto );
		
		// envoie du mail
		$res = @mail( $this->strTo, $this->xheaders['Subject'], utf8_encode($this->fullBody), $this->headers );
		return $res;
	}
	
	
	
	/**
	 * Return the whole email, headers and message
	 * 
	 * Can be used for displaying the message in plain text or logging it
	 * 
	 * @return string
	 */
	function Get(){
		$this->BuildMail();
		$mail = "To: " . $this->strTo . "\n";
		$mail .= $this->headers . "\n";
		$mail .= $this->fullBody;
		return $mail;
	}
	
	
	/**
	 * Check an email adress validity
	 * 
	 * @param string $address email address to check
	 * @return boolean
	 */
	function ValidEmail($address){
		/*if( ereg( ".*<(.+)>", $address, $regs ) ) {
			$address = $regs[1];
		}
		if(ereg( "^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|net|com|gov|mil|org|edu|int)\$",$address) ) 
			return true;
		else
			return false;
			*/
			return true;
	}
	
	

	 /**
	  * Check validity of email addresses
	  * 
	  * @param array $aad -
	  * @return mixed If invalid, output an error message and exit, this may -should- be customized
	  */
	function CheckAdresses( $aad ){
		for($i=0;$i< count( $aad); $i++ ) {
			if( ! $this->ValidEmail( $aad[$i]) ) {
				echo "Class Mail, method Mail : invalid address $aad[$i]";	
				exit;
			}
		}
	}
	
	
	/**
	 * Check and encode attach file
	 * @ignore
	 */
	function _build_attachement(){
	
		$this->xheaders["Content-Type"] = "multipart/mixed;\n boundary=\"$this->boundary\"";
	
		$this->fullBody = "This is a multi-part message in MIME format.\n--$this->boundary\n";
		$this->fullBody .= "Content-Type: Text/HTML; charset=$this->charset\nContent-Transfer-Encoding: $this->ctencoding\n\n" . $this->body ."\n";
		
		$sep= chr(13) . chr(10);
		
		$ata= array();
		$k=0;
		
		// for each attached file, do...
		for( $i=0; $i < count( $this->aattach); $i++ ) {
			
			$filename = $this->aattach[$i];
			$basename = basename($filename);
			$ctype = $this->actype[$i];	// content-type
			$disposition = $this->adispo[$i];
			if( ! file_exists( $filename) ) {
				echo "Class Mail, method attach : file $filename can't be found"; exit;
			}
			$subhdr= "--$this->boundary\nContent-type: $ctype;\n name=\"$basename\"\nContent-Transfer-Encoding: base64\nContent-Disposition: $disposition;\n  filename=\"$basename\"\n";
			$ata[$k++] = $subhdr;
			// non encoded line length
			$linesz= filesize( $filename)+1;
			$fp= fopen( $filename, 'r' );
			$ata[$k++] = chunk_split(base64_encode(fread( $fp, $linesz)));
			fclose($fp);
		}
		$this->fullBody .= implode($sep, $ata);
	}


}