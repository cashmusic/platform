<?php
namespace Kahlan\Reporter\Coverage\Exporter;

use DOMDocument;
use RuntimeException;

class Clover
{
    /**
     * Writes a coverage to an ouput file.
     *
     * @param  array   $options The option where the possible values are:
     *                          -`'file'` _string_: The output file name.
     * @return boolean
     */
    public static function write($options)
    {
        $defaults = [
            'file' => null
        ];
        $options += $defaults;

        if (!$options['file']) {
            throw new RuntimeException("Missing file name");
        }

        return file_put_contents($options['file'], static::export($options));
    }

    /**
     * Exports a coverage to a string.
     *
     * @param  array   $options The option array where the possible values are:
     *                          -`'collector'` _object_ : The collector instance.
     *                          -`'time'`      _integer_: The name of the service.
     * @return string
     */
    public static function export($options)
    {
        $defaults = [
            'collector' => null,
            'time'      => time(),
            'base_path' => getcwd()
        ];
        $options += $defaults;

        $collector = $options['collector'];

        $xmlDocument = new DOMDocument('1.0', 'UTF-8');
        $xmlDocument->formatOutput = true;

        $xmlCoverage = $xmlDocument->createElement('coverage');
        $xmlCoverage->setAttribute('generated', $options['time']);
        $xmlDocument->appendChild($xmlCoverage);

        $xmlProject = $xmlDocument->createElement('project');
        $xmlProject->setAttribute('timestamp', $options['time']);
        $xmlCoverage->appendChild($xmlProject);

        $base = $options['base_path'] ? rtrim($options['base_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : '';

        foreach ($collector->export() as $file => $data) {
            $xmlProject->appendChild(static::_exportFile($xmlDocument, $base . $file, $data));
        }
        $xmlProject->appendChild(static::_exportMetrics($xmlDocument, $collector->metrics()));
        return $xmlDocument->saveXML();
    }

    /**
     * Export the coverage of a file.
     *
     * @param  array   $options The option array where the possible values are:
     *                          -`'coverage'` The coverage instance.
     * @return object           The XML file node.
     */
    protected static function _exportFile($xmlDocument, $file, $data)
    {
        $xmlFile = $xmlDocument->createElement('file');
        $xmlFile->setAttribute('name', $file);
        foreach ($data as $line => $node) {
            $xmlLine = $xmlDocument->createElement('line');
            $xmlLine->setAttribute('num', $line + 1);
            $xmlLine->setAttribute('type', 'stmt');
            $xmlLine->setAttribute('count', $data[$line]);
            $xmlFile->appendChild($xmlLine);
        }
        return $xmlFile;
    }

    /**
     * Export the coverage of a metrics.
     *
     * @param  object $xmlDocument The DOMDocument root node instance.
     * @return object              The XML file node.
     */
    protected static function _exportMetrics($xmlDocument, $metrics)
    {
        $data = $metrics->data();
        $xmlMetrics = $xmlDocument->createElement('metrics');
        $xmlMetrics->setAttribute('loc', $data['loc']);
        $xmlMetrics->setAttribute('ncloc', $data['nlloc']);
        $xmlMetrics->setAttribute('statements', $data['lloc']);
        $xmlMetrics->setAttribute('coveredstatements', $data['cloc']);
        return $xmlMetrics;
    }
}
