<?php
namespace Kahlan\Reporter\Coverage\Exporter;

use RuntimeException;

class Coveralls
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

        if (!$file = $options['file']) {
            throw new RuntimeException("Missing file name");
        }
        unset($options['file']);
        return file_put_contents($file, static::export($options));
    }

    /**
     * Exports a coverage to a string.
     *
     * @param  array   $options The option array where the possible values are:
     *                          -`'collector'`      _object_ : The collector instance.
     *                          -`'service_name'`   _string_ : The name of the service.
     *                          -`'service_job_id'` _string_ : The job id of the service.
     *                          -`'repo_token'`     _string_ : The Coveralls repo token
     *                          -`'run_at'`         _integer_: The date of a timestamp.
     * @return string
     */
    public static function export($options)
    {
        $defaults = [
            'collector'      => null,
            'service_name'   => '',
            'service_job_id' => null,
            'repo_token'     => null,
            'run_at'         => date('Y-m-d H:i:s O')
        ];
        $options += $defaults;

        $collector = $options['collector'];

        $result = $options;
        unset($result['collector']);

        foreach ($collector->export() as $file => $data) {
            $nbLines = substr_count(file_get_contents($file), "\n");

            $lines = [];
            for ($i = 0; $i <= $nbLines; $i++) {
                $lines[] = isset($data[$i]) ? $data[$i] : null;
            }

            $result['source_files'][] = [
                'name' => $file,
                'source' => file_get_contents($file),
                'coverage' => $lines
            ];
        }

        return json_encode($result);
    }
}
