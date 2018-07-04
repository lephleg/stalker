<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facades\App\Libraries\Mailchimp;
use App\Libraries\Mailchimp as MailchimpLib;
use Illuminate\Validation\ValidationException;
use Validator;
use \Exception;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class MailchimpController
 * @package App\Http\Controllers
 *
 * Accepts requests that will be forwarded to MailChimp v3.0 API,
 * using a custom App\Libraries\Mailchimp wrapper
 *
 * You can use this controller with a predefined API key in the config file
 * or pass yours on every request on-demand
 *
 */
class MailchimpController extends Controller
{

    /**
     * Get the lists index for your MailChimp account
     * @param Request $request
     * @return mixed
     */
    public function getLists(Request $request)
    {

        try {
            // if the request doesn't include an api key,
            // use real-time facade to access Mailchimp wrapper
            if (!$request->has('api_key')) {
                $response = Mailchimp::request('get', 'lists');
            } else { // else use the wrapper directly
                $chimp = new MailchimpLib($request->api_key);
                $response = $chimp->request('get', 'lists');
            }
        } catch (GuzzleException $e) {
            return $this->returnErrorJson($e->getMessage(),500, $e);
        } catch (Exception $e) {
            return $this->returnErrorJson($e->getMessage(),400, $e);
        }

        return json_encode($response);
    }

    /**
     * Subscribe a new member to a newsletter list with the minimal set of options
     * @param Request $request
     * @return mixed
     */
    public function subscribe(Request $request)
    {

        // validate request payload
        $validator = Validator::make($request->all(), [
            'email_address' => 'required|email',
            'list_id' => 'required',
            'api_key' => 'string'
        ]);

        if ($validator->fails()) {
            return $this->returnErrorJson($validator->errors()->getMessageBag(),400);
        }

        // form member array
        $member = [
            'email_address' => $request->email_address,
            'status' => 'subscribed'
        ];

        try {
            // if the request doesn't include an api key,
            // use real-time facade to access Mailchimp wrapper
            if (!$request->has('api_key')) {
                $response = Mailchimp::subscribe($request->list_id, $member);
            } else { // else use the wrapper directly
                $chimp = new MailchimpLib($request->api_key);
                $response = $chimp->subscribe($request->list_id, $member);
            }
        } catch (GuzzleException $e) {
            return $this->returnErrorJson($e->getMessage(),500, $e);
        } catch (Exception $e) {
            return $this->returnErrorJson($e->getMessage(),400, $e);
        }

        // return only the id as JSON
        return ['id' => $response->id];

    }

}
