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

class Google_Service_Container_ServerConfig extends Google_Collection
{
  protected $collection_key = 'validNodeVersions';
  public $defaultClusterVersion;
  public $defaultImageFamily;
  public $validImageFamilies;
  public $validNodeVersions;

  public function setDefaultClusterVersion($defaultClusterVersion)
  {
    $this->defaultClusterVersion = $defaultClusterVersion;
  }
  public function getDefaultClusterVersion()
  {
    return $this->defaultClusterVersion;
  }
  public function setDefaultImageFamily($defaultImageFamily)
  {
    $this->defaultImageFamily = $defaultImageFamily;
  }
  public function getDefaultImageFamily()
  {
    return $this->defaultImageFamily;
  }
  public function setValidImageFamilies($validImageFamilies)
  {
    $this->validImageFamilies = $validImageFamilies;
  }
  public function getValidImageFamilies()
  {
    return $this->validImageFamilies;
  }
  public function setValidNodeVersions($validNodeVersions)
  {
    $this->validNodeVersions = $validNodeVersions;
  }
  public function getValidNodeVersions()
  {
    return $this->validNodeVersions;
  }
}
