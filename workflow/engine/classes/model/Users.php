<?php
/**
 * Users.php
 *
 * @package workflow.engine.classes.model
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2011 Colosa Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * For more information, contact Colosa Inc, 2566 Le Jeune Rd.,
 * Coral Gables, FL, 33134, USA, or email info@colosa.com.
 *
 */

//require_once 'classes/model/om/BaseUsers.php';
//require_once 'classes/model/IsoCountry.php';
//require_once 'classes/model/IsoSubdivision.php';
//require_once 'classes/model/IsoLocation.php';

/**
 * Skeleton subclass for representing a row from the 'USERS' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package workflow.engine.classes.model
 */
class Users extends BaseUsers
{

    public function create ($aData)
    {
        $con = Propel::getConnection( UsersPeer::DATABASE_NAME );
        try {
            $this->fromArray( $aData, BasePeer::TYPE_FIELDNAME );
            if ($this->validate()) {
                $result = $this->save();
            } else {
                $e = new Exception(G::LoadTranslation("ID_FAILED_VALIDATION_IN_CLASS1", SYS_LANG, array("CLASS" => get_class($this))));
                $e->aValidationFailures = $this->getValidationFailures();
                throw ($e);
            }
            $con->commit();
            return $result;
        } catch (Exception $e) {
            $con->rollback();
            throw ($e);
        }
    }

    public function userExists ($UsrUid)
    {
        try {
            $oRow = UsersPeer::retrieveByPK( $UsrUid );
            if (! is_null( $oRow )) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $oError) {
            return false;
        }
    }

    public function load ($UsrUid)
    {
        try {
            $oRow = UsersPeer::retrieveByPK( $UsrUid );
            if (! is_null( $oRow )) {
                $this->fromArray(
                    $oRow->toArray( BasePeer::TYPE_FIELDNAME, true ),
                    BasePeer::TYPE_FIELDNAME
                );
                $aFields = $oRow->toArray( BasePeer::TYPE_FIELDNAME );
                $this->setNew( false );
                return $aFields;
            } else {
                throw (new Exception(G::LoadTranslation("ID_USER_UID_DOESNT_EXIST", SYS_LANG, array("USR_UID" => $UsrUid))));
            }
        } catch (PropelException $e) {
            if(empty($oRow)) {
                error_log(\G::LoadTranslation('ID_CONTACT_ADMIN'));
                error_log($e->getTraceAsString());
                $oError = new \Exception(
                    \G::LoadTranslation('ID_ERROR_IN_SERVER').".\n"
                    .\G::LoadTranslation('ID_CONTACT_ADMIN'),
                    0,
                    $e
                );
                throw ($oError);
            }
            //capture invalid birthday date and replace by null
            $msg = $e->getMessage();
            if (strpos( 'Unable to parse value of [usr_birthday]', $msg ) !== false) {
                $oRow->setUsrBirthday( null );
                $oRow->save();
                return $this->load( $UsrUid );
            }
            //capture invalid create date and replace by null
            if (strpos( 'Unable to parse value of [usr_create_date]', $msg ) !== false) {
                $oRow->setUsrCreateDate( null );
                $oRow->save();
                return $this->load( $UsrUid );
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function loadByEmail ($sUsrEmail)
    {
        $c = new Criteria( 'workflow' );

        $c->clearSelectColumns();
        $c->addSelectColumn( UsersPeer::USR_UID );
        $c->addSelectColumn( UsersPeer::USR_USERNAME );
        $c->addSelectColumn( UsersPeer::USR_STATUS );
        $c->addSelectColumn( UsersPeer::USR_FIRSTNAME );
        $c->addSelectColumn( UsersPeer::USR_LASTNAME );

        $c->add( UsersPeer::USR_EMAIL, $sUsrEmail );
        $c->add( UsersPeer::USR_STATUS, array('INACTIVE', 'CLOSED'), Criteria::NOT_IN );
        return $c;
    }

    public function loadByUserEmailInArray ($sUsrEmail)
    {
        $c = $this->loadByEmail( $sUsrEmail );
        $rs = UsersPeer::doSelectRS( $c, Propel::getDbConnection('workflow_ro') );
        $rs->setFetchmode( ResultSet::FETCHMODE_ASSOC );
        $rows = Array ();
        while ($rs->next()) {
            $rows[] = $rs->getRow();
        }
        return $rows;
    }

    public function loadDetails ($UsrUid)
    {
        try {
            $result = array ();
            $oUser = UsersPeer::retrieveByPK( $UsrUid );
            if (! is_null( $oUser )) {
                $result['USR_UID'] = $oUser->getUsrUid();
                $result['USR_USERNAME'] = $oUser->getUsrUsername();
                $result['USR_FULLNAME'] = $oUser->getUsrFirstname() . ' ' . $oUser->getUsrLastname();
                $result['USR_EMAIL'] = $oUser->getUsrEmail();
                return $result;
            } else {
                // return $result;
                throw (new Exception(G::LoadTranslation("ID_USER_UID_DOESNT_EXIST", SYS_LANG, array("USR_UID" => $UsrUid))));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function loadDetailed ($UsrUid)
    {
        try {
            $result = array ();
            $oUser = UsersPeer::retrieveByPK( $UsrUid );

            if (! is_null( $oUser )) {
                $aFields = $oUser->toArray( BasePeer::TYPE_FIELDNAME );
                $this->fromArray( $aFields, BasePeer::TYPE_FIELDNAME );
                $this->setNew( false );

                $aIsoCountry = IsoCountry::findById( $aFields['USR_COUNTRY'] );
                $aIsoSubdivision = IsoSubdivision::findById( $aFields['USR_COUNTRY'], $aFields['USR_CITY'] );
                $aIsoLocation = IsoLocation::findById( $aFields['USR_COUNTRY'], $aFields['USR_CITY'], $aFields['USR_LOCATION'] );

                $aFields["USR_COUNTRY_NAME"]  = (!empty($aIsoCountry["IC_NAME"]))? $aIsoCountry["IC_NAME"] : "";
                $aFields["USR_CITY_NAME"]     = (!empty($aIsoSubdivision["IS_NAME"]))? $aIsoSubdivision["IS_NAME"] : "";
                $aFields["USR_LOCATION_NAME"] = (!empty($aIsoLocation["IL_NAME"]))? $aIsoLocation["IL_NAME"] : "";

                require_once PATH_RBAC . "model/Roles.php";
                $roles = new Roles();
                $role = $roles->loadByCode($aFields['USR_ROLE']);
                $aFields['USR_ROLE_NAME'] = $role['ROL_NAME'];

                if (empty($aFields['USR_DEFAULT_LANG'])) {
                    $aFields['USR_DEFAULT_LANG'] = 'en';
                }
                $translations = new Language();
                $translation  = $translations->loadByCode($aFields['USR_DEFAULT_LANG']);
                $aFields['USR_DEFAULT_LANG_NAME'] = $translation['LANGUAGE_NAME'];

                //Get the fullName with the correct format related to the settings
                $conf = new \Configurations();
                $confEnvSetting = $conf->getFormats();
                $aFields['USR_FULLNAME'] = $conf->usersNameFormatBySetParameters(
                    $confEnvSetting['format'],
                    $aFields['USR_USERNAME'],
                    $aFields['USR_FIRSTNAME'],
                    $aFields['USR_LASTNAME']
                );

                $result = $aFields;

                return $result;
            } else {
                //return $result;
                throw (new Exception(G::LoadTranslation("ID_USER_UID_DOESNT_EXIST", SYS_LANG, array("USR_UID" => $UsrUid))));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function update ($fields)
    {
        $con = Propel::getConnection( UsersPeer::DATABASE_NAME );
        try {
            $con->begin();
            $this->load( $fields['USR_UID'] );
            $this->fromArray( $fields, BasePeer::TYPE_FIELDNAME );
            if ($this->validate()) {
                $result = $this->save();
                $con->commit();
                return $result;
            } else {
                $con->rollback();
                throw (new Exception(G::LoadTranslation("ID_FAILED_VALIDATION_IN_CLASS1", SYS_LANG, array("CLASS" => get_class($this)))));
            }
        } catch (Exception $e) {
            $con->rollback();
            throw ($e);
        }
    }

    public function remove ($UsrUid)
    {
        $con = Propel::getConnection( UsersPeer::DATABASE_NAME );
        try {
            $con->begin();
            $this->setUsrUid( $UsrUid );
            $result = $this->delete();
            $con->commit();
            return $result;
        } catch (Exception $e) {
            $con->rollback();
            throw ($e);
        }
    }

    public function loadByUsername ($sUsername)
    {
        $c = new Criteria( 'workflow' );
        $del = DBAdapter::getStringDelimiter();

        $c->clearSelectColumns();
        $c->addSelectColumn( UsersPeer::USR_UID );
        $c->addSelectColumn( UsersPeer::USR_USERNAME );
        $c->addSelectColumn( UsersPeer::USR_STATUS );

        $c->add( UsersPeer::USR_USERNAME, $sUsername );
        return $c;
    }

    public function loadByUsernameInArray ($sUsername)
    {
        $c = $this->loadByUsername( $sUsername );
        $rs = UsersPeer::doSelectRS( $c, Propel::getDbConnection('workflow_ro') );
        $rs->setFetchmode( ResultSet::FETCHMODE_ASSOC );
        $rs->next();
        $row = $rs->getRow();
        return $row;
    }

    /**
     * Get all information about the user
     * @param string $userUid
     * @return array $arrayData
     * @throws Exception
    */
    public function getAllInformation ($userUid)
    {
        if (!isset($userUid) || empty($userUid)) {
            throw (new Exception('$userUid is empty.'));
        }
        if (RBAC::isGuestUserUid($userUid)) {
            throw new Exception(G::LoadTranslation("ID_USER_CAN_NOT_UPDATE", array($userUid)));
            return false;
        }

        try {
            $aFields = $this->load( $userUid );

            $c = new Criteria( "workflow" );
            $c->add( IsoCountryPeer::IC_UID, $aFields["USR_COUNTRY"] );
            $rs = IsoCountryPeer::doSelectRS( $c );
            $rs->setFetchmode( ResultSet::FETCHMODE_ASSOC );
            $rs->next();
            $rowC = $rs->getRow();

            $c->clearSelectColumns();
            $c->add( IsoSubdivisionPeer::IC_UID, $aFields["USR_COUNTRY"] );
            $c->add( IsoSubdivisionPeer::IS_UID, $aFields["USR_CITY"] );
            $rs = IsoSubdivisionPeer::doSelectRS( $c );
            $rs->setFetchmode( ResultSet::FETCHMODE_ASSOC );
            $rs->next();
            $rowS = $rs->getRow();

            $c->clearSelectColumns();
            $c->add( IsoLocationPeer::IC_UID, $aFields["USR_COUNTRY"] );
            $c->add( IsoLocationPeer::IL_UID, $aFields["USR_LOCATION"] );
            $rs = IsoLocationPeer::doSelectRS( $c );
            $rs->setFetchmode( ResultSet::FETCHMODE_ASSOC );
            $rs->next();
            $rowL = $rs->getRow();

            //Calendar
            $calendar = new Calendar();
            $calendarInfo = $calendar->getCalendarFor( $userUid, $userUid, $userUid );
            $aFields["USR_CALENDAR"] = ($calendarInfo["CALENDAR_APPLIED"] != "DEFAULT") ? $calendarInfo["CALENDAR_UID"] : "";

            //Photo
            $pathPhoto = PATH_IMAGES_ENVIRONMENT_USERS . $userUid . ".gif";

            if (! file_exists( $pathPhoto )) {
                $pathPhoto = PATH_HOME . "public_html" . PATH_SEP . "images" . PATH_SEP . "user.gif";
            }

            //Data
            $arrayData = array ();
            $arrayData["username"] = $aFields["USR_USERNAME"];
            $arrayData["firstname"] = $aFields["USR_FIRSTNAME"];
            $arrayData["lastname"] = $aFields["USR_LASTNAME"];
            $arrayData["mail"] = $aFields["USR_EMAIL"];
            $arrayData["address"] = $aFields["USR_ADDRESS"];
            $arrayData["zipcode"] = $aFields["USR_ZIP_CODE"];
            $arrayData["country"] = $rowC["IC_NAME"];
            $arrayData["state"] = $rowS["IS_NAME"];
            $arrayData["location"] = $rowL["IL_NAME"];
            $arrayData["phone"] = $aFields["USR_PHONE"];
            $arrayData["fax"] = $aFields["USR_FAX"];
            $arrayData["cellular"] = $aFields["USR_CELLULAR"];
            $arrayData["birthday"] = $aFields["USR_BIRTHDAY"];
            $arrayData["position"] = $aFields["USR_POSITION"];
            $arrayData["replacedby"] = $aFields["USR_REPLACED_BY"];
            if(strlen($arrayData["replacedby"] != 0)){
                $oUser = UsersPeer::retrieveByPK( $arrayData["replacedby"] );
                $arrayData["replacedbyfullname"] = $oUser->getUsrFirstname() . ' ' . $oUser->getUsrLastname();
            }
            $arrayData["duedate"] = $aFields["USR_DUE_DATE"];
            $arrayData["calendar"] = $aFields["USR_CALENDAR"];
            if(strlen($aFields["USR_CALENDAR"] != 0)){
                $arrayData["calendarname"] = $calendar->calendarName( $aFields["USR_CALENDAR"] );
            }
            $arrayData["status"] = $aFields["USR_STATUS"];
            $arrayData["department"] = $aFields["DEP_UID"];
            if (strlen($arrayData["department"]) != 0) {
                $oDepart = DepartmentPeer::retrieveByPk( $arrayData["department"] );
                $arrayData["departmentname"] = $oDepart->getDepTitle();
            }
            $arrayData["reportsto"] = $aFields["USR_REPORTS_TO"];
            $arrayData["userexperience"] = $aFields["USR_UX"];
            $arrayData["photo"] = $pathPhoto;

            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAvailableUsersCriteria ($sGroupUID = '')
    {
        try {

            $oCriteria = new Criteria( 'workflow' );
            $oCriteria->addSelectColumn( UsersPeer::USR_UID );
            $oCriteria->addSelectColumn( UsersPeer::USR_FIRSTNAME );
            $oCriteria->addSelectColumn( UsersPeer::USR_LASTNAME );
            $oCriteria->add( UsersPeer::USR_STATUS, 'ACTIVE' );

            return $oCriteria;
        } catch (exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Get all Active users
     *
     * @return array of all active users
     */
    public function getAll ($start = null, $limit = null, $search = null)
    {
        $totalCount = 0;
        $criteria = new Criteria( 'workflow' );
        $criteria->addSelectColumn( UsersPeer::USR_UID );
        $criteria->addSelectColumn( UsersPeer::USR_USERNAME );
        $criteria->addSelectColumn( UsersPeer::USR_FIRSTNAME );
        $criteria->addSelectColumn( UsersPeer::USR_LASTNAME );
        $criteria->add( UsersPeer::USR_STATUS, 'ACTIVE' );
        $criteria->addAscendingOrderByColumn( UsersPeer::USR_LASTNAME );

        if ($search) {
            $criteria->add( $criteria->getNewCriterion( UsersPeer::USR_USERNAME, "%$search%", Criteria::LIKE )->addOr( $criteria->getNewCriterion( UsersPeer::USR_FIRSTNAME, "%$search%", Criteria::LIKE ) )->addOr( $criteria->getNewCriterion( UsersPeer::USR_LASTNAME, "%$search%", Criteria::LIKE ) ) );
        }

        $c = clone $criteria;
        $c->clearSelectColumns();
        $c->addSelectColumn( 'COUNT(*)' );
        $dataset = UsersPeer::doSelectRS( $c );
        $dataset->next();
        $rowCount = $dataset->getRow();

        if (is_array( $rowCount )) {
            $totalCount = $rowCount[0];
        }

        if ($start) {
            $criteria->setOffset( $start );
        }
        if ($limit) {
            $criteria->setLimit( $limit );
        }

        $rs = UsersPeer::doSelectRS( $criteria );
        $rs->setFetchmode( ResultSet::FETCHMODE_ASSOC );

        $rows = Array ();
        while ($rs->next()) {
            $rows[] = $rs->getRow();
        }

        $result = new stdClass();
        $result->data = $rows;
        $result->totalCount = $totalCount;

        return $result;
    }

    public function userVacation ($UsrUid = "")
    {
        $aFields = array ();
        $cnt = 0;
        do {
            if ($UsrUid != "" && $cnt < 100) {
                $aFields = $this->load( $UsrUid );
                $UsrUid = $aFields['USR_REPLACED_BY'];
            } else {
                break;
            }
            $cnt ++;
        } while ($aFields['USR_STATUS'] != 'ACTIVE');
        return $aFields;
    }

    /**
     * @Deprecated
     * @param $userId
     * @param string $type
     * @param string $list
     * @param int $total
     * @throws Exception
     */
    public function refreshTotal($userId, $type = 'add', $list = "inbox", $total = 1)
    {
        throw new Exception("This method (refreshTotal) is no longer in use. Please remove reference.");
    }

    public function userLanguaje ($usrUid = "")
    {
        try {

            $oCriteria = new Criteria( 'workflow' );
            $oCriteria->addSelectColumn( UsersPeer::USR_UID );
            $oCriteria->addSelectColumn( UsersPeer::USR_DEFAULT_LANG );
            $oCriteria->add( UsersPeer::USR_UID, $usrUid );
            $rsCriteria = UsersPeer::doSelectRS( $oCriteria );
            $rsCriteria->setFetchmode( ResultSet::FETCHMODE_ASSOC );

            return $rsCriteria;
        } catch (exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Load a process object by USR_ID
     *
     * @param type $id
     * @return Users
     */
    public static function loadById($id) {
        $criteria = new Criteria(UsersPeer::DATABASE_NAME);
        $criteria->add(UsersPeer::USR_ID, $id);
        return UsersPeer::doSelect($criteria)[0];
    }
    
    /**
     * {@inheritdoc} except USR_PASSWORD, for security reasons.
     *
     * @param string $keyType One of the class type constants TYPE_PHPNAME,
     *                        TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM
     * @param boolean $original If true return de original verion of fields.
     * @return an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $original = false)
    {
        if ($original) {
            return parent::toArray($keyType);
        }
        $key = UsersPeer::translateFieldName(
            UsersPeer::USR_PASSWORD,
            BasePeer::TYPE_COLNAME,
            $keyType
        );
        $array = parent::toArray($keyType);
        unset($array[$key]);
        return $array;
    }
}
