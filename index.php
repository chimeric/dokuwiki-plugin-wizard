<?php
error_reporting(E_ALL ^ E_NOTICE);

function create_bundle($conf) {
    $bundle = array();

    $search_replace = array(
        '@@AUTHOR_NAME@@' => $conf['author']['name'],
        '@@AUTHOR_MAIL@@' => $conf['author']['mail'],
        '@@PLUGIN_NAME@@' => $conf['name'],
        '@@PLUGIN_DESC@@' => $conf['desc'],
        '@@PLUGIN_URL@@'  => $conf['url'],
        '@@DATE@@'        => $conf['date'],

    );

    /**
      foreach component read in the relevant sekeleton replace al replacemetns
      and add it to the bundle array
    */
    foreach($conf['components'] as $type => $components) {
        foreach($conf['components'][$type] as $plugin => $data) {

            switch($type) {
                case 'action':
                    if($data['events']) {
                        $register = '';
                        $handler  = '';
                        $events = explode(',', $data['events']);
                        foreach($events as $event) {
                            if($event) {
                                $register .= "\n" . '       $controller->register_hook(\'' . $event . '\', \'FIXME\', $this, \'handle_' . strtolower($event) . '\');';
                                $handler  .= '    function handle_' . strtolower($event) . '(&$event, $param) { }' . "\n";
                            }
                        }
                        $search_replace['@@REGISTER@@'] = $register . "\n   ";
                        $search_replace['@@HANDLERS@@'] = $handler;
                    } else {
                        $search_replace['@@REGISTER@@'] = '';
                        $search_replace['@@HANDLERS@@'] = '';
                    }
                    break;
            }

            list($tmp, $name) = explode('_', $plugin, 2);

            $component['path'] = ($name) ?  $type . '/' . $name . '.php' : $type . '.php';
            $search_replace['@@PLUGIN_COMPONENT_NAME@@'] = $type . '_plugin_' . $plugin;
            $search_replace['@@INFO_TXT_PATH@@'] = ($name) ? '../plugin.info.txt' : 'plugin.info.txt';

            // use special skeleton for xhtml renderer
            if($data['inherits'] == 'Doku_Renderer_xhtml'){
                $skel = 'renderer_xhtml';
            }else{
                $skel = $type;
            }

            $skel = file_get_contents('./skel/' . $skel . '.skel');
            $skel = str_replace(array_keys($search_replace), array_values($search_replace), $skel);

            $component['skel'] = $skel;

            array_push($bundle, $component);
        }
    }

    // plugin.info.txt
    $skel = file_get_contents('./skel/info.skel');
    $skel = str_replace(array_keys($search_replace),
                        array_values($search_replace), $skel);
    $bundle[] = array('path' => 'plugin.info.txt',
                      'skel' => $skel);

    // configuration
    if($conf['use_config']){
        $skel = file_get_contents('./skel/conf/default.skel');
        $skel = str_replace(array_keys($search_replace),
                            array_values($search_replace), $skel);
        $bundle[] = array('path' => 'conf/default.php',
                          'skel' => $skel);

        $skel = file_get_contents('./skel/conf/metadata.skel');
        $skel = str_replace(array_keys($search_replace),
                            array_values($search_replace), $skel);
        $bundle[] = array('path' => 'conf/metadata.php',
                          'skel' => $skel);

        if($conf['use_lang']){
            $skel = file_get_contents('./skel/lang/settings.skel');
            $skel = str_replace(array_keys($search_replace),
                                array_values($search_replace), $skel);
            $bundle[] = array('path' => 'lang/en/settings.php',
                              'skel' => $skel);
        }
    }

    // localization
    if($conf['use_lang']){
        $skel = file_get_contents('./skel/lang/lang.skel');
        $skel = str_replace(array_keys($search_replace),
                            array_values($search_replace), $skel);
        $bundle[] = array('path' => 'lang/en/lang.php',
                          'skel' => $skel);
    }

    // create zip file
    $zipfile = tempnam('/tmp/','dwplugwiz').'.zip';
    $zip     = new ZipArchive();
    $res     = $zip->open($zipfile, ZipArchive::CREATE);
    if($res !== true) die('failed to create zip: '.$res.' '.$zipfile.' '.$zip->status );
    foreach($bundle as $component) {
        $zip->addFromString($conf['name'].'/'.ltrim($component['path'],'/'), $component['skel']);
    }
    $zip->close();

    // send to browser
    header('Content-type: application/zip');
    header('Content-disposition: attachment; filename='.$conf['name'].'-plugin.zip');
    readfile($zipfile);
    unlink($zipfile);
}

// create the zip:
if(isset($_REQUEST['plugin_wiz_create'])) {
    create_bundle($_REQUEST['plugin']);
    exit;
}
// still here? Show the form.
?>
<html>
<head>
  <title>DokuWiki Plugin Wizard</title>
  <script rel="text/javascript" charset="utf-8" src="js/jquery.js" ></script>
  <script rel="text/javascript" charset="utf-8" src="js/script.js" ></script>
  <script type="text/javascript" src="js/plugins/autocomplete/lib/jquery.bgiframe.min.js"></script>
  <script type="text/javascript" src="js/plugins/autocomplete/lib/jquery.dimensions.js"></script>
  <script type="text/javascript" src="js/plugins/autocomplete/jquery.autocomplete.js"></script>

  <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
  <link rel="stylesheet" href="http://dev.jquery.com/view/trunk/plugins/autocomplete/jquery.autocomplete.css" type="text/css" />
</head>
<body>
  <div id="container">


    <h1>DokuWiki Plugin Wizard</h1>

    <noscript>
        <div class="nojs">Sorry, this wizard needs JavaScript to do its magic. It will not work with your current setup.</div>
    </noscript>

    <div class="intro">
        <p>This wizard generates a <a href="http://www.dokuwiki.org/devel:plugins">DokuWiki plugin</a>
           skeleton to help get started with coding.
           Before using it you should familiarize your self with how plugins in DokuWiki work
           and determine what components your plugin will need.</p>

        <p>To use it, fill in the general plugin info and add plugin components. Once you're
           done, click "create" and download your plugin skeleton.</p>

        <div class="clearer" />
    </div>

    <form action="index.php" method="post" id="ajax__plugin_wiz">

      <div id="plugin_info">
        <h2>Plugin Information</h2>

        <label for="plugin[name]" class="block">Plugin Name:</label>
        <input type="text" name="plugin[name]" value="" class="edit" id="ajax__plugin_name"/>
        <br />

        <label for="plugin[author][name]" class="block">Your Name:</label>
        <input type="text" name="plugin[author][name]" value="" class="edit validate_string"></label>
        <br />

        <label for="plugin[author][mail]" class="block">E-Mail:</label>
        <input type="text" name="plugin[author][mail]" value="" class="edit validate_string"></label>
        <br />

        <label for="plugin[desc]" class="block">Plugin Description:</label>
        <input type="text" name="plugin[desc]" value="" class="edit validate_string" />
        <br />

        <label for="plugin[url]" class="block">URL:</label>
        <input type="text" name="plugin[url]" value="" class="edit validate_url" />
        <br />

        <label for="plugin[date]" class="block">Date:</label>
        <input type="text" name="plugin[date]" value="<?php echo strftime('%Y-%m-%d', time())?>" class="edit validate_date" />
        <br />

      <label for="ajax__has_lang" class="block">Use Localization:</label>
      <input type="checkbox" name="plugin[use_lang]" id="ajax__has_lang" value="1" />
      <div id="ajax__plugin_lang"></div>
      <br />

      <label for="ajax__has_config" class="block">Use Configuration:</label>
      <input type="checkbox" name="plugin[use_config]" id="ajax__has_config" value="1" />
      <div id="ajax__plugin_config"></div>
      <br />

      </div>

      <div id="plugin_components">
        <h2>Add Plugin Components</h2>

        <label for="ajax__is_plugin_component" class="inline">Component:</label>
        <input type="checkbox" name="ajax__is_plugin_component" id="ajax__is_plugin_component" />
        <label for="ajax__plugin_component_name" class="ajax__plugin_component_name inline">Component Name:</label>
        <input type="text" name="ajax__plugin_component_name" class="ajax__plugin_component_name" value="" />

        <label for="ajax__plugin_component_type" class="inline">Type:</label>
        <select name="ajax__plugin_component_type" id="ajax__plugin_component_type">
          <option value="action">action</option>
          <option value="syntax">syntax</option>
          <option value="helper">helper</option>
          <option value="renderer">renderer</option>
          <option value="admin">admin</option>
        </select>
        <input type="button" name="ajax__btn_add_plugin_component" id="ajax__btn_add_plugin_component" value="add" />

        <div id="ajax__plugin_layout"></div>
        <br />

      </div>

      <div class="clearer" />
      <input type="submit" name="plugin_wiz_create" value="create" id="ajax__btn_create" />

    <form>

  </div>
</body>
</html>
