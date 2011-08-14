<?php
define( '_JEXEC', 1 );
define('JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
/* Required Files */
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
/* To use Joomla's Database Class */
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'factory.php' );
/* Create the Application */
require_once("configuration.php");

/* Create the Application */
$mainframe =& JFactory::getApplication('site');

/* we are going to use the default mail settings of the joomla install */
jimport('joomla.user.helper');
jimport('joomla.mail.helper');

class Reregister
{ 

  public  $_id = null;
  public  $_user = null;
  public  $_password = null;
  public  $_password_hash = null;
  public  $_email = null;
  public  $_username = null;
  public  $_activation = null;
    
    /* For each user we are going to make a query for the id, and get everything we need. This way we can do lots of users in a loop or just one. */
    
    public function __construct($id) {
       
       $db =& JFactory::getDBO();
       $query = "select * from `jos_users` where `id` = '{$id}'";
       $db->setQuery($query);
       $this->_user = $db->loadObjectList();
       
               
        if($this->_user) {
          $this->_id = $id;
          $this->_password = JUserHelper::genRandomPassword();
          $this->_password_hash = md5($this->_password);
          $this->_email = $this->_user['0']->email;
          $this->_username = $this->_user['0']->username;
          $this->_activation = JUtility::getHash(JUserHelper::genRandomPassword());
       
        } 
        
       
    }
    // quick and dirty query, so after you create the object, you just call the update method. $user = New Reregister($id); $user->update();
   public function update() {
       $db =& JFactory::getDBO(); 
       $query = "UPDATE jos_users SET `password`='{$this->_password_hash}', `activation`='{$this->_activation}', `lastvisitDate`='0000-00-00 00:00:00', `block`='1' where `id`='{$this->_id}'";
      
       $query = $db->setQuery($query);
   
       $result = $db->query();
         if(!$result ) {
           die('OMG'. mysql_error());
       }
       //$this->mailtime();
    }
    
    // I have mailtime as its own method here,   I called them one after another you could if you wanted  call mailtime at the end of update method. 
    public function mailtime() {
        
        $body = 'We have upgraded our website! Come see what is new!'. "\n";
        $body .= 'We have made several changes to our system, and need you to confirm your information.'. "\n";
	$body .= 'Reactive your account now by clicking the following link.'. "\n";
        $body .= 'http://website.com/component/user/?task=activate&activation='.$this->_activation  . "\n";
	$body .= ''. "\n";
	$body .= 'Once you have activated you can login.'. "\n";
	$body .= 'http://insideschools.org/login'. "\n";
	$body .= 'Username: '.$this->_username  . "\n";
	$body .= 'Password: '.$this->_password  . ' (You will be forced change your password.)'. "\n";
	$body .= ''. "\n";
	$body .= 'If you do not wish to activate, just do nothing and eventually your account will be removed.'. "\n";

 
        
        
        $mail = JFactory::getMailer();

	$mail->addRecipient( $this->_email );
	$mail->setSender( array('contact@insideschools.org', 'Insideschools.org' ) );
	$mail->setSubject('Insideschools New Site : Require activation');
	$mail->setBody( $body );
        $sent = $mail->Send();
        
        
        
        
    }
    
    
    
    
}

//You can call the method however you want.  I had lots of users i wanted to change their information so i used a query like this, to avoid having it reregiser someone again after they just activate if the script was still running. 
       $db =& JFactory::getDBO(); 
       $query = "select * from `jos_users` where `gid` = '18' AND `block` = '0' AND (`activation` = '' OR `activation` IS NULL) AND `registerDate` < '2011-08-06 22:41:45' AND `lastvisitDate` < '2011-08-13 22:41:45' order by `id` limit 0,1000 ";
       $query = $db->setQuery($query);
       $result = $db->loadResultArray();
       
 foreach ($result as $id) {
$user = New Reregister($id);

$user->update();

$user->mailtime();
  }      
       








?>
