<?php
/**
 * Subclass for representing a row from the 'sf_audit' table.
 *
 *
 *
 * @package plugins.sfPropelAuditPlugin.lib.model
 */
class sfAudit extends BasesfAudit
{
    public function getObjectChanges()
    {
        $ret = array();
        $changes = parent::getObjectChanges();
        
        if (is_string($changes) && strlen($changes) > 0) {
            $ret = unserialize($changes);
        } // End if

        return $ret;
        
    } // End function

} // End class
