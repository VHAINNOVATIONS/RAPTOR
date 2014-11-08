<?php namespace raptor;

interface IMdwsDao {
    //put your code here
    public function connectAndLogin($siteCode, $username, $password);
    
    public function connectRemoteSites($applicationPassword);
    
    public function disconnect();
    
    public function makeQuery($functionToInvoke, $args);

    public function makeStatelessQuery($siteCode, $username, $password, $patientId, $functionToInvoke, $args, $multiSiteFlag, $appPwd);
    
    public function isAuthenticated();
}
