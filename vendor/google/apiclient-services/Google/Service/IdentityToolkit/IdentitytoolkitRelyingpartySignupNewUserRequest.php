<?php
/*
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

class Google_Service_IdentityToolkit_IdentitytoolkitRelyingpartySignupNewUserRequest extends Google_Model
{
  public $captchaChallenge;
  public $captchaResponse;
  public $displayName;
  public $email;
  public $idToken;
  public $instanceId;
  public $password;

  public function setCaptchaChallenge($captchaChallenge)
  {
    $this->captchaChallenge = $captchaChallenge;
  }
  public function getCaptchaChallenge()
  {
    return $this->captchaChallenge;
  }
  public function setCaptchaResponse($captchaResponse)
  {
    $this->captchaResponse = $captchaResponse;
  }
  public function getCaptchaResponse()
  {
    return $this->captchaResponse;
  }
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function getEmail()
  {
    return $this->email;
  }
  public function setIdToken($idToken)
  {
    $this->idToken = $idToken;
  }
  public function getIdToken()
  {
    return $this->idToken;
  }
  public function setInstanceId($instanceId)
  {
    $this->instanceId = $instanceId;
  }
  public function getInstanceId()
  {
    return $this->instanceId;
  }
  public function setPassword($password)
  {
    $this->password = $password;
  }
  public function getPassword()
  {
    return $this->password;
  }
}
