<?php
/*
 * Copyright 2014 Google Inc.
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

class Google_Service_Container_IPAllocationPolicy extends Google_Model
{
  public $clusterIpv4Cidr;
  public $createSubnetwork;
  public $nodeIpv4Cidr;
  public $servicesIpv4Cidr;
  public $subnetworkName;
  public $useIpAliases;

  public function setClusterIpv4Cidr($clusterIpv4Cidr)
  {
    $this->clusterIpv4Cidr = $clusterIpv4Cidr;
  }
  public function getClusterIpv4Cidr()
  {
    return $this->clusterIpv4Cidr;
  }
  public function setCreateSubnetwork($createSubnetwork)
  {
    $this->createSubnetwork = $createSubnetwork;
  }
  public function getCreateSubnetwork()
  {
    return $this->createSubnetwork;
  }
  public function setNodeIpv4Cidr($nodeIpv4Cidr)
  {
    $this->nodeIpv4Cidr = $nodeIpv4Cidr;
  }
  public function getNodeIpv4Cidr()
  {
    return $this->nodeIpv4Cidr;
  }
  public function setServicesIpv4Cidr($servicesIpv4Cidr)
  {
    $this->servicesIpv4Cidr = $servicesIpv4Cidr;
  }
  public function getServicesIpv4Cidr()
  {
    return $this->servicesIpv4Cidr;
  }
  public function setSubnetworkName($subnetworkName)
  {
    $this->subnetworkName = $subnetworkName;
  }
  public function getSubnetworkName()
  {
    return $this->subnetworkName;
  }
  public function setUseIpAliases($useIpAliases)
  {
    $this->useIpAliases = $useIpAliases;
  }
  public function getUseIpAliases()
  {
    return $this->useIpAliases;
  }
}
