<?php
$l=new OC_L10N('aufgaben');
OC::$CLASSPATH['OCA\Aufgaben\App'] = 'aufgaben/lib/app.php';
OC::$CLASSPATH['OCA\Aufgaben\Timeline'] = 'aufgaben/lib/timeline.php';
OC::$CLASSPATH['OCA\Aufgaben\Share_Backend_Vtodo'] = 'aufgaben/lib/share/vtodo.php';
OC::$CLASSPATH['OCA\Aufgaben\SearchProvider'] = 'aufgaben/lib/search.php';
OCP\App::addNavigationEntry( array(
  'id' => 'aufgaben_index',
  'order' => 11,
  'href' => OCP\Util::linkTo( 'aufgaben', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'aufgaben', 'tasks.svg' ),
  'name' => $l->t('Tasks')));
  
OC_Search::registerProvider('OCA\Aufgaben\SearchProvider');  
OCP\Share::registerBackend('todo', 'OCA\Aufgaben\Share_Backend_Vtodo');