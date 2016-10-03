<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Phone
 *
 * Representation of a phone number.
 *
 * @package PayPal\Api
 *
 * @property string country_code
 * @property string national_number
 * @property string extension
 */
class Phone extends PayPalModel
{
    /**
     * The country calling code (CC) as defined by E.164. The combined length of CC+national cannot be more than 15 digits. 
     *
     * @param string $country_code
     * 
     * @return $this
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * The country calling code (CC) as defined by E.164. The combined length of CC+national cannot be more than 15 digits. 
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * The national number as defined by E.164. The combined length of CC+national cannot be more than 15 digits. A national number consists of National Destination Code (NDC) and Subscriber Number (SN).
     *
     * @param string $national_number
     * 
     * @return $this
     */
    public function setNationalNumber($national_number)
    {
        $this->national_number = $national_number;
        return $this;
    }

    /**
     * The national number as defined by E.164. The combined length of CC+national cannot be more than 15 digits. A national number consists of National Destination Code (NDC) and Subscriber Number (SN).
     *
     * @return string
     */
    public function getNationalNumber()
    {
        return $this->national_number;
    }

    /**
     * Phone extension
     *
     * @param string $extension
     *
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Phone extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

}
