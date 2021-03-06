<?php
/*************************************************************************
    staff.inc.php

    File included on every staff page...handles logins (security) and file path issues.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die('Access denied'); //Say hi to our friend..

if(!file_exists('../main.inc.php')) die('Fatal error... get technical support');

require_once('../main.inc.php');

if(!defined('INCLUDE_DIR')) die('Fatal error... invalid setting.');

/*Some more include defines specific to staff only */
define('STAFFINC_DIR',INCLUDE_DIR.'staff/');
define('SCP_DIR',str_replace('//','/',dirname(__FILE__).'/'));

/* Define tag that included files can check */
define('OSTSCPINC',TRUE);
define('OSTSTAFFINC',TRUE);

/* Tables used by staff only */
define('KB_PREMADE_TABLE',TABLE_PREFIX.'kb_premade');

/* include what is needed on staff control panel */

require_once(INCLUDE_DIR.'class.staff.php');
require_once(INCLUDE_DIR.'class.group.php');
require_once(INCLUDE_DIR.'class.nav.php');
require_once(INCLUDE_DIR.'class.csrf.php');

/* First order of the day is see if the user is logged in and with a valid session.
    * User must be valid staff beyond this point
    * ONLY super admins can access the helpdesk on offline state.
*/
// get previous page
$previousURL = null;
if(isset($_SERVER['HTTP_REFERER'])) {
    $previousURL = $_SERVER['HTTP_REFERER'];
}

if(!function_exists('staffLoginPage')) { //Ajax interface can pre-declare the function to  trap expired sessions.
    function staffLoginPage($msg) {
        global $ost, $cfg;
        $_SESSION['_staff']['auth']['dest'] =
            '/' . ltrim($_SERVER['REQUEST_URI'], '/');
        $_SESSION['_staff']['auth']['msg']=$msg;
        require(SCP_DIR.'login.php');
        exit;
    }
}
function isSearchOrNot()
{
    if(isset($_REQUEST['advsid']) ||(isset($_REQUEST['a']) && $_REQUEST['a'] == 'search') ||$search)
    {
        return true;
    }
    else
    {
        return false;
    }
}


$thisstaff = StaffAuthenticationBackend::getUser();
if(isset($thisstaff)&&!empty($thisstaff))
{
    $originalStaff;
    $originalStaff['isAdmin'] = $thisstaff->isAdmin();
    $originalStaff['assigned_only'] = $thisstaff->showAssignedOnly();
    // if(isset($_SESSION['previous2PageUrl'])&&$_SESSION['previous2PageUrl'])
    // {
    //     $_SESSION['previous3PageUrl'] = true;
    // }
    // if(isset($_SESSION['previousPageUrl'])&&$_SESSION['previousPageUrl'])
    // {
    //     $_SESSION['previous2PageUrl'] = true;
    // }
    
    
    // error_reporting(~0); ini_set('display_errors', 1);
    if(isSearchOrNot())
    {
        $id = $thisstaff->getId();
        if($thisstaff->updateAgentTicketAccess(0))
        {
           
        }
        $thisstaff->reload();
    }
    // else
    // {
    //     $_SESSION['previousPageUrl'] = "http://$_SERVER[HTTP_HOST]/scp";
    // }
}
function changeStaffToOrigin()
{
    if($thisstaff)
    {
        $id = $thisstaff->getId();
        if($thisstaff->updateAgent($originalStaff['assigned_only']))
        {
            // echo "change to admin now";
        }
        $thisstaff->reload();
    }
}
function backToSearchPage()
{
    if(isset($_SESSION['previousPageUrl'])&&$_SESSION['previousPageUrl'] == true)
    {
        // $history_2_Url = $_SESSION['previous2PageUrl'];
        // if(strpos($history_2_Url, 'advsid=')||strpos($history_2_Url, 'a=search'))
        // {
             echo "<script>   
                window.history.go(-2);
               </script>"; 
        // }
    }
    else
    {
          $newUrl = $_SERVER['REQUEST_URI'];
          header('Location: '.$newUrl);  

    }

}
// Bootstrap gettext translations as early as possible, but after attempting
// to sign on the agent
TextDomain::configureForUser($thisstaff);

//1) is the user Logged in for real && is staff.
if (!$thisstaff || !$thisstaff->getId() || !$thisstaff->isValid()) {
    if (isset($_SESSION['_staff']['auth']['msg'])) {
        $msg = $_SESSION['_staff']['auth']['msg'];
        unset($_SESSION['_staff']['auth']['msg']);
    } elseif ($thisstaff && !$thisstaff->isValid())
        $msg = __('Session timed out due to inactivity');
    else
        $msg = __('Authentication Required');

    staffLoginPage($msg);
    exit;
}
//2) if not super admin..check system status and group status
if(!$thisstaff->isAdmin()) {
    //Check for disabled staff or group!
    if(!$thisstaff->isactive() || !$thisstaff->isGroupActive()) {
        staffLoginPage(__('Access Denied. Contact Admin'));
        exit;
    }

    //Staff are not allowed to login in offline mode!!
    if(!$ost->isSystemOnline() || $ost->isUpgradePending()) {
        staffLoginPage(__('System Offline'));
        exit;
    }
}

//Keep the session activity alive
$thisstaff->refreshSession();

/******* CSRF Protectin *************/
// Enforce CSRF protection for POSTS
if ($_POST  && !$ost->checkCSRFToken()) {
    Http::response(400, __('Valid CSRF Token Required'));
    exit;
}

//Add token to the header - used on ajax calls [DO NOT CHANGE THE NAME]
$ost->addExtraHeader('<meta name="csrf_token" content="'.$ost->getCSRFToken().'" />');

/******* SET STAFF DEFAULTS **********/
//Set staff's timezone offset.
$_SESSION['TZ_OFFSET']=$thisstaff->getTZoffset();
$_SESSION['TZ_DST']=$thisstaff->observeDaylight();

define('PAGE_LIMIT', $thisstaff->getPageLimit()?$thisstaff->getPageLimit():DEFAULT_PAGE_LIMIT);

$tabs=array();
$submenu=array();
$exempt = in_array(basename($_SERVER['SCRIPT_NAME']), array('logout.php', 'ajax.php', 'logs.php', 'upgrade.php'));

if($ost->isUpgradePending() && !$exempt) {
    $errors['err']=$sysnotice=__('System upgrade is pending').' <a href="upgrade.php">'.__('Upgrade Now').'</a>';
    require('upgrade.php');
    exit;
} elseif($cfg->isHelpDeskOffline()) {
    $sysnotice='<strong>'.__('System is set to offline mode').'</strong> - '.__('Client interface is disabled and ONLY admins can access staff control panel.');
    $sysnotice.=' <a href="settings.php">'.__('Enable').'</a>.';
}

if (!defined('AJAX_REQUEST'))
    $nav = new StaffNav($thisstaff);

//Check for forced password change.
if($thisstaff->forcePasswdChange() && !$exempt) {
    # XXX: Call staffLoginPage() for AJAX and API requests _not_ to honor
    #      the request
    $sysnotice = __('Password change required to continue');
    require('profile.php'); //profile.php must request this file as require_once to avoid problems.
    exit;
}
$ost->setWarning($sysnotice);
$ost->setPageTitle(__('osTicket :: Staff Control Panel'));

?>
