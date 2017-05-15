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

/**
 * Service definition for AndroidEnterprise (v1).
 *
 * <p>
 * Manages the deployment of apps to Android for Work users.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/android/work/play/emm-api" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Google_Service_AndroidEnterprise extends Google_Service
{
  /** Manage corporate Android devices. */
  const ANDROIDENTERPRISE =
      "https://www.googleapis.com/auth/androidenterprise";

  public $collections;
  public $collectionviewers;
  public $devices;
  public $enterprises;
  public $entitlements;
  public $grouplicenses;
  public $grouplicenseusers;
  public $installs;
  public $permissions;
  public $products;
  public $storelayoutclusters;
  public $storelayoutpages;
  public $users;
  
  /**
   * Constructs the internal representation of the AndroidEnterprise service.
   *
   * @param Google_Client $client
   */
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'androidenterprise/v1/';
    $this->version = 'v1';
    $this->serviceName = 'androidenterprise';

    $this->collections = new Google_Service_AndroidEnterprise_Resource_Collections(
        $this,
        $this->serviceName,
        'collections',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'enterprises/{enterpriseId}/collections',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/collections',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->collectionviewers = new Google_Service_AndroidEnterprise_Resource_Collectionviewers(
        $this,
        $this->serviceName,
        'collectionviewers',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->devices = new Google_Service_AndroidEnterprise_Resource_Devices(
        $this,
        $this->serviceName,
        'devices',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getState' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/state',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setState' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/state',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->enterprises = new Google_Service_AndroidEnterprise_Resource_Enterprises(
        $this,
        $this->serviceName,
        'enterprises',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'enroll' => array(
              'path' => 'enterprises/enroll',
              'httpMethod' => 'POST',
              'parameters' => array(
                'token' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getStoreLayout' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'enterprises',
              'httpMethod' => 'POST',
              'parameters' => array(
                'token' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises',
              'httpMethod' => 'GET',
              'parameters' => array(
                'domain' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'sendTestPushNotification' => array(
              'path' => 'enterprises/{enterpriseId}/sendTestPushNotification',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setAccount' => array(
              'path' => 'enterprises/{enterpriseId}/account',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setStoreLayout' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'unenroll' => array(
              'path' => 'enterprises/{enterpriseId}/unenroll',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->entitlements = new Google_Service_AndroidEnterprise_Resource_Entitlements(
        $this,
        $this->serviceName,
        'entitlements',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'install' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'install' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->grouplicenses = new Google_Service_AndroidEnterprise_Resource_Grouplicenses(
        $this,
        $this->serviceName,
        'grouplicenses',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'enterprises/{enterpriseId}/groupLicenses/{groupLicenseId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'groupLicenseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/groupLicenses',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->grouplicenseusers = new Google_Service_AndroidEnterprise_Resource_Grouplicenseusers(
        $this,
        $this->serviceName,
        'grouplicenseusers',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'enterprises/{enterpriseId}/groupLicenses/{groupLicenseId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'groupLicenseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->installs = new Google_Service_AndroidEnterprise_Resource_Installs(
        $this,
        $this->serviceName,
        'installs',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->permissions = new Google_Service_AndroidEnterprise_Resource_Permissions(
        $this,
        $this->serviceName,
        'permissions',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'permissions/{permissionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'permissionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->products = new Google_Service_AndroidEnterprise_Resource_Products(
        $this,
        $this->serviceName,
        'products',
        array(
          'methods' => array(
            'approve' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/approve',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'generateApprovalUrl' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/generateApprovalUrl',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'languageCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getAppRestrictionsSchema' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/appRestrictionsSchema',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getPermissions' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/permissions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/products',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'approved' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'language' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'query' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'token' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'updatePermissions' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/permissions',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->storelayoutclusters = new Google_Service_AndroidEnterprise_Resource_Storelayoutclusters(
        $this,
        $this->serviceName,
        'storelayoutclusters',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}/clusters/{clusterId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'clusterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}/clusters/{clusterId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'clusterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}/clusters',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}/clusters',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}/clusters/{clusterId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'clusterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}/clusters/{clusterId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'clusterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->storelayoutpages = new Google_Service_AndroidEnterprise_Resource_Storelayoutpages(
        $this,
        $this->serviceName,
        'storelayoutpages',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/storeLayout/pages/{pageId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->users = new Google_Service_AndroidEnterprise_Resource_Users(
        $this,
        $this->serviceName,
        'users',
        array(
          'methods' => array(
            'generateToken' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/token',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getAvailableProductSet' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/availableProductSet',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'email' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'revokeToken' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/token',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setAvailableProductSet' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/availableProductSet',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}
