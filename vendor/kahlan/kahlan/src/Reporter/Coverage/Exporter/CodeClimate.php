<?php
namespace Kahlan\Reporter\Coverage\Exporter;

use RuntimeException;

class CodeClimate
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
     * @param  array  $options The option array where the possible values are:
     *                         -`'collector'`      _object_ : The collector instance.
     *                         -`'repo_token'`     _string_ : The Coveralls repo token.
     *                         -`'head'`           _string_ : The HEAD hash.
     *                         -`'branch'`         _string_ : The branch name.
     *                         -`'committed_at'`   _integer_: The committed timestamp.
     *                         -`'environment'`    _array_  : The Environment. Possible values are:
     *                           -`'pwd'`          _string_ : The repo absolute path.
     *                         -`'ci_service'`     _string_ : The CI service name
     *                           - `'name`             _string_ : CI service name
     *                           - `'build_identifier` _string_ : build identifier
     *                           - `'build_url`        _string_ : build url
     *                           - `'branch`           _string_ : branch name
     *                           - `'commit_sha`       _string_ : commit SHA
     *                           - `'pull_request`     _string_ : pull request id
     *                         -`'run_at'`         _integer_: The runned timestamp.
     * @return string
     */
    public static function export($options)
    {
        $defaults = [
            'collector'    => null,
            'head'         => null,
            'branch'       => null,
            'committed_at' => null,
            'repo_token'   => null,
            'environment'  => [
                'pwd' => getcwd()
            ],
            'ci_service'  => [],
            'run_at'      => time()
        ];
        $options += $defaults;

        return json_encode([
            'partial'      => false,
            'run_at'       => $options['run_at'],
            'repo_token'   => $options['repo_token'],
            'environment'  => $options['environment'] + ['package_version' => '0.1.2'],
            'git'          => [
                'head'         => $options['head'] ?: `git log -1 --pretty=format:'%H'`,
                'branch'       => $options['branch'] ?: trim(`git rev-parse --abbrev-ref HEAD`),
                'committed_at' => $options['committed_at'] ?: `git log -1 --pretty=format:'%ct'`
            ],
            'ci_service'   => $options['ci_service'],
            'source_files' => static::_sourceFiles($options['collector'])
        ]);
    }

    /**
     * Exports source file coverage
     *
     * @param  object $collector The collector instance.
     * @return array
     */
    protected static function _sourceFiles($collector)
    {
        $result = [];
        foreach ($collector->export() as $file => $data) {
            $content = file_get_contents($file);
            $nbLines = substr_count($content, "\n");

            $lines = [];
            for ($i = 0; $i <= $nbLines; $i++) {
                $lines[] = isset($data[$i]) ? $data[$i] : null;
            }

            $result[] = [
                'name'     => $file,
                'coverage' => json_encode($lines),
                'blob_id'  => sha1('blob ' . strlen($content) . "\0" . $content)
            ];
        }

        return $result;
    }
}
