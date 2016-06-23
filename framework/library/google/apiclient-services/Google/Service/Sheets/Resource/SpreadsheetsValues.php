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
 * The "values" collection of methods.
 * Typical usage is:
 *  <code>
 *   $sheetsService = new Google_Service_Sheets(...);
 *   $values = $sheetsService->values;
 *  </code>
 */
class Google_Service_Sheets_Resource_SpreadsheetsValues extends Google_Service_Resource
{
  /**
   * Returns one or more ranges of values from a spreadsheet. The caller must
   * specify the spreadsheet ID and one or more ranges. (values.batchGet)
   *
   * @param string $spreadsheetId The ID of the spreadsheet to retrieve data from.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string ranges The A1 notation of the values to retrieve.
   * @opt_param string valueRenderOption How values should be represented in the
   * output.
   * @opt_param string dateTimeRenderOption How dates, times, and durations should
   * be represented in the output. This is ignored if value_render_option is
   * FORMATTED_VALUE.
   * @opt_param string majorDimension The major dimension that results should use.
   *
   * For example, if the spreadsheet data is: `A1=1,B1=2,A2=3,B2=4`, then
   * requesting `range=A1:B2,majorDimension=ROWS` will return `[[1,2],[3,4]]`,
   * whereas requesting `range=A1:B2,majorDimension=COLUMNS` will return
   * `[[1,3],[2,4]]`.
   * @return Google_Service_Sheets_BatchGetValuesResponse
   */
  public function batchGet($spreadsheetId, $optParams = array())
  {
    $params = array('spreadsheetId' => $spreadsheetId);
    $params = array_merge($params, $optParams);
    return $this->call('batchGet', array($params), "Google_Service_Sheets_BatchGetValuesResponse");
  }
  /**
   * Sets values in one or more ranges of a spreadsheet. The caller must specify
   * the spreadsheet ID, a valueInputOption, and one or more ValueRanges.
   * (values.batchUpdate)
   *
   * @param string $spreadsheetId The ID of the spreadsheet to update.
   * @param Google_Service_Sheets_BatchUpdateValuesRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Sheets_BatchUpdateValuesResponse
   */
  public function batchUpdate($spreadsheetId, Google_Service_Sheets_BatchUpdateValuesRequest $postBody, $optParams = array())
  {
    $params = array('spreadsheetId' => $spreadsheetId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('batchUpdate', array($params), "Google_Service_Sheets_BatchUpdateValuesResponse");
  }
  /**
   * Returns a range of values from a spreadsheet. The caller must specify the
   * spreadsheet ID and a range. (values.get)
   *
   * @param string $spreadsheetId The ID of the spreadsheet to retrieve data from.
   * @param string $range The A1 notation of the values to retrieve.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string valueRenderOption How values should be represented in the
   * output.
   * @opt_param string dateTimeRenderOption How dates, times, and durations should
   * be represented in the output. This is ignored if value_render_option is
   * FORMATTED_VALUE.
   * @opt_param string majorDimension The major dimension that results should use.
   *
   * For example, if the spreadsheet data is: `A1=1,B1=2,A2=3,B2=4`, then
   * requesting `range=A1:B2,majorDimension=ROWS` will return `[[1,2],[3,4]]`,
   * whereas requesting `range=A1:B2,majorDimension=COLUMNS` will return
   * `[[1,3],[2,4]]`.
   * @return Google_Service_Sheets_ValueRange
   */
  public function get($spreadsheetId, $range, $optParams = array())
  {
    $params = array('spreadsheetId' => $spreadsheetId, 'range' => $range);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Sheets_ValueRange");
  }
  /**
   * Sets values in a range of a spreadsheet. The caller must specify the
   * spreadsheet ID, range, and a valueInputOption. (values.update)
   *
   * @param string $spreadsheetId The ID of the spreadsheet to update.
   * @param string $range The A1 notation of the values to update.
   * @param Google_Service_Sheets_ValueRange $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string valueInputOption How the input data should be interpreted.
   * @return Google_Service_Sheets_UpdateValuesResponse
   */
  public function update($spreadsheetId, $range, Google_Service_Sheets_ValueRange $postBody, $optParams = array())
  {
    $params = array('spreadsheetId' => $spreadsheetId, 'range' => $range, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Sheets_UpdateValuesResponse");
  }
}
