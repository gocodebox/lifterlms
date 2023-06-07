<?php

namespace PHP_Parallel_Lint\PhpConsoleHighlighter;

use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class Highlighter
{
    const TOKEN_DEFAULT = 'token_default',
        TOKEN_COMMENT = 'token_comment',
        TOKEN_STRING = 'token_string',
        TOKEN_HTML = 'token_html',
        TOKEN_KEYWORD = 'token_keyword';

    const ACTUAL_LINE_MARK = 'actual_line_mark',
        LINE_NUMBER = 'line_number';

    /** @var ConsoleColor */
    private $color;

    /** @var array */
    private $defaultTheme = array(
        self::TOKEN_STRING => 'red',
        self::TOKEN_COMMENT => 'yellow',
        self::TOKEN_KEYWORD => 'green',
        self::TOKEN_DEFAULT => 'default',
        self::TOKEN_HTML => 'cyan',

        self::ACTUAL_LINE_MARK  => 'red',
        self::LINE_NUMBER => 'dark_gray',
    );

    /** @var array */
    private $phpTagTokens = array(
        T_OPEN_TAG           => T_OPEN_TAG,
        T_OPEN_TAG_WITH_ECHO => T_OPEN_TAG_WITH_ECHO,
        T_CLOSE_TAG          => T_CLOSE_TAG,
    );

    /** @var array */
    private $magicConstantTokens = array(
        T_DIR      => T_DIR,
        T_FILE     => T_FILE,
        T_LINE     => T_LINE,
        T_CLASS_C  => T_CLASS_C,
        T_FUNC_C   => T_FUNC_C,
        T_METHOD_C => T_METHOD_C,
        T_NS_C     => T_NS_C,
    );

    /** @var array */
    private $miscTokens = array(
        T_STRING   => T_STRING, // Labels.
        T_VARIABLE => T_VARIABLE,
        T_DNUMBER  => T_DNUMBER, // Floats.
        T_LNUMBER  => T_LNUMBER, // Integers.
    );

    /** @var array */
    private $commentTokens = array(
        T_COMMENT     => T_COMMENT,
        T_DOC_COMMENT => T_DOC_COMMENT,
    );

    /** @var array */
    private $textStringTokens = array(
        T_ENCAPSED_AND_WHITESPACE  => T_ENCAPSED_AND_WHITESPACE,
        T_CONSTANT_ENCAPSED_STRING => T_CONSTANT_ENCAPSED_STRING,
    );

    /** @var array */
    private $htmlTokens = array(
        T_INLINE_HTML => T_INLINE_HTML,
    );

    /**
     * @param ConsoleColor $color
     * @throws \PHP_Parallel_Lint\PhpConsoleColor\InvalidStyleException
     */
    public function __construct(ConsoleColor $color)
    {
        $this->color = $color;

        foreach ($this->defaultTheme as $name => $styles) {
            if (!$this->color->hasTheme($name)) {
                $this->color->addTheme($name, $styles);
            }
        }
    }

    /**
     * @param string $source
     * @param int $lineNumber
     * @param int $linesBefore
     * @param int $linesAfter
     * @return string
     * @throws \PHP_Parallel_Lint\PhpConsoleColor\InvalidStyleException
     * @throws \InvalidArgumentException
     */
    public function getCodeSnippet($source, $lineNumber, $linesBefore = 2, $linesAfter = 2)
    {
        $tokenLines = $this->getHighlightedLines($source);

        $offset = $lineNumber - $linesBefore - 1;
        $offset = max($offset, 0);

        if ($lineNumber <= $linesBefore) {
            $length = $lineNumber + $linesAfter;
        } else {
            $length = $linesAfter + $linesBefore + 1;
        }

        $tokenLines = array_slice($tokenLines, $offset, $length, $preserveKeys = true);

        $lines = $this->colorLines($tokenLines);

        return $this->lineNumbers($lines, $lineNumber);
    }

    /**
     * @param string $source
     * @return string
     * @throws \PHP_Parallel_Lint\PhpConsoleColor\InvalidStyleException
     * @throws \InvalidArgumentException
     */
    public function getWholeFile($source)
    {
        $tokenLines = $this->getHighlightedLines($source);
        $lines = $this->colorLines($tokenLines);
        return implode(PHP_EOL, $lines);
    }

    /**
     * @param string $source
     * @return string
     * @throws \PHP_Parallel_Lint\PhpConsoleColor\InvalidStyleException
     * @throws \InvalidArgumentException
     */
    public function getWholeFileWithLineNumbers($source)
    {
        $tokenLines = $this->getHighlightedLines($source);
        $lines = $this->colorLines($tokenLines);
        return $this->lineNumbers($lines);
    }

    /**
     * @param string $source
     * @return array
     */
    private function getHighlightedLines($source)
    {
        $source = str_replace(array("\r\n", "\r"), "\n", $source);
        $tokens = $this->tokenize($source);
        return $this->splitToLines($tokens);
    }

    /**
     * @param string $source
     * @return array
     */
    private function tokenize($source)
    {
        $tokens = token_get_all($source);

        $output = array();
        $currentType = null;
        $buffer = '';

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] !== T_WHITESPACE) {
                    $newType = $this->getTokenType($token);
                }
            } else {
                $newType = $token === '"' ? self::TOKEN_STRING : self::TOKEN_KEYWORD;
            }

            if ($currentType === null) {
                $currentType = $newType;
            }

            if ($currentType !== $newType) {
                $output[] = array($currentType, $buffer);
                $buffer = '';
                $currentType = $newType;
            }

            $buffer .= is_array($token) ? $token[1] : $token;
        }

        if (isset($newType)) {
            $output[] = array($newType, $buffer);
        }

        return $output;
    }

    /**
     * @param array $arrayToken
     * @return string
     */
    private function getTokenType($arrayToken)
    {
        switch (true) {
            case isset($this->phpTagTokens[$arrayToken[0]]):
            case isset($this->magicConstantTokens[$arrayToken[0]]):
            case isset($this->miscTokens[$arrayToken[0]]):
                return self::TOKEN_DEFAULT;

            case isset($this->commentTokens[$arrayToken[0]]):
                return self::TOKEN_COMMENT;

            case isset($this->textStringTokens[$arrayToken[0]]):
                return self::TOKEN_STRING;

            case isset($this->htmlTokens[$arrayToken[0]]):
                return self::TOKEN_HTML;
        }

        // phpcs:disable PHPCompatibility.Constants.NewConstants -- The new token constants are only used when defined.

        // Traits didn't exist in PHP 5.3 yet, so the trait magic constant needs special casing for PHP >= 5.4.
        // __TRAIT__ will tokenize as T_STRING in PHP 5.3, so, the end result will be the same cross-version.
        if (defined('T_TRAIT_C') && $arrayToken[0] === T_TRAIT_C) {
            return self::TOKEN_DEFAULT;
        }

        // Handle PHP >= 8.0 namespaced name tokens.
        // https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.tokenizer
        if (
            (defined('T_NAME_QUALIFIED') && $arrayToken[0] === T_NAME_QUALIFIED)
            || (defined('T_NAME_FULLY_QUALIFIED') && $arrayToken[0] === T_NAME_FULLY_QUALIFIED)
            || (defined('T_NAME_RELATIVE') && $arrayToken[0] === T_NAME_RELATIVE)
        ) {
            return self::TOKEN_DEFAULT;
        }

        // phpcs:enable

        return self::TOKEN_KEYWORD;
    }

    /**
     * @param array $tokens
     * @return array
     */
    private function splitToLines(array $tokens)
    {
        $lines = array();

        $line = array();
        foreach ($tokens as $token) {
            foreach (explode("\n", $token[1]) as $count => $tokenLine) {
                if ($count > 0) {
                    $lines[] = $line;
                    $line = array();
                }

                if ($tokenLine === '') {
                    continue;
                }

                $line[] = array($token[0], $tokenLine);
            }
        }

        $lines[] = $line;

        return $lines;
    }

    /**
     * @param array $tokenLines
     * @return array
     * @throws \PHP_Parallel_Lint\PhpConsoleColor\InvalidStyleException
     * @throws \InvalidArgumentException
     */
    private function colorLines(array $tokenLines)
    {
        $lines = array();
        foreach ($tokenLines as $lineCount => $tokenLine) {
            $line = '';
            foreach ($tokenLine as $token) {
                list($tokenType, $tokenValue) = $token;
                if ($this->color->hasTheme($tokenType)) {
                    $line .= $this->color->apply($tokenType, $tokenValue);
                } else {
                    $line .= $tokenValue;
                }
            }
            $lines[$lineCount] = $line;
        }

        return $lines;
    }

    /**
     * @param array $lines
     * @param null|int $markLine
     * @return string
     * @throws \PHP_Parallel_Lint\PhpConsoleColor\InvalidStyleException
     */
    private function lineNumbers(array $lines, $markLine = null)
    {
        end($lines);
        $lineStrlen = strlen(key($lines) + 1);

        $snippet = '';
        foreach ($lines as $i => $line) {
            if ($markLine !== null) {
                $snippet .= ($markLine === $i + 1 ? $this->color->apply(self::ACTUAL_LINE_MARK, '  > ') : '    ');
            }

            $snippet .= $this->color->apply(self::LINE_NUMBER, str_pad($i + 1, $lineStrlen, ' ', STR_PAD_LEFT) . '| ');
            $snippet .= $line . PHP_EOL;
        }

        return rtrim($snippet, PHP_EOL);
    }
}
