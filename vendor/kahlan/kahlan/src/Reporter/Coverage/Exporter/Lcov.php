<?php
namespace Kahlan\Reporter\Coverage\Exporter;

use RuntimeException;

class Lcov
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
     * Exports a coverage to a Istanbul compatible JSON format.
     *
     * @param  array  $options The option array where the possible values are:
     *                         -`'collector'`      _object_ : The collector instance.
     * @return string
     */
    public static function export($options)
    {
        $defaults = [
            'collector' => null,
            'base_path' => getcwd()
        ];
        $options += $defaults;

        $collector = $options['collector'];

        $export = '';

        $base = $options['base_path'] ? rtrim($options['base_path'], DS) . DS : '';

        foreach ($collector->export() as $file => $coverage) {
            $path = $base . $file;
            $export .= static::_export($path, $collector->parse($file), $coverage);
        }

        return $export;
    }

    /**
     * Exports source file coverage
     *
     * @param  object $collector The collector instance.
     * @return array
     */
    protected static function _export($path, $tree, $coverage)
    {
        $result = [
            'TN:',
            'SF:' . $path
        ];

        $statements = [];

        $fnda = [];
        $fnf = 0;

        $fnCurr = null;

        foreach ($tree->lines['content'] as $num => $content) {
            $coverable = null;
            foreach ($content['nodes'] as $node) {
                if ($node->type === 'function' && $node->lines['start'] === $num) {
                    if ($node->isMethod || !$node->isClosure) {
                        $result[] = 'FN:' . ($num + 1) . ',' . $node->name;
                        $fnda[$node->name] = 0;
                        $fnf++;
                        $fnCurr = $node;
                    }
                }
                if ($node->coverable && $node->lines['stop'] === $num) {
                    $coverable = $node;
                    break;
                }
            }
            if (!$coverable) {
                continue;
            }
            $value = isset($coverage[$num]) ? $coverage[$num] : 0;

            $statements[] = 'DA:' . ($num + 1) . ',' . $value;

            if ($fnCurr) {
                if ($fnCurr->lines['stop'] >= $coverable->lines['stop']) {
                    $fnda[$fnCurr->name] = max($fnda[$fnCurr->name], $value);
                }
            }
        }
        foreach ($fnda as $name => $value) {
            $result[] = 'FNDA:' . $value . ',' . $name;
        }
        $result[] = 'FNF:' . $fnf;
        $result[] = 'FNH:' . count(array_filter($fnda));
        $result = array_merge($result, $statements);
        $result[] = 'LF:' . count($coverage);
        $result[] = 'LH:' . count(array_filter($coverage));
        $result[] = 'end_of_record';
        return join("\n", $result) . "\n";
    }
}
