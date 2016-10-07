<?php

use Strata\Strata;
use Strata\Logger\Debugger;

if (!function_exists('debug')) {
    /**
     * Prints out better looking debug information about a variable.
     * This echoes directly where it is called.
     * @param mixed1, [ $mixed2, $mixed3, ...]
     */
    function debug()
    {
        if (!WP_DEBUG) {
            return;
        }

        $mixed = func_get_args();
        if (count($mixed) === 1) {
            $mixed = $mixed[0];
        }

        $context = "In unknown context at unknown line";
        foreach (debug_backtrace() as $idx => $file) {
            if ($file['file'] != __FILE__) {
                $last = explode(DIRECTORY_SEPARATOR, $file['file']);
                $context = sprintf("In %s at line %s: ", $last[count($last)-1], $file['line']);
                break;
            }
        }

        foreach (func_get_args() as $variable) {
            $exported = Debugger::export($variable, 5);

            if (Strata::isBundledServer()) {
                Strata::app()->getLogger("StrataConsole")->debug("\n\n<warning>$context</warning>\n" . $exported . "\n\n");
            }

            if (Strata::isCommandLineInterface()) {
                echo "$context\n$exported\n";
            } else {
                echo "<div style=\"".Debugger::HTML_STYLES."\"><pre>$context<br>" . $exported . "</pre></div>";
            }
        }
    }
}

if (!function_exists('stackTrace')) {
    /**
     * Outputs a stack trace based on the supplied options.
     *
     * ### Options
     *
     * - `depth` - The number of stack frames to return. Defaults to 50
     * - `start` - The stack frame to start generating a trace from. Defaults to 1
     *
     * @param array $options Format for outputting stack trace
     * @return mixed Formatted stack trace
     * @link https://github.com/cakephp/cakephp/blob/master/src/basics.php
     */
    function stackTrace(array $options = array())
    {
        if (!WP_DEBUG) {
            return;
        }

        $defaults =  array('start' => 0);

        if (Strata::isBundledServer() || Strata::isCommandLineInterface()) {
            $defaults['output'] = Debugger::CONSOLE;
        }

        $options += $defaults;
        $options['start']++;
        $trace = Debugger::trace(null, $options);

        if (Strata::isBundledServer()) {
            Strata::app()->getLogger("StrataConsole")->debug("\n\n" . $trace . "\n\n");
        }

        if (Strata::isCommandLineInterface()) {
            echo "\n\n" . $trace . "\n\n";
        } else {
            echo "<div style=\"".Debugger::HTML_STYLES."\"><pre>". $trace ."</pre></div>";
        }
    }
}

if (!function_exists('breakpoint')) {
    /**
     * Command to return the eval-able code to startup PsySH in interactive debugger
     * Works the same way as eval(\Psy\sh());
     * psy/psysh must be loaded in your project
     * @link http://psysh.org/
     * @link https://github.com/cakephp/cakephp/blob/master/src/basics.php
     * @return string
     */
    function breakpoint()
    {
        if (!WP_DEBUG) {
            return;
        }

        if ((PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') && class_exists('\Psy\Shell')) {

            if (Strata::isBundledServer()) {
                Strata::app()->getLogger("StrataConsole")->debug("\n\nLaunching debugger...\n\n");
            }

            list(, $caller) = debug_backtrace(false);
            extract(\Psy\Shell::debug(get_defined_vars(), isset($caller) ? $caller : null));
            return;
        }

        trigger_error(
            "psy/psysh must be installed and you must be in a CLI environment to use the breakpoint function",
            E_USER_WARNING
        );
    }
}
