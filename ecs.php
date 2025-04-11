<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\ReturnToYieldFromFixer;
use PhpCsFixer\Fixer\Alias\ArrayPushFixer;
use PhpCsFixer\Fixer\Alias\BacktickToShellExecFixer;
use PhpCsFixer\Fixer\Alias\NoAliasLanguageConstructCallFixer;
use PhpCsFixer\Fixer\AttributeNotation\AttributeEmptyParenthesesFixer;
use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\Basic\NoMultipleStatementsPerLineFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\Basic\NumericLiteralSeparatorFixer;
use PhpCsFixer\Fixer\Basic\OctalNotationFixer;
use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\IntegerLiteralCaseFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfStaticAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\ControlStructureContinuationPositionFixer;
use PhpCsFixer\Fixer\ControlStructure\ElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\ControlStructure\NoBreakCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\SimplifiedIfReturnFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSemicolonToColonFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSpaceFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\SingleLineThrowFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnneededImportAliasFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\Import\SingleLineAfterImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareParenthesesFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\LanguageConstruct\SingleSpaceAroundConstructFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\AssignNullCoalescingToCoalesceEqualFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use PhpCsFixer\Fixer\Operator\NoSpaceAroundDoubleColonFixer;
use PhpCsFixer\Fixer\Operator\NoUselessConcatOperatorFixer;
use PhpCsFixer\Fixer\Operator\NoUselessNullsafeOperatorFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAddMissingParamAnnotationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAliasTagFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitAttributesFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderMethodOrderFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\ReturnNotation\SimplifiedNullReturnFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocClosingMarkerFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer;
use PhpCsFixer\Fixer\StringNotation\MultilineStringToHeredocFixer;
use PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBetweenImportGroupsFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\Whitespace\HeredocIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use PhpCsFixer\Fixer\Whitespace\SpacesInsideParenthesesFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/examples',
    ]);

    $ecsConfig->skip([
        __DIR__ . '/vendor',
    ]);

    $ecsConfig->sets([
        SetList::ARRAY,
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::DOCBLOCK,
        SetList::SPACES,
    ]);

    // Configure rules
    $ecsConfig->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, [
        'default' => 'align_single_space_minimal_by_scope',
    ]);

    $ecsConfig->ruleWithConfiguration(BlankLinesBeforeNamespaceFixer::class, [
        'max_line_breaks' => 1,
        'min_line_breaks' => 0,
    ]);

    $ecsConfig->ruleWithConfiguration(ControlStructureContinuationPositionFixer::class, [
        'position' => 'next_line',
    ]);

    $ecsConfig->ruleWithConfiguration(DeclareEqualNormalizeFixer::class, [
        'space' => 'single',
    ]);

    $ecsConfig->ruleWithConfiguration(MethodArgumentSpaceFixer::class, [
        'after_heredoc' => true,
        'attribute_placement' => 'same_line',
        'keep_multiple_spaces_after_comma' => false,
        'on_multiline' => 'ensure_single_line',
    ]);

    $ecsConfig->ruleWithConfiguration(ClassAttributesSeparationFixer::class, [
        'elements' => [
            'const' => 'only_if_meta',
            'method' => 'one',
            'property' => 'only_if_meta',
            'trait_import' => 'only_if_meta',
            'case' => 'only_if_meta',
        ],
    ]);

    $ecsConfig->ruleWithConfiguration(PhpdocAddMissingParamAnnotationFixer::class, [
        'only_untyped' => false,
    ]);

    $ecsConfig->ruleWithConfiguration(BlankLineBeforeStatementFixer::class, [
        'statements' => ['return'],
    ]);

    $ecsConfig->ruleWithConfiguration(BracesPositionFixer::class, [
        'classes_opening_brace' => 'same_line',
        'functions_opening_brace' => 'same_line',
    ]);

    $ecsConfig->ruleWithConfiguration(ConcatSpaceFixer::class, [
        'spacing' => 'none',
    ]);

    $ecsConfig->ruleWithConfiguration(TrailingCommaInMultilineFixer::class, [
        'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
    ]);

    $ecsConfig->ruleWithConfiguration(        HeaderCommentFixer::class, [
        'header' => 'Made with love.',
        'comment_type' => 'PHPDoc',
        'location' => 'after_open',
        'separate' => 'bottom',
    ]);

    // Add all other rules
    $ecsConfig->rules([
        BlankLineAfterNamespaceFixer::class,
        BlankLineAfterOpeningTagFixer::class,
        BlankLineBetweenImportGroupsFixer::class,
        ClassDefinitionFixer::class,
        CompactNullableTypeDeclarationFixer::class,
        ConstantCaseFixer::class,
        ControlStructureBracesFixer::class,
        ElseifFixer::class,
        EncodingFixer::class,
        FullOpeningTagFixer::class,
        FunctionDeclarationFixer::class,
        IndentationTypeFixer::class,
        LineEndingFixer::class,
        LowercaseCastFixer::class,
        LowercaseKeywordsFixer::class,
        LowercaseStaticReferenceFixer::class,
        NewWithParenthesesFixer::class,
        NoBlankLinesAfterClassOpeningFixer::class,
        NoBreakCommentFixer::class,
        NoClosingTagFixer::class,
        NoExtraBlankLinesFixer::class,
        NoLeadingImportSlashFixer::class,
        NoMultipleStatementsPerLineFixer::class,
        NoSpaceAroundDoubleColonFixer::class,
        NoSpacesAfterFunctionNameFixer::class,
        NoTrailingWhitespaceFixer::class,
        NoTrailingWhitespaceInCommentFixer::class,
        NoWhitespaceInBlankLineFixer::class,
        OrderedClassElementsFixer::class,
        OrderedImportsFixer::class,
        ReturnTypeDeclarationFixer::class,
        ShortScalarCastFixer::class,
        SingleBlankLineAtEofFixer::class,
        SingleClassElementPerStatementFixer::class,
        SingleImportPerStatementFixer::class,
        SingleLineAfterImportsFixer::class,
        SingleSpaceAroundConstructFixer::class,
        SpacesInsideParenthesesFixer::class,
        StatementIndentationFixer::class,
        SwitchCaseSemicolonToColonFixer::class,
        SwitchCaseSpaceFixer::class,
        TernaryOperatorSpacesFixer::class,
        UnaryOperatorSpacesFixer::class,
        VisibilityRequiredFixer::class,
        AlignMultilineCommentFixer::class,
        ArrayIndentationFixer::class,
        ArrayPushFixer::class,
        ArraySyntaxFixer::class,
        AssignNullCoalescingToCoalesceEqualFixer::class,
        AttributeEmptyParenthesesFixer::class,
        NoSpacesAroundOffsetFixer::class,
        CastSpacesFixer::class,
        SingleLineEmptyBodyFixer::class,
        TernaryToNullCoalescingFixer::class,
        SimpleToComplexStringVariableFixer::class,
        OctalNotationFixer::class,
        NormalizeIndexBraceFixer::class,
        NoWhitespaceBeforeCommaInArrayFixer::class,
        NoUnsetCastFixer::class,
        ListSyntaxFixer::class,
        HeredocIndentationFixer::class,
        CleanNamespaceFixer::class,
        BacktickToShellExecFixer::class,
        ClassReferenceNameCasingFixer::class,
        CombineConsecutiveIssetsFixer::class,
        CombineConsecutiveUnsetsFixer::class,
        DeclareParenthesesFixer::class,
        ExplicitIndirectVariableFixer::class,
        ExplicitStringVariableFixer::class,
        FullyQualifiedStrictTypesFixer::class,
        HeredocClosingMarkerFixer::class,
        HeredocToNowdocFixer::class,
        IntegerLiteralCaseFixer::class,
        MethodChainingIndentationFixer::class,
        MultilineWhitespaceBeforeSemicolonsFixer::class,
        MultilineStringToHeredocFixer::class,
        NativeTypeDeclarationCasingFixer::class,
        NativeFunctionCasingFixer::class,
        NoAliasLanguageConstructCallFixer::class,
        NoAlternativeSyntaxFixer::class,
        NoBlankLinesAfterPhpdocFixer::class,
        NoBinaryStringFixer::class,
        NoEmptyCommentFixer::class,
        NoEmptyPhpdocFixer::class,
        NoEmptyStatementFixer::class,
        NoLeadingNamespaceWhitespaceFixer::class,
        NoMultilineWhitespaceAroundDoubleArrowFixer::class,
        NoNullPropertyInitializationFixer::class,
        NoShortBoolCastFixer::class,
        NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        NoSuperfluousElseifFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
        NoTrailingCommaInSinglelineFixer::class,
        NoUnneededBracesFixer::class,
        NoUnneededControlParenthesesFixer::class,
        NoUnneededImportAliasFixer::class,
        NoUnusedImportsFixer::class,
        NoUselessConcatOperatorFixer::class,
        NoUselessElseFixer::class,
        NoUselessNullsafeOperatorFixer::class,
        NoUselessReturnFixer::class,
        NullableTypeDeclarationFixer::class,
        NumericLiteralSeparatorFixer::class,
        ObjectOperatorWithoutWhitespaceFixer::class,
        PhpUnitAttributesFixer::class,
        PhpUnitDataProviderMethodOrderFixer::class,
        PhpdocAlignFixer::class,
        PhpdocAnnotationWithoutDotFixer::class,
        PhpdocIndentFixer::class,
        PhpdocOrderFixer::class,
        PhpdocLineSpanFixer::class,
        PhpdocNoAccessFixer::class,
        PhpdocNoAliasTagFixer::class,
        PhpdocNoEmptyReturnFixer::class,
        PhpdocNoPackageFixer::class,
        PhpdocNoUselessInheritdocFixer::class,
        PhpdocInlineTagNormalizerFixer::class,
        PhpdocOrderByValueFixer::class,
        PhpdocReturnSelfReferenceFixer::class,
        PhpdocScalarFixer::class,
        ReturnAssignmentFixer::class,
        ReturnToYieldFromFixer::class,
        SelfStaticAccessorFixer::class,
        SimplifiedIfReturnFixer::class,
        SimplifiedNullReturnFixer::class,
        SingleLineCommentSpacingFixer::class,
        SingleLineCommentStyleFixer::class,
        SingleLineThrowFixer::class,
        SingleQuoteFixer::class,
        StandardizeNotEqualsFixer::class,
        TypeDeclarationSpacesFixer::class,
        TypesSpacesFixer::class,
        YodaStyleFixer::class,
    ]);

    $ecsConfig->skip([
        '*Risky*',
    ]);
};
