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
 * Service definition for Partners (v2).
 *
 * <p>
 * Lets advertisers search certified companies and create contact leads with
 * them, and also audits the usage of clients.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/partners/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Google_Service_Partners extends Google_Service
{


  public $clientMessages;
  public $companies;
  public $companies_leads;
  public $userEvents;
  public $userStates;
  
  /**
   * Constructs the internal representation of the Partners service.
   *
   * @param Google_Client $client
   */
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://partners.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v2';
    $this->serviceName = 'partners';

    $this->clientMessages = new Google_Service_Partners_Resource_ClientMessages(
        $this,
        $this->serviceName,
        'clientMessages',
        array(
          'methods' => array(
            'log' => array(
              'path' => 'v2/clientMessages:log',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->companies = new Google_Service_Partners_Resource_Companies(
        $this,
        $this->serviceName,
        'companies',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v2/companies/{companyId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'companyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'requestMetadata.userOverrides.ipAddress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.userOverrides.userId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.partnersSessionId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.experimentIds' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'requestMetadata.trafficSource.trafficSourceId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.trafficSource.trafficSubId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'currencyCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'address' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v2/companies',
              'httpMethod' => 'GET',
              'parameters' => array(
                'requestMetadata.userOverrides.ipAddress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.userOverrides.userId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.partnersSessionId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.experimentIds' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'requestMetadata.trafficSource.trafficSourceId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.trafficSource.trafficSubId' => array(
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
                'companyName' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'minMonthlyBudget.currencyCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'minMonthlyBudget.units' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'minMonthlyBudget.nanos' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'maxMonthlyBudget.currencyCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxMonthlyBudget.units' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxMonthlyBudget.nanos' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'industries' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'services' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'languageCodes' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'address' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'gpsMotivations' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'websiteUrl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->companies_leads = new Google_Service_Partners_Resource_CompaniesLeads(
        $this,
        $this->serviceName,
        'leads',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v2/companies/{companyId}/leads',
              'httpMethod' => 'POST',
              'parameters' => array(
                'companyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->userEvents = new Google_Service_Partners_Resource_UserEvents(
        $this,
        $this->serviceName,
        'userEvents',
        array(
          'methods' => array(
            'log' => array(
              'path' => 'v2/userEvents:log',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->userStates = new Google_Service_Partners_Resource_UserStates(
        $this,
        $this->serviceName,
        'userStates',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v2/userStates',
              'httpMethod' => 'GET',
              'parameters' => array(
                'requestMetadata.userOverrides.ipAddress' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.userOverrides.userId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.partnersSessionId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.experimentIds' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'requestMetadata.trafficSource.trafficSourceId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'requestMetadata.trafficSource.trafficSubId' => array(
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
