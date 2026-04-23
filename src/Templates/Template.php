<?php

class TTemplate
{
    const BASE_DIRECTORY = __DIR__ . '/../../../';

    protected string $templatePath;
    protected string $cachePath;

 //   protected bool $compileCacheEnabled = false;
    protected int $compileCacheTtl = -1;

 //   protected bool $renderCacheEnabled = false;
    protected int $renderCacheTtl = -1;

 //   protected bool $fragmentCacheEnabled = false;
    protected int $fragmentCacheTtl = -1;

    protected bool $debug = false;

    protected array $blocks = [];
    protected array $lazyBlocks = [];
    protected array $globals = [];

    protected array $deps = [];
    protected array $renderStack = [];

    public function __construct(string $templatePath, string $cachePath = null)
    {
        $this->templatePath = self::BASE_DIRECTORY . rtrim($templatePath, '/');
        $this->cachePath = $cachePath ?? self::BASE_DIRECTORY . 'var/cache';

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    public function setCompileCacheTtl(int $ttl): void
    {
        $this->compileCacheTtl = $ttl;
    }

    public function setRenderCacheTtl(int $ttl): void
    {
        $this->renderCacheTtl = $ttl;
    }

    public function setFragmentCacheTtl(int $ttl): void
    {
        $this->fragmentCacheTtl = $ttl;
    }

    public function setGlobals(array $vars): void
    {
        $this->globals = $vars;
    }

    protected function mergeVars(array $vars): array
    {
        return array_merge($this->globals, $vars);
    }

    // ===================== BLOCKS =====================

    public function renderBlock(string $name, string $file, array $vars = []): void
    {
        $this->lazyBlocks[$name] = function ($varsOverride = []) use ($file, $vars, $name) {
            $finalVars = $this->mergeVars(array_merge($vars, $varsOverride));
            $hash = md5(serialize($finalVars));

            $sourceHash = md5_file($this->templatePath . '/' . $file);
            $fragFile = $this->cachePath . '/frag_' . md5($name . $hash . $sourceHash) . '.html';

            // dependency tracking
            $parent = end($this->renderStack);
            if ($parent) {
                $this->deps[$parent][] = $name;
            }

            // in-memory memo
            if (isset($this->blocks[$name][$hash])) {
                return $this->blocks[$name][$hash];
            }

            // fragment cache hit
            if (/*$this->fragmentCacheEnabled && */!$this->debug && $this->isCacheValid($fragFile, $this->fragmentCacheTtl)) {
                $html = file_get_contents($fragFile);
                $this->blocks[$name][$hash] = $html;
                return $html;
            }

            $compiled = $this->compile($file);

            $this->renderStack[] = $name;

            ob_start();
            extract($finalVars);
            $blocks = $this->createBlockAccessor();
            include $compiled;
            $output = ob_get_clean();

            array_pop($this->renderStack);

            if (/*$this->fragmentCacheEnabled*/ $this->fragmentCacheTtl >= 0 && !$this->debug) {
                file_put_contents($fragFile, $output);
            }

            $this->blocks[$name][$hash] = $output;
            return $output;
        };
    }

    public function resolveBlock(string $name, array $vars = [])
    {
        if (!isset($this->lazyBlocks[$name])) {
            return '';
        }
        return ($this->lazyBlocks[$name])($vars);
    }

    // ===================== FINAL RENDER =====================

    public function render(string $file, array $vars = []): string
    {
        $vars = $this->mergeVars($vars);

        $sourceHash = md5_file($this->templatePath . '/' . $file);
        $cacheKey = md5($file . $sourceHash . serialize($vars));

        $cacheFileHtml = $this->cachePath . '/render_' . $cacheKey . '.html';
        $cacheFilePhp  = $this->cachePath . '/render_' . $cacheKey . '.php';

        if (/*!$this->renderCacheEnabled*/ $this->renderCacheTtl < 0 || $this->debug) {
            return $this->execute($file, $vars);
        }

        if ($this->isCacheValid($cacheFileHtml, $this->renderCacheTtl)) {
            return file_get_contents($cacheFileHtml);
        }

        if (!$this->isCacheValid($cacheFilePhp, $this->renderCacheTtl)) {
            $compiled = $this->compile($file);
            copy($compiled, $cacheFilePhp);
        }

        $this->renderStack[] = '__root__';

        ob_start();
        extract($vars);
        $blocks = $this->createBlockAccessor();
        include $cacheFilePhp;
        $output = ob_get_clean();

        array_pop($this->renderStack);

        file_put_contents($cacheFileHtml, $output);

        return $output;
    }

    protected function execute(string $file, array $vars): string
    {
        $compiled = $this->compile($file, true);

        $this->renderStack[] = '__root__';

        ob_start();
        extract($vars);
        $blocks = $this->createBlockAccessor();
        include $compiled;
        $output = ob_get_clean();

        array_pop($this->renderStack);

        return $output;
    }

    protected function isCacheValid(string $file, int $ttl): bool
    {
        if (!file_exists($file)) return false;
        if ($ttl === 0) return true;
        if ($ttl < 0) return false;
        return (time() - filemtime($file)) <= $ttl;
    }

    public function clearCache(): void
    {
        foreach (glob($this->cachePath . '/*.php') as $f) unlink($f);
        foreach (glob($this->cachePath . '/*.html') as $f) unlink($f);
    }

    public function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    private function parseLangTags(string $content): string
    {
        $result = '';
        $offset = 0;

        while (($pos = strpos($content, '@lang(', $offset)) !== false) {
            // Přidáme text před @lang
            $result .= substr($content, $offset, $pos - $offset);

            $start = $pos + 6; // pozice za "@lang("
            $level = 1;
            $i = $start;

            while ($i < strlen($content) && $level > 0) {
                if ($content[$i] === '(') $level++;
                if ($content[$i] === ')') $level--;
                $i++;
            }

            // Obsah uvnitř závorek
            $inside = substr($content, $start, $i - $start - 1);

            // Rozdělíme parametry podle čárek, ale zachováme složité výrazy (např. pole, volání funkcí)
            $params = preg_split('/,(?=(?:[^\[\]]*[\[\]])*[^\[\]]*$)/', $inside);

            // Trim pro každý parametr
            $params = array_map('trim', $params);

            if (count($params) === 2) {
                // Jen klíč a default
                $replacement = "<?= \$this->e(__({$params[0]}, {$params[1]})) ?>";
            } elseif (count($params) >= 3) {
                // Klíč, default a parametry (může být pole nebo jediná hodnota)
                $replacement = "<?= \$this->e(sprintf(__({$params[0]}, {$params[1]}), ...(is_array({$params[2]}) ? {$params[2]} : [{$params[2]}]))) ?>";
            } else {
                // Jen klíč
                $replacement = "<?= \$this->e(__({$params[0]})) ?>";
            }

            $result .= $replacement;

            $offset = $i;
        }

        // Zbytek
        $result .= substr($content, $offset);

        return $result;
    }

    private function extractParenthesesContent(string $content, int $startPos): array
    {
        $length = strlen($content);
        $depth = 0;
        $expression = '';

        for ($i = $startPos; $i < $length; $i++) {
            $char = $content[$i];

            if ($char === '(') {
                $depth++;
                if ($depth > 1) {
                    $expression .= $char;
                }
            } elseif ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    return [$expression, $i];
                }
                $expression .= $char;
            } else {
                if ($depth > 0) {
                    $expression .= $char;
                }
            }
        }

        throw new Exception("Neuzavřená závorka v šabloně.");
    }

    private function compileStructure(string $content, string $directive, string $phpKeyword): string
    {
        $offset = 0;

        while (($pos = strpos($content, "@$directive(", $offset)) !== false) {

            $start = $pos + strlen($directive) + 1;

            [$expression, $endPos] = $this->extractParenthesesContent($content, $start);

            $phpCode = "<?php $phpKeyword($expression): ?>";

            $length = $endPos - $pos + 1;

            $content = substr_replace($content, $phpCode, $pos, $length);

            $offset = $pos + strlen($phpCode);
        }

        return $content;
    }

    private function compileControlStructures(string $content): string
    {
        // složité direktivy s () – IF / ELSEIF / FOREACH
        $content = $this->compileStructure($content, 'if', 'if');
        $content = $this->compileStructure($content, 'elseif', 'elseif');
        $content = $this->compileStructure($content, 'foreach', 'foreach');

        // jednoduché direktivy s () – FOR / WHILE
        $content = $this->compileStructure($content, 'for', 'for');
        $content = $this->compileStructure($content, 'while', 'while');

        // jednoduché direktivy bez závorek
        $content = str_replace('@else', '<?php else: ?>', $content);
        $content = str_replace('@endif', '<?php endif; ?>', $content);
        $content = str_replace('@endforeach', '<?php endforeach; ?>', $content);
        $content = str_replace('@endfor', '<?php endfor; ?>', $content);
        $content = str_replace('@endwhile', '<?php endwhile; ?>', $content);

        return $content;
    }

    protected function compile(string $file, bool $force = false): string
    {
        $sourceFile = $this->templatePath . '/' . $file;

        $hash = md5_file($sourceFile);
        $compiledFile = $this->cachePath . '/compiled_' . md5($file . $hash) . '.php';

        if (/*$this->compileCacheEnabled && */!$force && $this->isCacheValid($compiledFile, $this->compileCacheTtl)) {
            return $compiledFile;
        }

        $content = file_get_contents($sourceFile);

        // 1️⃣ raw HTML {!! !!} – vloží přímo
        $content = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?= $1 ?>', $content);

        // 2️⃣ escapované {{ }} – htmlspecialchars
        $content = preg_replace('/\{{\s*(.+?)\s*\}}/s', '<?= $this->e($1) ?>', $content);

        // 3️⃣ @lang direktiva
        $content = $this->parseLangTags($content);

        // ======================
        // @block('name'), @block("name"), @block(name)
        // ======================
        $content = preg_replace_callback(
            '/@block\s*\(\s*(?:[\'"])?([a-zA-Z0-9_]+)(?:[\'"])?\s*\)/',
            function ($matches) {
                $name = $matches[1];
                return "<?php echo \$blocks->$name; ?>";
            },
            $content
        );

        // ======================
        // @if, @elseif, @foreach, @for, @while...
        // ======================
        $content = $this->compileControlStructures($content);

        if ($this->debug) {
            $content = "<?php /* TEMPLATE: $file */ ?>\n" . $content;
        }

        file_put_contents($compiledFile, $content);
        return $compiledFile;
    }

    protected function createBlockAccessor()
    {
        return new class($this) {
            private $tpl;
            public function __construct($tpl) { $this->tpl = $tpl; }
            public function __get($name) { return $this->tpl->resolveBlock($name); }
            public function call($name, array $vars = []) { return $this->tpl->resolveBlock($name, $vars); }
        };
    }

    public function build(string $entryFile, string $outputFile): void
    {
        $compiled = $this->compile($entryFile, true);
        $content = file_get_contents($compiled);

        $wrapper = "<?php\n";
        $wrapper .= "return function(array $vars = [], $tpl = null) { extract($vars); ?>\n";
        $wrapper .= $content;
        $wrapper .= "<?php };";

        file_put_contents($outputFile, $wrapper);
    }
}