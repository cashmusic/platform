<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 1/25/17
 * Time: 3:32 PM
 */

namespace Cashmusic\Elements\subscription;


class States
{
    protected $state;

    public function __construct($state, $user_id)
    {
        $this->state = $state;
        $this->user_id = $user_id;
    }

    public function router($callback) {
        if (!empty($this->state)) {

            $result = [
                'template' => 'default',
                'data' => []
            ];

            switch ($this->state) {
                case "success":
                    $result['template'] = "success";
                break;

                case "verified":
                    $result = $this->stateVerified();
                    break;

                case "validatelogin":
                    break;

                case "validate_login":
                    break;

                case "logged_in_index":
                    break;

                default:
                    return false;
            }

            $callback($result['template'], $result['data']);
        }
    }

    private function stateVerified() {
        $user_request = new \CASHRequest(
            array(
                'cash_request_type' => 'people',
                'cash_action' => 'getuser',
                'user_id' => $this->user_id
            )
        );

        $data['has_password'] = false;

        if ($user_request->response['payload']) {

            if ($user_request->response['payload']['is_admin']) {
                $data['has_password'] = true;
            }
        }

        return [
            'template' => 'settings',
            'data' => $data
        ];
    }
}