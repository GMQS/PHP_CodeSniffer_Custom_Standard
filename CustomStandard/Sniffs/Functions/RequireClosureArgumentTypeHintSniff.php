<?php

namespace CustomStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * 無名関数の引数にタイプヒントを強制するルール
 */
class RequireClosureArgumentTypeHintSniff implements Sniff
{
    public function register()
    {
        return [
            T_CLOSURE,
            T_FN,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        //ファイル内で出現するトークン全てを配列として取得する
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['parenthesis_opener']) === false
            || isset($tokens[$stackPtr]['parenthesis_closer']) === false
            || $tokens[$stackPtr]['parenthesis_opener'] === null
            || $tokens[$stackPtr]['parenthesis_closer'] === null
        ) {
            return;
        }

        $this->processBracket($phpcsFile, $tokens[$stackPtr]['parenthesis_opener']);

    }

    /**
     * Processes the contents of a single set of brackets.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile   The file being scanned.
     * @param int                         $openBracket The position of the open bracket
     *                                                 in the stack.
     *
     * @return void
     */
    public function processBracket(File $phpcsFile, int $openBracket)
    {
        $tokens = $phpcsFile->getTokens();

        $stackPtr = isset($tokens[$openBracket]['parenthesis_owner']) === true
            ? $tokens[$openBracket]['parenthesis_owner']
            : $phpcsFile->findPrevious(T_USE, ($openBracket - 1));

        $params = $phpcsFile->getMethodParameters($stackPtr);

        //関数の引数が無い時
        if ($params === []) {
            return;
        }

        foreach ($params as $param) {
            $hasTypeHint = $param["type_hint"] !== "";

            //引数に型定義が無い時にエラーを追加
            if ($hasTypeHint === false) {
                $phpcsFile->addError(
                    "Requires type definition for argument %s",
                    $param["token"],
                    "RequireTypeHint",
                    [$param["name"]],
                );
            }
        }
    }
}