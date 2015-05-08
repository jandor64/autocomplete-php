<?php
    $source = '';

    while ($a = fread(STDIN, 1024)) {
        $source .= $a;
    }

    $tokens = token_get_all($source);

    $cachedFunctions = array();
    $cachedFunctionComplex = array();
    $tmpFunc = array();

    $nextStringIsFunc = false;
    $functionParameters = false;
    $snippetCount = 1;

    foreach($tokens as $token) {
        switch($token[0]) {
            case T_FUNCTION:
                $nextStringIsFunc = true;
                break;
            case T_STRING:
                if($nextStringIsFunc) {
                    // $nextStringIsFunc = false;
                    if (!in_array($token[1], $cachedFunctions)) {
                        $cachedFunctions[] = $token[1];
                        $tmpFunc[] =  $token[1];
                    }
                }
                break;
            case '(':
                if ($nextStringIsFunc) {
                    $nextStringIsFunc = false;
                    $functionParameters = true;
                }
                break;
            case ')':
                if ($functionParameters) {
                    $functionParameters = false;
                    $cachedFunctionComplex[] = $tmpFunc;
                    $tmpFunc = array();
                    $snippetCount = 1;
                }
                break;
            case T_VARIABLE:
                if ($functionParameters) {
                    $tmpFunc[] = '${' . $snippetCount++ . ':' . $token[1] . '}';
                }
                break;
        }
    }


    $localFuncs = array();

    foreach ($cachedFunctionComplex as $funObj) {
        $tmp = [
            'text' => $funObj[0],
            'type' => 'function',
            'snippet' => array_shift($funObj) . '(' . implode(', ', $funObj) . ')${99}',
        ];

        array_push($localFuncs, $tmp);
    }

    echo json_encode(['user_functions' => $localFuncs]);