<?php
namespace LifterLMSCS\LifterLMS\Sniffs\Commenting;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FileCommentSniff as Squiz_FileCommentSniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
/**
 * Customizes the default Squiz_FileCommentSniff to match tags
 * in the order specified by LifterLMS documentation standards
 *
 * @link https://github.com/gocodebox/lifterlms/blob/master/docs/documentation-standards.md#file-headers
 */
class FileCommentSniff extends Squiz_FileCommentSniff {
	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param int                         $stackPtr  The position of the current token
	 *                                               in the stack passed in $tokens.
	 *
	 * @return int Stack pointer to skip the rest of the file.
	 */
	public function process( File $phpcsFile, $stackPtr ) {

        $tokens       = $phpcsFile->getTokens();
        $commentStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);


        if (isset($tokens[$commentStart]['comment_closer']) === false
            || ($tokens[$tokens[$commentStart]['comment_closer']]['content'] === ''
            && $tokens[$commentStart]['comment_closer'] === ($phpcsFile->numTokens - 1))
        ) {
            // Don't process an unfinished file comment during live coding.
            return ($phpcsFile->numTokens + 1);
        }

        $commentEnd   = $tokens[$commentStart]['comment_closer'];

        // Required tags in correct order.
        $required = [
            '@package'    => true,
            '@since'      => true,
            '@version'    => true,
            // '@subpackage' => true,
            // '@author'     => true,
            // '@copyright'  => true,
        ];

        $foundTags = [];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            $name       = $tokens[$tag]['content'];
            $isRequired = isset($required[$name]);

            if ($isRequired === true && in_array($name, $foundTags, true) === true) {

            	// Templates can have multiple since tags.
            	if ( '@since' !== $name || ( '@since' === $name && false === strpos( $phpcsFile->path, '/templates/' ) ) ) {

	                $error = 'Only one %s tag is allowed in a file comment';
	                $data  = [$name];
	                $phpcsFile->addError($error, $tag, 'Duplicate'.ucfirst(substr($name, 1)).'Tag', $data);

            	}

            }

            $foundTags[] = $name;

            if ($isRequired === false) {
                continue;
            }

            $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
            if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                $error = 'Content missing for %s tag in file comment';
                $data  = [$name];
                $phpcsFile->addError($error, $tag, 'Empty'.ucfirst(substr($name, 1)).'Tag', $data);
                continue;
            }

        }//end foreach

        // Check if the tags are in the correct position.
        $pos = 0;
        foreach ($required as $tag => $true) {
            if (in_array($tag, $foundTags, true) === false) {
                $error = 'Missing %s tag in file comment';
                $data  = [$tag];
                $phpcsFile->addError($error, $commentEnd, 'Missing'.ucfirst(substr($tag, 1)).'Tag', $data);
            }

            // Exclude potential duplicate since tags.
            $foundTags = array_unique( $foundTags );

            if (isset($foundTags[$pos]) === false) {
                break;
            }

            if ($foundTags[$pos] !== $tag) {
                $error = 'The tag in position %s should be the %s tag';
                $data  = [
                    ($pos + 1),
                    $tag,
                ];
                $phpcsFile->addError($error, $tokens[$commentStart]['comment_tags'][$pos], ucfirst(substr($tag, 1)).'TagOrder', $data);
            }

            $pos++;
        }//end foreach

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);

    }
}
