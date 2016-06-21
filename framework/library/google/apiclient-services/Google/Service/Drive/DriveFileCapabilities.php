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

class Google_Service_Drive_DriveFileCapabilities extends Google_Model
{
  public $canComment;
  public $canCopy;
  public $canEdit;
  public $canReadRevisions;
  public $canShare;

  public function setCanComment($canComment)
  {
    $this->canComment = $canComment;
  }
  public function getCanComment()
  {
    return $this->canComment;
  }
  public function setCanCopy($canCopy)
  {
    $this->canCopy = $canCopy;
  }
  public function getCanCopy()
  {
    return $this->canCopy;
  }
  public function setCanEdit($canEdit)
  {
    $this->canEdit = $canEdit;
  }
  public function getCanEdit()
  {
    return $this->canEdit;
  }
  public function setCanReadRevisions($canReadRevisions)
  {
    $this->canReadRevisions = $canReadRevisions;
  }
  public function getCanReadRevisions()
  {
    return $this->canReadRevisions;
  }
  public function setCanShare($canShare)
  {
    $this->canShare = $canShare;
  }
  public function getCanShare()
  {
    return $this->canShare;
  }
}
