<?php
require __DIR__ . '/../vendor/autoload.php';

use NateWr\themehelper\ThemeHelper;

$reflection = new ReflectionClass(ThemeHelper::class);

$functions = [];

foreach ($reflection->getMethods() as $method) {
    $docblock = $method->getDocComment();

    // @example
    preg_match('/\@example (\{([a-z_]*)[\s\S]*?([a-z_=\"\$]*)?\})/mi', $docblock, $matches);
    if (!count($matches)) {
        continue;
    }

    $name = $matches[2];
    $example = preg_replace('/([\s\*]+)/mi', "\n    ", $matches[1]);
    $example = str_replace('    }', "}", $example);

    // // Description
    preg_match('/[\/\*\s]*([\s\S]*)?@example/mi', $docblock, $matches);
    $desc = trim(preg_replace('/([ ]{2,}\*[\/]?[ ]?)/mi', '', $matches[1]));

    // @option
    preg_match_all('/@option ([a-z\[\]]*)? ([a-z]*) ([^@]*)/mi', $docblock, $params, PREG_SET_ORDER);

    $params = array_map(fn($option) => [
        'type' => $option[1],
        'name' => $option[2],
        'desc' => trim(preg_replace('/([\s]{2,}\*[\/]?([\s]{2,})?)/mi', ' ', $option[3])),
    ], $params);

    array_push($functions, [
        'name' => $name,
        'desc' => $desc,
        'example' => $example,
        'params' => $params,
    ]);
}

foreach ($functions as $function) {

    $params = [];
    foreach ($function['params'] as $param) {
        array_push(
            $params,
            "*@option* `{$param['type']}` **{$param['name']}** {$param['desc']}"
        );
    }
    $params = join("<br>\n", $params);

    echo "
#### {$function['name']}

{$function['desc']}

{$params}

```html
{$function['example']}
```
";
}