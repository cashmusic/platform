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

class Google_Service_Container_ClusterUpdate extends Google_Model
{
  protected $desiredAddonsConfigType = 'Google_Service_Container_AddonsConfig';
  protected $desiredAddonsConfigDataType = '';
  public $desiredMasterVersion;
  public $desiredMonitoringService;
  public $desiredNodePoolId;
  public $desiredNodeVersion;

  public function setDesiredAddonsConfig(Google_Service_Container_AddonsConfig $desiredAddonsConfig)
  {
    $this->desiredAddonsConfig = $desiredAddonsConfig;
  }
  public function getDesiredAddonsConfig()
  {
    return $this->desiredAddonsConfig;
  }
  public function setDesiredMasterVersion($desiredMasterVersion)
  {
    $this->desiredMasterVersion = $desiredMasterVersion;
  }
  public function getDesiredMasterVersion()
  {
    return $this->desiredMasterVersion;
  }
  public function setDesiredMonitoringService($desiredMonitoringService)
  {
    $this->desiredMonitoringService = $desiredMonitoringService;
  }
  public function getDesiredMonitoringService()
  {
    return $this->desiredMonitoringService;
  }
  public function setDesiredNodePoolId($desiredNodePoolId)
  {
    $this->desiredNodePoolId = $desiredNodePoolId;
  }
  public function getDesiredNodePoolId()
  {
    return $this->desiredNodePoolId;
  }
  public function setDesiredNodeVersion($desiredNodeVersion)
  {
    $this->desiredNodeVersion = $desiredNodeVersion;
  }
  public function getDesiredNodeVersion()
  {
    return $this->desiredNodeVersion;
  }
}
