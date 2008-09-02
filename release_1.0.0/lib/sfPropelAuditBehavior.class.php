<?php
/**
 * sfPropelAuditBehavior adds audit tracking.
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Sacha Telgenhof Oude Koehorst <s.telgenhof@xs4all.nl>
 * @version    SVN: $Id:$
 */
class sfPropelAuditBehavior
{
    /**
     * Holds the name of the component for logging.
     *
     * @var string
     */
    protected $type = '{sfAudit}';

    /**
     * Holds the date format that is being used for storing the entry in the audit table.
     *
     * @var string
     */
    protected $date_format = 'Y-m-d H:i:s';
    const TYPE_INSERT = 'INSERT';
    const TYPE_UPDATE = 'UPDATE';
    const TYPE_DELETE = 'DELETE';
    const TYPE_SELECT = 'SELECT';

    /**
     * Hook function to the Peer Class function doUpdate (pre)
     *
     * @param array $class The name of the object
     * @param mixed $values Criteria or object containing data that is used to create the INSERT statement.
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @return bool
     */
    public function preDoUpdate($class, $values, $con)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' preDOUPDATE');
        } // End if
        return false;
    } // End function

    /**
     * Hook function to the Peer Class function doUpdate (post)
     *
     * @param array $class The name of the object
     * @param mixed $values Criteria or object containing data that is used to create the INSERT statement.
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @param int $affectedrows The number of affected rows (if supported by underlying database driver).
     * @return bool
     */
    public function postDoUpdate($class, $values, $con, $affectedrows)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' postDOUPDATE');
        } // End if

        // Do not keep track of this change when no rows are affected
        if (!$affectedrows) return false;

        foreach(call_user_func(array($class, 'getFieldNames') , BasePeer::TYPE_COLNAME) as $column) {

            if ($values->isColumnModified($column)) {
                $column_phpname = call_user_func(array($class, 'translateFieldName') , $column, BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);
                $method = 'get'.sfInflector::camelize($column_phpname);
                $changes[$column_phpname] = $values->$method();
            } // End if

        } // End foreach
        $this->save(get_class($values) , $values->getPrimaryKey() , serialize($changes) , $con->getLastExecutedQuery() , self::TYPE_UPDATE);
        return true;
    } // End function

    /**
     * Hook function to the Peer Class function doInsert (pre)
     *
     * @param array $class The name of the object
     * @param mixed $values Criteria or object containing data that is used to create the INSERT statement.
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @return bool
     */
    public function preDoInsert($class, $values, $con)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' preDOINSERT');
        } // End if
        return false;
    } // End function

    /**
     * Hook function to the Peer Class function doInsert (post)
     *
     * @param array $class The name of the object
     * @param mixed $values Criteria or object containing data that is used to create the INSERT statement.
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @param mixed $pk The primary key of the object
     * @return bool
     */
    public function postDoInsert($class, $values, $con, $pk)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' postDOINSERT');
        } // End if
        $this->save(get_class($values) , $values->getPrimaryKey() , null, $con->getLastExecutedQuery() , self::TYPE_INSERT);
    } // End function

    /**
     * Hook function to the Base Class function Save (pre)
     *
     * @param array $object The name of the object
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @return bool
     */
    public function preSave($object, $con = null)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' preSAVE');
        } // End if
        return true;
    } // End function

    /**
     * Hook function to the Base Class function Save (post)
     *
     * @param array $object The name of the object
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @param int $affectedRows Number of rows affected by the change.
     * @return bool
     */
    public function postSave($object, $con = null, $affectedRows)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' postSAVE');
        } // End if
        return true;
    } // End function

    /**
     * Hook function to the Base Class function Delete (pre)
     *
     * @param array $object The name of the object
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @return bool
     */
    public function preDelete($object, $con = null)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' preDELETE');
        } // End if

    } // End function

    /**
     * Hook function to the Base Class function Save (post)
     *
     * @param array $object The name of the object
     * @param Connection $con The connection to use (specify Connection object to exert more control over transactions).
     * @return bool
     */
    public function postDelete($object, $con = null)
    {

        if (sfConfig::get('sf_audit_logging')) {
            $context = sfContext::getInstance();
            $context->getLogger()->info($this->type.' postDELETE');
        } // End if
        $this->save(get_class($object) , $object->getPrimaryKey() , null, $con->getLastExecutedQuery() , self::TYPE_DELETE);
    } // End function

    /**
     * Internal function which will create an audit record for the object that was
     * being tracked.
     *
     * @param string $object The name of the object that was being tracked.
     * @param mixed $object_key The primary key of the object that was being tracked.
     * @param string $changes A (serialized) string containing the individual changes of the object.
     * @param string $query The SQL query that was executed for this record.
     * @param string $type The audit type. This can be one of the following constants:
     TYPE_INSERT, TYPE_UPDATE, TYPE_DELETE, or TYPE_SELECT
     * @return void
     */
    private function save($object, $object_key, $changes, $query, $type)
    {
        $audit = new sfAudit();
        $audit->setRemoteIpAddress($_SERVER['REMOTE_ADDR']);
        $audit->setObject($object);
        $audit->setObjectKey($object_key);
        $audit->setObjectChanges($changes);
        $audit->setQuery($query);
        $audit->setUser(sfContext::getInstance()->getUser());
        $audit->setType($type);
        $audit->setCreatedAt(date($this->date_format));
        $audit->save();
    } // End function

} // End class
