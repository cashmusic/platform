<?php

namespace EddTurtle\DirectUpload;

/**
 * Class Region
 *
 * Region signifies an AWS Region, created and identified by it's hyphenated name.
 * More info on these can be found at: http://amzn.to/1FtPG6r
 *
 * @package EddTurtle\DirectUpload
 */
class Region
{

    private $possibleOptions = [
        "ap-northeast-1",
        "ap-northeast-2",
        "ap-south-1",
        "ap-southeast-1",
        "ap-southeast-2",
        "ca-central-1",
        "eu-central-1",
        "eu-west-1",
        "eu-west-2",
        "sa-east-1",
        "us-east-1",
        "us-east-2",
        "us-west-1",
        "us-west-2",
    ];

    /**
     * @var string
     */
    private $name;

    public function __construct($region)
    {
        $this->setName($region);
    }

    /**
     * @return string the aws region.
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param string $region the aws region.
     *
     * @throws InvalidRegionException
     */
    public function setName($region)
    {
        $region = strtolower($region);
        if (in_array($region, $this->possibleOptions)) {
            $this->name = $region;
        } else {
            throw new InvalidRegionException;
        }
    }

    /**
     * @return string the aws region.
     */
    public function getName()
    {
        return $this->name;
    }

}
