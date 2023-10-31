<?php

namespace CustomStandard\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * ヒアドキュメント構文の引用符を"EOL"または"SQL"に強制するルール
 */
class HeredocQuotesSniff implements Sniff
{
    public function register()
    {
        return [
            T_START_HEREDOC,
            T_END_HEREDOC,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        if ((bool)preg_match("/[a-zA-Z_][a-zA-Z0-9_]+/", $token["content"], $matches) === true) {
            if ($matches[0] === "EOL" || $matches[0] === "SQL") {
                return;
            }

            $phpcsFile->addError(
                "The quoting identifier for the heredoc expects \"SQL\" or \"EOL\" but found \"%s\"",
                $stackPtr,
                "HeredocQuotes",
                [$matches[0]]
            );
        }
    }

}
