<?php

declare(strict_types=1);

/*
 * CRA design system - style checker. No dependency, PHP >= 8.1.
 *
 *   php <skill>/scripts/check-styles.php [project-root]
 *
 * Checks, from the Symfony project root (default: current directory):
 *  1. assets/styles/tokens.css declares exactly the tokens of the skill's tokens/tokens.json
 *     (or assets/styles/tokens.json when the project keeps a copy), with the same references.
 *  2. Every var(--x) in base.css, utilities.css, print.css and components/*.css is a SEMANTIC token
 *     or a custom property declared in the same file (component-scoped, e.g. --button-bg).
 *  3. No hard-coded colors or lengths in those files, except: @media / @page preludes,
 *     the 1px of the visually-hidden pattern, and 0.
 * Exit code 1 when something is wrong. Run it after every CSS or token change.
 */

$root = rtrim($argv[1] ?? getcwd(), '/');
$skill = dirname(__DIR__);
$tokensJson = is_file("$root/assets/styles/tokens.json") ? "$root/assets/styles/tokens.json" : "$skill/tokens/tokens.json";
$tokensCss = "$root/assets/styles/tokens.css";
$errors = [];

if (!is_file($tokensCss)) {
    fwrite(STDERR, "tokens.css not found in $root/assets/styles/ (pass the project root as argument)\n");
    exit(1);
}

$json = json_decode((string) file_get_contents($tokensJson), true, flags: JSON_THROW_ON_ERROR);
$primitive = [];
$semantic = [];
foreach ($json['primitive'] as $group) {
    foreach ($group as $name => $token) {
        $primitive[$name] = $token['$value'];
    }
}
foreach ($json['semantic'] as $group) {
    foreach ($group as $name => $token) {
        $semantic[$name] = $token['$value'];
    }
}

// ---------------------------------------------------------------- 1. tokens.css <-> tokens.json
$css = (string) file_get_contents($tokensCss);
$rootBlock = substr($css, strpos($css, ':root {'), strpos($css, "\n}\n") - strpos($css, ':root {'));
preg_match_all('/^\s*--([\w-]+):\s*([^;]+);/m', $rootBlock, $m, PREG_SET_ORDER);
$declared = [];
foreach ($m as [, $name, $value]) {
    $declared[$name] = trim($value);
}
$ref = static fn (string $alias): string => 'var(--'.trim($alias, '{}').')';

foreach ($primitive as $name => $value) {
    if (!isset($declared[$name])) {
        $errors[] = "tokens.css: primitive --$name missing";
    } elseif ($declared[$name] !== $value) {
        $errors[] = "tokens.css: --$name is '{$declared[$name]}', tokens.json says '$value'";
    }
}
foreach ($semantic as $name => $value) {
    $light = \is_array($value) ? $value['light'] : $value;
    if (!isset($declared[$name])) {
        $errors[] = "tokens.css: semantic --$name missing";
    } elseif ($declared[$name] !== $ref($light)) {
        $errors[] = "tokens.css: --$name is '{$declared[$name]}', tokens.json says '{$ref($light)}'";
    }
    if (\is_array($value) && $value['dark'] !== $value['light']) {
        $needle = "--$name: {$ref($value['dark'])};";
        if (substr_count($css, $needle) < 2) {
            $errors[] = "tokens.css: dark value of --$name should be {$ref($value['dark'])} (in both dark blocks)";
        }
    }
}
foreach (array_keys($declared) as $name) {
    if (!isset($primitive[$name]) && !isset($semantic[$name])) {
        $errors[] = "tokens.css: --$name is not in tokens.json";
    }
}

// ---------------------------------------------------------------- 2 + 3. stylesheets
$files = array_merge(
    glob("$root/assets/styles/components/*.css") ?: [],
    array_filter(["$root/assets/styles/base.css", "$root/assets/styles/utilities.css", "$root/assets/styles/print.css"], 'is_file'),
);

foreach ($files as $file) {
    $short = substr($file, \strlen($root) + 1);
    // Strip comments but keep line breaks, so reported line numbers stay right.
    $source = (string) preg_replace_callback('~/\*.*?\*/~s', static fn (array $c): string => str_repeat("\n", substr_count($c[0], "\n")), (string) file_get_contents($file));
    preg_match_all('/(--[\w-]+)\s*:/', $source, $locals);
    $local = array_flip($locals[1]);

    preg_match_all('/var\(--([\w-]+)/', $source, $vars);
    foreach (array_unique($vars[1]) as $name) {
        if (isset($local["--$name"]) || isset($semantic[$name])) {
            continue;
        }
        $errors[] = isset($primitive[$name])
            ? "$short: primitive token --$name used directly (use a semantic token)"
            : "$short: unknown token --$name (add it to tokens.json + tokens.css)";
    }

    // Literal values, outside @media/@page preludes and the visually-hidden pattern.
    $lines = explode("\n", $source);
    $inPage = false;
    foreach ($lines as $i => $line) {
        if (preg_match('/^\s*@page\b/', $line)) {
            $inPage = true;
        }
        if ($inPage) {
            $inPage = !str_contains($line, '}');
            continue; // @page cannot read custom properties
        }
        if (preg_match('/^\s*@(media|page|import|layer|font-face|keyframes)/', $line) || str_contains($line, 'src: url(')) {
            continue;
        }
        if (preg_match('/^\s*(width|height|margin):\s*-?1px;/', $line)) {
            continue; // visually-hidden pattern
        }
        if (preg_match_all('/#[0-9a-fA-F]{3,8}\b|(?<![\w-])\d*\.?\d+(px|rem|em|ms|s|pt|mm|vh|vw)\b/', $line, $lit)) {
            foreach ($lit[0] as $literal) {
                $errors[] = sprintf('%s:%d: hard-coded value %s (use or create a semantic token)', $short, $i + 1, $literal);
            }
        }
    }
}

if ([] === $errors) {
    echo "OK - tokens in sync, only semantic tokens used in ".\count($files)." stylesheets.\n";
    exit(0);
}

echo implode("\n", $errors)."\n\n".\count($errors)." problem(s).\n";
exit(1);
