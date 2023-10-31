<?php

namespace CustomStandard\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;

/**
 *
 */
class RequireExplicitCallInvokableClassSniff implements Sniff {
    public function register() {
        return [
            T_NEW,
        ];
    }

    public function process(File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();

        //newキーワードの前のトークンを調べる
        // "(" 以外の場合は対象外
        $previousTokenPtr = TokenHelper::findPreviousEffective($phpcsFile, $stackPtr - 1);
        $previousToken = $tokens[$previousTokenPtr];
        if ($previousToken["code"] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $checkTokenPtr = $previousTokenPtr;
        //do {
        //    $foundTokenPtr = TokenHelper::findPreviousEffective($phpcsFile, $checkTokenPtr - 1);
        //    $checkToken = $tokens[$foundTokenPtr];
        //    if ($checkToken["code"] === T_OPEN_PARENTHESIS) {
        //        $checkTokenPtr = $foundTokenPtr;
        //    }
        //} while ($checkToken["code"] === T_OPEN_PARENTHESIS);

        //__invoke()呼び出し直前の閉じ括弧まで進む
        $foundCloseBracketPtr = $this->findFunctionCallCloseBracket($phpcsFile, $checkTokenPtr);
        $foundNextTokenPtr = TokenHelper::findNextEffective($phpcsFile, $foundCloseBracketPtr + 1);
        $foundNextToken = $tokens[$foundNextTokenPtr];
        if ($foundNextToken["code"] === T_OPEN_PARENTHESIS) {
            $fix = $phpcsFile->addFixableError(
                "Callable classes should explicitly call the \"__invoke()\" method.",
                $foundNextTokenPtr,
                "RequireExplicitCallInvokableClass"
            );

            if ($fix === true) {
                $phpcsFile->fixer->replaceToken($foundNextTokenPtr, "->__invoke(");
            }
        }

    }

    /**
     * 関数呼び出しの閉じ括弧を探す
     *
     * @param File $phpcsFile
     * @param int  $startPointer 最初に開き括弧が見つかった位置
     *
     * @return int
     */
    private function findFunctionCallCloseBracket(File $phpcsFile, int $startPointer): int {
        $tokens = $phpcsFile->getTokens();
        $openBracketCount = 1;
        $closeBracketCount = 0;
        $nextTokenPtr = $startPointer;
        do {
            $nextTokenPtr = TokenHelper::findNext(
                $phpcsFile,
                [T_OPEN_PARENTHESIS, T_CLOSE_PARENTHESIS],
                $nextTokenPtr + 1
            );
            $nextToken = $tokens[$nextTokenPtr];
            if ($nextToken["code"] === T_OPEN_PARENTHESIS) {
                $openBracketCount++;
            } elseif ($nextToken["code"] === T_CLOSE_PARENTHESIS) {
                $closeBracketCount++;
            }

        } while ($openBracketCount !== $closeBracketCount);

        return $nextTokenPtr;
    }

}