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
 * Service definition for Monitoring (v3).
 *
 * <p>
 * Manages your Stackdriver monitoring data and configurations. Projects must be
 * associated with a Stackdriver account, except for the following methods: [mon
 * itoredResourceDescriptors.list](v3/projects.monitoredResourceDescriptors/list
 * ), [monitoredResourceDescriptors.get](v3/projects.monitoredResourceDescriptor
 * s/get), [metricDescriptors.list](v3/projects.metricDescriptors/list),
 * [metricDescriptors.get](v3/projects.metricDescriptors/get), and
 * [timeSeries.list](v3/projects.timeSeries/list).</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://cloud.google.com/monitoring/api/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Google_Service_Monitoring extends Google_Service
{
  /** View and manage your data across Google Cloud Platform services. */
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";
  /** View and write monitoring data for all of your Google and third-party Cloud and API projects. */
  const MONITORING =
      "https://www.googleapis.com/auth/monitoring";
  /** View monitoring data for all of your Google Cloud and third-party projects. */
  const MONITORING_READ =
      "https://www.googleapis.com/auth/monitoring.read";
  /** Publish metric data to your Google Cloud projects. */
  const MONITORING_WRITE =
      "https://www.googleapis.com/auth/monitoring.write";

  public $projects_collectdTimeSeries;
  public $projects_groups;
  public $projects_groups_members;
  public $projects_metricDescriptors;
  public $projects_monitoredResourceDescriptors;
  public $projects_timeSeries;
  
  /**
   * Constructs the internal representation of the Monitoring service.
   *
   * @param Google_Client $client
   */
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://monitoring.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v3';
    $this->serviceName = 'monitoring';

    $this->projects_collectdTimeSeries = new Google_Service_Monitoring_Resource_ProjectsCollectdTimeSeries(
        $this,
        $this->serviceName,
        'collectdTimeSeries',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v3/{+name}/collectdTimeSeries',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->projects_groups = new Google_Service_Monitoring_Resource_ProjectsGroups(
        $this,
        $this->serviceName,
        'groups',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v3/{+name}/groups',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'validateOnly' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'delete' => array(
              'path' => 'v3/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v3/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v3/{+name}/groups',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'childrenOfGroup' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ancestorsOfGroup' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'descendantsOfGroup' => array(
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
            ),'update' => array(
              'path' => 'v3/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'validateOnly' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->projects_groups_members = new Google_Service_Monitoring_Resource_ProjectsGroupsMembers(
        $this,
        $this->serviceName,
        'members',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v3/{+name}/members',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
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
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'interval.endTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'interval.startTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->projects_metricDescriptors = new Google_Service_Monitoring_Resource_ProjectsMetricDescriptors(
        $this,
        $this->serviceName,
        'metricDescriptors',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v3/{+name}/metricDescriptors',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v3/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v3/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v3/{+name}/metricDescriptors',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
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
    $this->projects_monitoredResourceDescriptors = new Google_Service_Monitoring_Resource_ProjectsMonitoredResourceDescriptors(
        $this,
        $this->serviceName,
        'monitoredResourceDescriptors',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v3/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v3/{+name}/monitoredResourceDescriptors',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
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
    $this->projects_timeSeries = new Google_Service_Monitoring_Resource_ProjectsTimeSeries(
        $this,
        $this->serviceName,
        'timeSeries',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v3/{+name}/timeSeries',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v3/{+name}/timeSeries',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'interval.endTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'interval.startTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggregation.alignmentPeriod' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggregation.perSeriesAligner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggregation.crossSeriesReducer' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggregation.groupByFields' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
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
            ),
          )
        )
    );
  }
}
