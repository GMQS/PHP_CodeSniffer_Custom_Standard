<?php

namespace CustomStandard\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * 文字列リテラルの引用符をダブルクォートに強制するルール
 */
class RequireDoubleQuotesSniff implements Sniff
{
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $content = $token["content"];

        //複数行の文字列 - 検出テキストが「'」のみ
        if ($content === "'") {
            $fix = $phpcsFile->addFixableError("Quote strings should be double quotes.", $stackPtr, "RequireDoubleQuotes");
            if ($fix === true) {
                $phpcsFile->fixer->replaceToken($stackPtr, "\"");
            }
            return;
        }

        //先頭と最後の文字を切り出す
        $firstChar = substr($content, 0, 1);
        $lastChar = substr($content, -1, 1);

        //複数行の文字列 - 開始引用符のみ出現
        if ($firstChar === "'" && $lastChar !== "'") {
            $trimmedString = substr($content, 1);
            $fix = $phpcsFile->addFixableError("Quote strings should be double quotes.", $stackPtr, "RequireDoubleQuotes");
            if ($fix === true) {
                $phpcsFile->fixer->replaceToken($stackPtr, "\"" . $trimmedString);
            }
            return;
        }

        //複数行の文字列 - 終了引用符のみ出現
        if ($firstChar !== "'" && $lastChar === "'") {
            $trimmedString = substr($content, 0, -1);
            $fix = $phpcsFile->addFixableError("Quote strings should be double quotes.", $stackPtr, "RequireDoubleQuotes");
            if ($fix === true) {
                $phpcsFile->fixer->replaceToken($stackPtr, $trimmedString . "\"");
            }
            return;
        }


        //ここから単一行の文字列
        $trimmedString = substr($content, 1, -1);
        if ($firstChar !== $lastChar) {
            return;
        }

        if ($firstChar !== "'" && $firstChar !== "\"") {
            return;
        }

        if ($firstChar === "'") {
            $fix = $phpcsFile->addFixableError("Quote strings should be double quotes.", $stackPtr, "RequireDoubleQuotes");
            if ($fix === true) {
                $phpcsFile->fixer->replaceToken($stackPtr, "\"{$trimmedString}\"");
            }
        }
    }

}