<?php
sfConfig::set('sf_audit_logging', true);

sfPropelBehavior::registerHooks('audit', array(
    ':save:pre' => array(
        'sfPropelAuditBehavior',
        'preSave'
    ) ,
    ':save:post' => array(
        'sfPropelAuditBehavior',
        'postSave'
    ) ,
    ':delete:pre' => array(
        'sfPropelAuditBehavior',
        'preDelete'
    ) ,
    ':delete:post' => array(
        'sfPropelAuditBehavior',
        'postDelete'
    ) ,
    'Peer:doInsert:pre' => array(
        'sfPropelAuditBehavior',
        'preDoInsert'
    ) ,
    'Peer:doInsert:post' => array(
        'sfPropelAuditBehavior',
        'postDoInsert'
    ) ,
    'Peer:doUpdate:pre' => array(
        'sfPropelAuditBehavior',
        'preDoUpdate'
    ) ,
    'Peer:doUpdate:post' => array(
        'sfPropelAuditBehavior',
        'postDoUpdate'
    ) ,
));
