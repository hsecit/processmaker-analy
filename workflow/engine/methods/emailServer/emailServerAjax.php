<?php

use ProcessMaker\Core\System;
use ProcessMaker\GmailOAuth\GmailOAuth;
use ProcessMaker\Office365OAuth\Office365OAuth;

$option = (isset($_POST["option"])) ? $_POST["option"] : "";
$response = [];

$RBAC->allows(basename(__FILE__), $option);
switch ($option) {
    case "INS":
        $arrayData = [];

        $server = "";
        $port = "";
        $incomingServer = "";
        $incomingPort = "";
        $reqAuthentication = 0;
        $password = "";
        $smtpSecure = "";

        $cboEmailEngine = $_POST["cboEmailEngine"];
        $accountFrom = (isset($_POST["accountFrom"])) ? $_POST["accountFrom"] : "";
        $fromName = $_POST["fromName"];
        $fromMail = $_POST["fromMail"];
        $sendTestMail = (int) ($_POST["sendTestMail"]);
        $mailTo = ($sendTestMail == 1) ? $_POST["mailTo"] : "";
        $emailServerDefault = (int) ($_POST["emailServerDefault"]);

        if ($cboEmailEngine == "PHPMAILER") {
            $server = $_POST["server"];
            $port = (int) ($_POST["port"]);
            $reqAuthentication = (int) ($_POST["reqAuthentication"]);
            $password = ($reqAuthentication == 1) ? $_POST["password"] : "";
            $smtpSecure = $_POST["smtpSecure"];
        } elseif ($cboEmailEngine == "IMAP") {
            $server = $_POST["server"];
            $port = (int) ($_POST["port"]);
            $incomingServer = $_POST["incomingServer"];
            $incomingPort = (int) ($_POST["incomingPort"]);
            $reqAuthentication = (int) ($_POST["reqAuthentication"]);
            $password = ($reqAuthentication == 1) ? $_POST["password"] : "";
            $smtpSecure = $_POST["smtpSecure"];
        }

        try {
            $arrayData = array(
                "MESS_ENGINE" => $cboEmailEngine,
                "MESS_SERVER" => $server,
                "MESS_PORT" => $port,
                "MESS_INCOMING_SERVER" => $incomingServer,
                "MESS_INCOMING_PORT" => $incomingPort,
                "MESS_RAUTH" => $reqAuthentication,
                "MESS_ACCOUNT" => $accountFrom,
                "MESS_PASSWORD" => $password,
                "MESS_FROM_MAIL" => $fromMail,
                "MESS_FROM_NAME" => $fromName,
                "SMTPSECURE" => $smtpSecure,
                "MESS_TRY_SEND_INMEDIATLY" => $sendTestMail,
                "MAIL_TO" => $mailTo,
                "MESS_DEFAULT" => $emailServerDefault
            );

            $emailSever = new \ProcessMaker\BusinessModel\EmailServer();

            $arrayEmailServerData = $emailSever->create($arrayData);

            $response["status"] = "OK";
            $response["data"] = $arrayEmailServerData;
        } catch (Exception $e) {
            $response["status"] = "ERROR";
            $response["message"] = $e->getMessage();
        }
        break;
    case "UPD":
        $arrayData = [];

        $emailServerUid = $_POST["emailServerUid"];

        $server = "";
        $port = "";
        $incomingServer = "";
        $incomingPort = "";
        $reqAuthentication = 0;
        $password = "";
        $smtpSecure = "";

        $cboEmailEngine = $_POST["cboEmailEngine"];
        $accountFrom = (isset($_POST["accountFrom"])) ? $_POST["accountFrom"] : "";
        $fromName = $_POST["fromName"];
        $fromMail = $_POST["fromMail"];
        $sendTestMail = (int) ($_POST["sendTestMail"]);
        $mailTo = ($sendTestMail == 1) ? $_POST["mailTo"] : "";
        $emailServerDefault = (int) ($_POST["emailServerDefault"]);

        if ($cboEmailEngine == "PHPMAILER") {
            $server = $_POST["server"];
            $port = (int) ($_POST["port"]);
            $reqAuthentication = (int) ($_POST["reqAuthentication"]);
            $password = ($reqAuthentication == 1) ? $_POST["password"] : "";
            $smtpSecure = $_POST["smtpSecure"];
        } elseif ($cboEmailEngine == "IMAP") {
            $server = $_POST["server"];
            $port = (int) ($_POST["port"]);
            $incomingServer = $_POST["incomingServer"];
            $incomingPort = (int) ($_POST["incomingPort"]);
            $reqAuthentication = (int) ($_POST["reqAuthentication"]);
            $password = ($reqAuthentication == 1) ? $_POST["password"] : "";
            $smtpSecure = $_POST["smtpSecure"];
        }

        try {
            $arrayData = array(
                "MESS_ENGINE" => $cboEmailEngine,
                "MESS_SERVER" => $server,
                "MESS_PORT" => $port,
                "MESS_INCOMING_SERVER" => $incomingServer,
                "MESS_INCOMING_PORT" => $incomingPort,
                "MESS_RAUTH" => $reqAuthentication,
                "MESS_ACCOUNT" => $accountFrom,
                "MESS_PASSWORD" => $password,
                "MESS_FROM_MAIL" => $fromMail,
                "MESS_FROM_NAME" => $fromName,
                "SMTPSECURE" => $smtpSecure,
                "MESS_TRY_SEND_INMEDIATLY" => $sendTestMail,
                "MAIL_TO" => $mailTo,
                "MESS_DEFAULT" => $emailServerDefault
            );

            $emailSever = new \ProcessMaker\BusinessModel\EmailServer();

            $arrayEmailServerData = $emailSever->update($emailServerUid, $arrayData);

            $response["status"] = "OK";
            $response["data"] = $arrayEmailServerData;
        } catch (Exception $e) {
            $response["status"] = "ERROR";
            $response["message"] = $e->getMessage();
        }

        break;
    case "DEL":
        $emailServerUid = $_POST["emailServerUid"];

        try {
            $emailSever = new \ProcessMaker\BusinessModel\EmailServer();

            $result = $emailSever->delete($emailServerUid);

            $response["status"] = "OK";
        } catch (Exception $e) {
            $response["status"] = "ERROR";
            $response["message"] = $e->getMessage();
        }
        break;
    case "LST":
        $pageSize = $_POST["pageSize"];
        $search = $_POST["search"];

        $sortField = (isset($_POST["sort"])) ? $_POST["sort"] : "";
        $sortDir = (isset($_POST["dir"])) ? $_POST["dir"] : "";
        $start = (isset($_POST["start"])) ? $_POST["start"] : 0;
        $limit = (isset($_POST["limit"])) ? $_POST["limit"] : $pageSize;

        try {
            $emailSever = new \ProcessMaker\BusinessModel\EmailServer();

            $result = $emailSever->getEmailServers(array("filter" => $search), $sortField, $sortDir, $start, $limit);

            $response["status"] = "OK";
            $response["success"] = true;
            $response["resultTotal"] = $result["total"];
            $response["resultRoot"] = $result["data"];
        } catch (Exception $e) {
            $response["status"] = "ERROR";
            $response["message"] = $e->getMessage();
        }
        break;
    case "TEST":
        $arrayData = [];

        $server = "";
        $port = "";
        $incomingServer = "";
        $incomingPort = "";
        $reqAuthentication = 0;
        $password = "";
        $smtpSecure = "";

        $cboEmailEngine = $_POST["cboEmailEngine"];
        $accountFrom = (isset($_POST["accountFrom"])) ? $_POST["accountFrom"] : "";
        $fromName = $_POST["fromName"];
        $fromMail = $_POST["fromMail"];
        $sendTestMail = (int) ($_POST["sendTestMail"]);
        $mailTo = ($sendTestMail == 1) ? $_POST["mailTo"] : "";
        $emailServerDefault = (int) ($_POST["emailServerDefault"]);

        if ($cboEmailEngine == "PHPMAILER" || $cboEmailEngine == "IMAP") {
            $server = $_POST["server"];
            $port = (int) ($_POST["port"]);
            $reqAuthentication = (int) ($_POST["reqAuthentication"]);
            $password = ($reqAuthentication == 1) ? $_POST["password"] : "";
            $smtpSecure = $_POST["smtpSecure"];
        }

        try {
            $arrayData = array(
                "MESS_ENGINE" => $cboEmailEngine,
                "MESS_SERVER" => $server,
                "MESS_PORT" => $port,
                "MESS_RAUTH" => $reqAuthentication,
                "MESS_ACCOUNT" => $accountFrom,
                "MESS_PASSWORD" => $password,
                "MESS_FROM_MAIL" => $fromMail,
                "MESS_FROM_NAME" => $fromName,
                "SMTPSECURE" => $smtpSecure,
                "MESS_TRY_SEND_INMEDIATLY" => $sendTestMail,
                "MAIL_TO" => $mailTo,
                "MESS_DEFAULT" => $emailServerDefault
            );

            $emailSever = new \ProcessMaker\BusinessModel\EmailServer();

            $arrayEmailServerData = $emailSever->testConnection($arrayData);

            $response["data"] = $arrayEmailServerData;
        } catch (Exception $e) {
            $response["status"] = "ERROR";
            $response["message"] = $e->getMessage();
        }
        break;
    case "createAuthUrl":
        try {
            $gmailOAuth = new GmailOAuth();
            $gmailOAuth->setServer($_POST['server']);
            $gmailOAuth->setPort($_POST['port']);
            $gmailOAuth->setClientID($_POST['clientID']);
            $gmailOAuth->setClientSecret($_POST['clientSecret']);
            $gmailOAuth->setRedirectURI(System::getServerMainPath() . "/emailServer/emailServerGmailOAuth");
            $gmailOAuth->setEmailEngine($_POST['emailEngine']);
            $gmailOAuth->setFromAccount($_POST['fromAccount']);
            $gmailOAuth->setSenderEmail($_POST['senderEmail']);
            $gmailOAuth->setSenderName($_POST['senderName']);
            $gmailOAuth->setSendTestMail((int) $_POST['sendTestMail']);
            $gmailOAuth->setMailTo($_POST['mailTo']);
            $gmailOAuth->setSetDefaultConfiguration((int) $_POST['setDefaultConfiguration']);
            if (!empty($_POST['emailServerUid'])) {
                $gmailOAuth->setEmailServerUid($_POST['emailServerUid']);
            }
            $client = $gmailOAuth->getGoogleClient();
            $response = [
                "status" => 200,
                "data" => $client->createAuthUrl()
            ];
            $_SESSION['gmailOAuth'] = $gmailOAuth;
        } catch (Exception $e) {
            $response = [
                "status" => 500,
                "message" => $e->getMessage()
            ];
        }
        break;
    case "createAuthUrlOffice365":
        try {
            $office365OAuth = new Office365OAuth();
            $office365OAuth->setServer($_POST['server']);
            $office365OAuth->setPort($_POST['port']);
            $office365OAuth->setClientID($_POST['clientID']);
            $office365OAuth->setClientSecret($_POST['clientSecret']);
            $office365OAuth->setRedirectURI(System::getServerMainPath() . "/emailServer/emailServerOffice365OAuth");
            $office365OAuth->setEmailEngine($_POST['emailEngine']);
            $office365OAuth->setFromAccount($_POST['fromAccount']);
            $office365OAuth->setSenderEmail($_POST['senderEmail']);
            $office365OAuth->setSenderName($_POST['senderName']);
            $office365OAuth->setSendTestMail((int) $_POST['sendTestMail']);
            $office365OAuth->setMailTo($_POST['mailTo']);
            $office365OAuth->setSetDefaultConfiguration((int) $_POST['setDefaultConfiguration']);

            if (!empty($_POST['emailServerUid'])) {
                $office365OAuth->setEmailServerUid($_POST['emailServerUid']);
            }
            $client = $office365OAuth->getOffice365Client();
            $response = [
                "status" => 200,
                "data" => $client->getAuthorizationUrl($office365OAuth->getOptions())
            ];
            $_SESSION['office365OAuth'] = $office365OAuth;
        } catch (Exception $e) {
            $response = [
                "status" => 500,
                "message" => $e->getMessage()
            ];
        }
        break;
}

echo G::json_encode($response);
