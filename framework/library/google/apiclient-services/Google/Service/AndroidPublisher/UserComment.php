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

class Google_Service_AndroidPublisher_UserComment extends Google_Model
{
  public $androidOsVersion;
  public $appVersionCode;
  public $appVersionName;
  public $device;
  protected $lastModifiedType = 'Google_Service_AndroidPublisher_Timestamp';
  protected $lastModifiedDataType = '';
  public $reviewerLanguage;
  public $starRating;
  public $text;

  public function setAndroidOsVersion($androidOsVersion)
  {
    $this->androidOsVersion = $androidOsVersion;
  }
  public function getAndroidOsVersion()
  {
    return $this->androidOsVersion;
  }
  public function setAppVersionCode($appVersionCode)
  {
    $this->appVersionCode = $appVersionCode;
  }
  public function getAppVersionCode()
  {
    return $this->appVersionCode;
  }
  public function setAppVersionName($appVersionName)
  {
    $this->appVersionName = $appVersionName;
  }
  public function getAppVersionName()
  {
    return $this->appVersionName;
  }
  public function setDevice($device)
  {
    $this->device = $device;
  }
  public function getDevice()
  {
    return $this->device;
  }
  public function setLastModified(Google_Service_AndroidPublisher_Timestamp $lastModified)
  {
    $this->lastModified = $lastModified;
  }
  public function getLastModified()
  {
    return $this->lastModified;
  }
  public function setReviewerLanguage($reviewerLanguage)
  {
    $this->reviewerLanguage = $reviewerLanguage;
  }
  public function getReviewerLanguage()
  {
    return $this->reviewerLanguage;
  }
  public function setStarRating($starRating)
  {
    $this->starRating = $starRating;
  }
  public function getStarRating()
  {
    return $this->starRating;
  }
  public function setText($text)
  {
    $this->text = $text;
  }
  public function getText()
  {
    return $this->text;
  }
}
