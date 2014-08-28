<?php
/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 26.08.14 at 12:19
 */

namespace samson\social\odnoklassniki;

class Odnoklassniki extends \samson\social\network\Network
{
    public $id = 'odnoklassniki';

    public $dbIdField = 'ok_id';

    public $baseURL = 'http://www.odnoklassniki.ru/';

    public $socialURL = 'http://www.odnoklassniki.ru/oauth/authorize';

    public $tokenURL = 'http://api.odnoklassniki.ru/oauth/token.do';

    public $userURL = 'http://api.odnoklassniki.ru/fb.do';

    public $publicKey;

    public function __HANDLER()
    {
        parent::__HANDLER();

        // Send http get request to retrieve VK code
        $this->redirect($this->socialURL, array(
            'client_id'     => $this->appCode,
            'response_type' => 'code',
            'redirect_uri'  => $this->returnURL(),
        ));
    }

    public function __token()
    {
        $code = $_GET['code'];
        if (isset($code)) {

            // Send http get request to retrieve VK code
            $token = $this->post($this->tokenURL, array(
                'code' => $code,
                'redirect_uri' => $this->returnURL(),
                'grant_type' => 'uthorization_code',
                'client_id' => $this->appCode,
                'client_secret' => $this->appSecret,
            ));

            // take user's information using access token
            if (isset($token)) {
                $sig = md5('application_key=' . $this->publicKey . 'method=users.getCurrentUser' . md5($token['access_token'] . $this->appSecret));
                $userInfo = $this->get($this->userURL, array(
                    'method' => 'users.getCurrentUser',
                    'access_token' => $token['access_token'],
                    'application_key' => $this->publicKey,
                    'sig' => $sig,
                ));
                $this->setUser($userInfo);
            }

        }

        parent::__token();
    }

    protected function setUser(array $userData, & $user = null)
    {
        $user = new \samson\social\User();
        $user->birthday = $userData['birthday'];
        $user->gender = $userData['gender'];
        $user->locale = $userData['locale'];
        $user->name = $userData['first_name'];
        $user->surname = $userData['last_name'];
        $user->socialID = $userData['uid'];
        $user->photo = $this->baseURL.$userData['uid'].'/picture';

        parent::setUser($userData, $user);
    }

}
