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

class Google_Service_Appengine_NetworkUtilization extends Google_Model
{
  public $targetReceivedBytesPerSec;
  public $targetReceivedPacketsPerSec;
  public $targetSentBytesPerSec;
  public $targetSentPacketsPerSec;

  public function setTargetReceivedBytesPerSec($targetReceivedBytesPerSec)
  {
    $this->targetReceivedBytesPerSec = $targetReceivedBytesPerSec;
  }
  public function getTargetReceivedBytesPerSec()
  {
    return $this->targetReceivedBytesPerSec;
  }
  public function setTargetReceivedPacketsPerSec($targetReceivedPacketsPerSec)
  {
    $this->targetReceivedPacketsPerSec = $targetReceivedPacketsPerSec;
  }
  public function getTargetReceivedPacketsPerSec()
  {
    return $this->targetReceivedPacketsPerSec;
  }
  public function setTargetSentBytesPerSec($targetSentBytesPerSec)
  {
    $this->targetSentBytesPerSec = $targetSentBytesPerSec;
  }
  public function getTargetSentBytesPerSec()
  {
    return $this->targetSentBytesPerSec;
  }
  public function setTargetSentPacketsPerSec($targetSentPacketsPerSec)
  {
    $this->targetSentPacketsPerSec = $targetSentPacketsPerSec;
  }
  public function getTargetSentPacketsPerSec()
  {
    return $this->targetSentPacketsPerSec;
  }
}
