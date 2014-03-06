<?php
if (! defined('DOKU_INC'))
    die();

class auth_plugin_authsimplesamlphp extends DokuWiki_Auth_Plugin
{
    var $ssp;

    public function __construct()
    {
        parent::__construct();

        $this->cando['external'] = true;
        $this->cando['logoff'] = true;
        $this->success = true;

        $this->loadConfig();
        require_once($this->conf["path"].'/lib/_autoload.php');
        $this->ssp = new SimpleSAML_Auth_Simple($this->conf["authsource"]);
    }

    public function getUserData($user)
    {
        global $USERINFO;
        $this->ssp->requireAuth();
        if ($this->ssp->isAuthenticated()) {
                $attributes = $this->ssp->getAttributes();
                $USERINFO['name'] = $attributes[$this->conf["uid"]][0];
                $USERINFO['mail'] = $attributes[$this->conf["mail"]][0];
                $USERINFO['grps'] = array_values($attributes[$this->conf["grps"]]);
                return $USERINFO;
        }
        return null;
    }

    public function checkPass($user, $pass) {
        return false;
    }

    public function trustExternal()
    {
        global $USERINFO;
        $this->ssp->requireAuth();
        if ($this->ssp->isAuthenticated()) {
                $attributes = $this->ssp->getAttributes();

                $USERINFO['name'] = $attributes[$this->conf["uid"]][0];
                $USERINFO['mail'] = $attributes[$this->conf["mail"]][0];
                $USERINFO['grps'] = array_values($attributes[$this->conf["grps"]]);

                $_SERVER['REMOTE_USER']                = $attributes[$this->conf["uid"]][0];
                $_SESSION[DOKU_COOKIE]['auth']['user'] = $attributes[$this->conf["uid"]][0];
                $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;

            return true;
        }

        return false;
    }
}
