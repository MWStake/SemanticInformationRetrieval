<?php
/**
 * Hooks for BoilerPlate extension
 *
 * @file
 * @ingroup Extensions
 */

class ExampleSIRIndexerHooks {

  static function onPageContentSaveComplete($article, $user, $content, $summary, $isMinor,
		$isWatch, $section, $flags, $status) {
    $pageName = $article->mTitle->mUrlform;
    $categories = Page::getMWPropertyInstanceValues($pageName, 'categories');
    if(in_array('Subject', $categories)) {
      #error_log('A');
      $_SESSION['elasticIndexThisPage'] = 'true';
    }
    return true;
  }

  static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parseroutput ) {
    $pageName = $out->getPageTitle();
    if($pageName <> "" && $_SESSION['elasticIndexThisPage'] == 'true') {
      $categories = Page::getMWPropertyInstanceValues($pageName, 'categories');
      if(in_array('Subject', $categories)) {
        $subjectIndexer = new ExampleElasticsearchSubjectIndexer($pageName);
        $subjectIndexer->indexPage();
      }
    }
    $_SESSION['elasticIndexThisPage'] = 'false';
    return true;
  }

}
