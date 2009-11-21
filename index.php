<?php
error_reporting(E_ALL ^ E_NOTICE);

/**
 * Saves $content to $file.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return bool true on success
 */
function io_saveFile($file, $content){
    $fileexists = @file_exists($file);
    io_makeFileDir($file);
    $fh = @fopen($file, 'wb');
    if(!$fh){ return false; }
    fwrite($fh, $content);
    fclose($fh);
    chmod($file, 0666);
    return true;
}

/**
 * Creates a directory hierachy.
 *
 * @link    http://www.php.net/manual/en/function.mkdir.php
 * @author  <saint@corenova.com>
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_mkdir_p($target){
    if (@is_dir($target) || empty($target)) return 1; // best case check first
    if (@file_exists($target) && !is_dir($target)) return 0;

    //recursion
    if (io_mkdir_p(substr($target,0,strrpos($target, '/')))) {
        $ret = @mkdir($target,0775); // crawl back up & create dir tree
        if($ret && 0775) chmod($target, 0775);
        return $ret;
    }
    return 0;
}

/**
 * Create the directory needed for the given file
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function io_makeFileDir($file){
    global $conf;

    $dir = dirname($file);
    if(!@is_dir($dir)){
        io_mkdir_p($dir);
    }
}

/**
 * Creates a unique temporary directory and returns
 * its path.
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function io_mktmpdir() {
    $base = './tmp';
    $dir  = md5(uniqid(mt_rand(), true));
    $tmpdir = $base.'/'.$dir;

    if(io_mkdir_p($tmpdir)) {
        return($tmpdir);
    } else {
        return false;
    }
}

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

    $tmpd = io_mktmpdir();

    /**
      foreach component read in the relevant sekeleton replace al replacemetns
      and add it to the bundle array

        [/relative/path] => file-contents

      then create the bundle by writing all files zip it and done
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

            $component['path'] = ($name) ?  $tmpd . '/' . $conf['name'] . '/' . $type . '/' . $name . '.php' : $tmpd . '/' . $conf['name'] . '/' . $type . '.php';
            $search_replace['@@PLUGIN_COMPONENT_NAME@@'] = 'plugin_' . $type . '_' . $plugin;
            $search_replace['@@INFO_TXT_PATH@@'] = ($name) ? '../plugin.info.txt' : 'plugin.info.txt';

            $skel = file_get_contents('./skel/' . $type . '.skel');
            $skel = str_replace(array_keys($search_replace), array_values($search_replace), $skel);

            $component['skel'] = $skel;

            array_push($bundle, $component);
        }
    }

    // plugin.info.txt
    $skel = file_get_contents('./skel/info.skel');
    $skel = str_replace(array_keys($search_replace),
                        array_values($search_replace), $skel);
    $bundle[] = array('path' => $tmpd.'/'.$conf['name'].'/plugin.info.skel',
                      'skel' => $skel);

    // write output FIXME replace by zip action later
    foreach($bundle as $component) {
        io_saveFile($component['path'], $component['skel']);
    }
}

?>
<html>
<head>
  <title>DokuWiki Plugin Builder</title>
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

    <?php
    if(isset($_REQUEST['plugin_wiz_create'])) {
        create_bundle($_REQUEST['plugin']);
        print 'stuff done';
    } else {
    ?>

    <h1>DokuWiki Plugin Wizard</h1>
    <form action="index.php" method="post" id="ajax__plugin_wiz">

      <div id="plugin_info">
        <h2>Plugin Information</h2>

        <label for="plugin[author][name]" class="block">Your Name:</label>
        <input type="text" name="plugin[author][name]" value="" class="edit ajax__edit"></label>
        <br />

        <label for="plugin[author][mail]" class="block">E-Mail:</label>
        <input type="text" name="plugin[author][mail]" value="" class="edit ajax__edit"></label>
        <br />

        <label for="plugin[name]" class="block">Plugin Name:</label>
        <input type="text" name="plugin[name]" value="" class="edit ajax__edit" id="ajax__plugin_name"/>
        <br />

        <label for="plugin[desc]" class="block">Plugin Description:</label>
        <input type="text" name="plugin[desc]" value="" class="edit ajax__edit" />
        <br />

        <label for="plugin[url]" class="block">URL:</label>
        <input type="text" name="plugin[url]" value="" class="edit ajax__edit" />
        <br />

        <label for="plugin[date]" class="block">Date:</label>
        <input type="text" name="plugin[date]" value="<?php echo strftime('%Y-%m-%d', time())?>" class="edit ajax__edit" />
        <br />

      <label for="plugin[use_lang]" class="block">Use Localization:</label>
      <input type="checkbox" name="plugin[use_lang]" id="ajax__has_lang" />
      <div id="ajax__plugin_lang"></div>
      <br />

      <label for="plugin[use_config]" class="block">Use Configuration:</label>
      <input type="checkbox" name="plugin[use_config]" id="ajax__has_config" />
      <div id="ajax__plugin_config"></div>
      <br />

      </div>

      <div id="plugin_components">
        <h2>Add Plugin Types</h2>

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

      <input type="submit" name="plugin_wiz_reset" value="reset" id="ajax__btn_reset" />
      <input type="submit" name="plugin_wiz_create" value="create" id="ajax__btn_create" />

    <form>
    <?php } ?>

  </div>
</body>
</html>
