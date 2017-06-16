<?php

namespace EddTurtle\DirectUpload;

/**
 * Class Signature
 *
 * Build an AWS Signature, ready for direct upload. This will support AWS's signature v4 so should be
 * accepted by all regions.
 *
 * @package EddTurtle\DirectUpload
 */
class Signature
{

    CONST ALGORITHM = "AWS4-HMAC-SHA256";
    CONST SERVICE = "s3";
    CONST REQUEST_TYPE = "aws4_request";

    /**
     * Default options, these can be overwritten within the constructor.
     *
     * @var array
     */
    protected $options = [

        // If the upload is a success, this is the http code we get back from S3.
        // By default this will be a 201 Created.
        'success_status' => 201,

        // If the file should be private/public-read/public-write.
        // This is file specific, not bucket. More info: http://amzn.to/1SSOgwO
        'acl' => 'private',

        // The file's name on s3, can be set with JS by changing the input[name="key"].
        // ${filename} will just mean the original filename of the file being uploaded.
        'default_filename' => '${filename}',

        // The maximum file size of an upload in MB. Will refuse with a EntityTooLarge
        // and 400 Bad Request if you exceed this limit.
        'max_file_size' => 500,

        // Request expiration time, specified in relative time format or in seconds.
        // minimum of 1 (+1 second), maximum of 604800 (+7 days)
        'expires' => '+6 hours',

        // Server will check that the filename starts with this prefix and fail
        // with a AccessDenied 403 if not.
        'valid_prefix' => '',

        // Strictly only allow a single content type, blank will allow all. Will fail
        // with a AccessDenied 403 is this condition is not met.
        'content_type' => '',

        // Sets whether AWS server side encryption should be applied to the uploaded files,
        // so that files will be encrypted with AES256 when at rest.
        'encryption' => false,

        // Allow S3 compatible solutions by specifying the domain it should POST to. Must be
        // a valid url (inc. http/https) otherwise will throw InvalidOptionException.
        'custom_url' => null,

        // Any additional inputs to add to the form. This is an array of name => value
        // pairs e.g. ['Content-Disposition' => 'attachment']
        'additional_inputs' => []

    ];

    /**
     * @var string the AWS Key
     */
    private $key;

    /**
     * @var string the AWS Secret
     */
    private $secret;

    /**
     * @var string
     */
    private $bucket;

    /**
     * @var Region
     */
    private $region;

    /**
     * @var int the current unix timestamp
     */
    private $time = null;

    private $credentials = null;
    private $base64Policy = null;
    private $signature = null;

    /**
     * Signature constructor.
     *
     * @param string $key     the AWS API Key to use.
     * @param string $secret  the AWS API Secret to use.
     * @param string $bucket  the bucket to upload the file into.
     * @param string $region  the s3 region this bucket is within. More info: http://amzn.to/1FtPG6r
     * @param array  $options any additional options, like acl and success status.
     */
    public function __construct($key, $secret, $bucket, $region = "us-east-1", $options = [])
    {
        $this->setAwsCredentials($key, $secret);
        $this->populateTime();

        $this->bucket = $bucket;
        $this->region = new Region($region);

        $this->setOptions($options);
    }

    /**
     * Set the AWS Credentials
     *
     * @param string $key    the AWS API Key to use.
     * @param string $secret the AWS API Secret to use.
     */
    protected function setAwsCredentials($key, $secret)
    {
        // Key
        if (empty($key) || $key === "YOUR_S3_KEY") {
            throw new \InvalidArgumentException("Invalid AWS Key");
        }
        $this->key = $key;

        // Secret
        if (empty($secret) || $secret === "YOUR_S3_SECRET") {
            throw new \InvalidArgumentException("Invalid AWS Secret");
        }
        $this->secret = $secret;
    }

    /**
     * Build the form url for sending files, this will include the bucket and the region name.
     *
     * @return string the s3 bucket's url.
     */
    public function getFormUrl()
    {
        if (!is_null($this->options['custom_url'])) {
            return $this->buildCustomUrl();
        } else {
            return $this->buildAmazonUrl();
        }
    }

    private function buildCustomUrl()
    {
        $url = trim($this->options['custom_url']);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidOptionException("The custom_url option you have specified is invalid");
        }

        $separator = (substr($url, -1) === "/" ? "" : "/");

        return $url . $separator . urlencode($this->bucket);
    }

    private function buildAmazonUrl()
    {
        $region = (string)$this->region;

        // Only the us-east-1 region is exempt from needing the region in the url.
        if ($region !== "us-east-1") {
            $middle = "-" . $region;
        } else {
            $middle = "";
        }

        return "//" . self::SERVICE . $middle . ".amazonaws.com" . "/" . urlencode($this->bucket);
    }

    /**
     * Get all options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set/overwrite any default options.
     *
     * @param array $options any options to override.
     */
    public function setOptions($options)
    {
        // Overwrite default options
        $this->options = $options + $this->options;

        $this->options['acl'] = new Acl($this->options['acl']);

        // Return HTTP code must be a string
        $this->options['success_status'] = (string)$this->options['success_status'];
    }

    /**
     * Get an AWS Signature V4 generated.
     *
     * @return string the aws v4 signature.
     */
    public function getSignature()
    {
        if (is_null($this->signature)) {
            $this->generateScope();
            $this->generatePolicy();
            $this->generateSignature();
        }
        return $this->signature;
    }

    /**
     * Generate the necessary hidden inputs to go within the form. These inputs should match what's being send in
     * the policy.
     *
     * @param bool $addKey whether to add the 'key' input (filename), defaults to yes.
     *
     * @return array of the form inputs.
     */
    public function getFormInputs($addKey = true)
    {
        $this->getSignature();

        $inputs = [
            'Content-Type' => $this->options['content_type'],
            'acl' => (string)$this->options['acl'],
            'success_action_status' => $this->options['success_status'],
            'policy' => $this->base64Policy,
            'X-amz-credential' => $this->credentials,
            'X-amz-algorithm' => self::ALGORITHM,
            'X-amz-date' => $this->getFullDateFormat(),
            'X-amz-signature' => $this->signature
        ];

        if ($this->options['encryption']) {
            $inputs['X-amz-server-side-encryption'] = 'AES256';
        }

        $inputs = array_merge($inputs, $this->options['additional_inputs']);

        if ($addKey) {
            // Note: The Key (filename) will need to be populated with JS on upload
            // if anything other than the filename is wanted.
            $inputs['key'] = $this->options['valid_prefix'] . $this->options['default_filename'];
        }

        return $inputs;
    }

    /**
     * Based on getFormInputs(), this will build up the html to go within the form.
     *
     * @param bool $addKey whether to add the 'key' input (filename), defaults to yes.
     *
     * @return string html of hidden form inputs.
     */
    public function getFormInputsAsHtml($addKey = true)
    {
        $inputs = [];
        foreach ($this->getFormInputs($addKey) as $name => $value) {
            $inputs[] = '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }
        return implode(PHP_EOL, $inputs);
    }


    // Where the magic begins ;)

    /**
     * Step 1: Generate the Scope
     */
    protected function generateScope()
    {
        $scope = [
            $this->key,
            $this->getShortDateFormat(),
            $this->region,
            self::SERVICE,
            self::REQUEST_TYPE
        ];
        $this->credentials = implode('/', $scope);
    }

    /**
     * Step 2: Generate a Base64 Policy
     */
    protected function generatePolicy()
    {
        $policy = [
            'expiration' => $this->getExpirationDate(),
            'conditions' => [
                ['bucket' => $this->bucket],
                ['acl' => (string)$this->options['acl']],
                ['starts-with', '$key', $this->options['valid_prefix']],
                $this->getPolicyContentTypeArray(),
                ['content-length-range', 0, $this->mbToBytes($this->options['max_file_size'])],
                ['success_action_status' => $this->options['success_status']],
                ['x-amz-credential' => $this->credentials],
                ['x-amz-algorithm' => self::ALGORITHM],
                ['x-amz-date' => $this->getFullDateFormat()]
            ]
        ];
        $policy = $this->addAdditionalInputs($policy);
        $this->base64Policy = base64_encode(json_encode($policy));
    }

    private function getPolicyContentTypeArray()
    {
        $contentTypePrefix = (empty($this->options['content_type']) ? 'starts-with' : 'eq');
        return [
            $contentTypePrefix,
            '$Content-Type',
            $this->options['content_type']
        ];
    }

    private function addAdditionalInputs($policy)
    {
        foreach ($this->options['additional_inputs'] as $name => $value) {
            $policy['conditions'][] = ['starts-with', '$' . $name, $value];
        }
        return $policy;
    }

    /**
     * Step 3: Generate and sign the Signature (v4)
     */
    protected function generateSignature()
    {
        $signatureData = [
            $this->getShortDateFormat(),
            (string)$this->region,
            self::SERVICE,
            self::REQUEST_TYPE
        ];

        // Iterates over the data (defined in the array above), hashing it each time.
        $initial = 'AWS4' . $this->secret;
        $signingKey = array_reduce($signatureData, function($key, $data) {
            return $this->keyHash($data, $key);
        }, $initial);

        // Finally, use the signing key to hash the policy.
        $this->signature = $this->keyHash($this->base64Policy, $signingKey, false);
    }


    // Helper functions

    private function keyHash($date, $key, $raw = true)
    {
        return hash_hmac('sha256', $date, $key, $raw);
    }

    private function populateTime()
    {
        if (is_null($this->time)) {
            $this->time = time();
        }
    }

    private function mbToBytes($megaByte)
    {
        if (is_numeric($megaByte)) {
            return $megaByte * pow(1024, 2);
        }
        return 0;
    }


    // Dates

    private function getShortDateFormat()
    {
        return gmdate("Ymd", $this->time);
    }

    private function getFullDateFormat()
    {
        return gmdate("Ymd\THis\Z", $this->time);
    }

    private function getExpirationDate()
    {
        // Note: using \DateTime::ISO8601 doesn't work :(

        $exp = strtotime($this->options['expires'], $this->time);
        $diff = $exp - $this->time;

        if (!($diff >= 1 && $diff <= 604800)) {
            throw new \InvalidArgumentException("Expiry must be between 1 and 604800");
        }

        return gmdate('Y-m-d\TG:i:s\Z', $exp);
    }


}
