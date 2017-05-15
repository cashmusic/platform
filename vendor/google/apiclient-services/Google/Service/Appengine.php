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
 * Service definition for Appengine (v1beta5).
 *
 * <p>
 * Provisions and manages App Engine applications.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://cloud.google.com/appengine/docs/admin-api/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Google_Service_Appengine extends Google_Service
{
  /** View and manage your data across Google Cloud Platform services. */
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";

  public $apps;
  public $apps_operations;
  public $apps_services;
  public $apps_services_versions;
  public $apps_services_versions_instances;
  
  /**
   * Constructs the internal representation of the Appengine service.
   *
   * @param Google_Client $client
   */
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://appengine.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1beta5';
    $this->serviceName = 'appengine';

    $this->apps = new Google_Service_Appengine_Resource_Apps(
        $this,
        $this->serviceName,
        'apps',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1beta5/apps/{appsId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ensureResourcesExist' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->apps_operations = new Google_Service_Appengine_Resource_AppsOperations(
        $this,
        $this->serviceName,
        'operations',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1beta5/apps/{appsId}/operations/{operationsId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'operationsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1beta5/apps/{appsId}/operations',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->apps_services = new Google_Service_Appengine_Resource_AppsServices(
        $this,
        $this->serviceName,
        'services',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1beta5/apps/{appsId}/services',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'mask' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'migrateTraffic' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->apps_services_versions = new Google_Service_Appengine_Resource_AppsServicesVersions(
        $this,
        $this->serviceName,
        'versions',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}/versions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}/versions/{versionsId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'versionsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}/versions/{versionsId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'versionsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}/versions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}/versions/{versionsId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'versionsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'mask' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->apps_services_versions_instances = new Google_Service_Appengine_Resource_AppsServicesVersionsInstances(
        $this,
        $this->serviceName,
        'instances',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1beta5/apps/{appsId}/services/{servicesId}/versions/{versionsId}/instances',
              'httpMethod' => 'GET',
              'parameters' => array(
                'appsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'servicesId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'versionsId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
  }
}
