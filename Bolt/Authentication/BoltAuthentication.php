<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * Bolt Authentication
 * =====================        ========================
 * =====================================================
 */

namespace Bolt\Bolt\Authentication;

use Bolt\Bolt\Bolt;
use Bolt\Bolt\Cookie;
use Bolt\Bolt\Session;
use Bolt\Bolt\Database\DatabaseModel;

class BoltAuthentication extends DatabaseModel
{
    protected $_current_user = false;
    private Session $session;

    public static function tableName(): string
    {
        return "users";
    }

    public function login()
    {
    }

    private function fromCookie()
    {
        $user_cookie_name = Bolt::$bolt->config->get("user_token");

        if (!Cookie::has($user_cookie_name))
            return false;

        $hash = Cookie::get($user_cookie_name);
    }

    private function logout()
    {
        $this->session->remove("user");
        $this->_current_user = false;
    }

    public function getCurrentUser()
    {
        if (!isset($this->_current_user) && $this->session->has("user")) {
            $user = $this->session->get("user");
            $this->_current_user = $this->findOne([
                'uuid' => "uuid"
            ]);
        }

        if (!$this->_current_user)
            $this->fromCookie();

        if ($this->_current_user && $this->_current_user->blocked) {
            $this->logout();
        }

        return $this->_current_user;
    }
}
