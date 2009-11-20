<?php
// FIXME define functions here and keep everything in one file?
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
        // FIXME DO STUFF
        print '<pre>';
        print_r($_REQUEST);
        print '</pre>';
    } else {
    ?>

    <h1>DokuWiki Plugin Wizard</h1>
    <form action="index.php" method="post" id="ajax__plugin_wiz">

      <div class="plugin_info">
      <label for="plugin[author][name]" class="block">Your Name:</label>
      <input type="text" name="plugin[author][name]" value="" class="edit ajax__edit"></label>
      <br />

      <label for="plugin[author][mail]" class="block">E-Mail:</label>
      <input type="text" name="plugin[author][mail]" value="" class="edit ajax__edit"></label>
      <br />

      <label for="plugin[name]" class="block">Plugin Name:</label>
      <input type="text" name="plugin[name]" value="" class="edit ajax__edit" id="ajax__plugin_name"/>
      <br />

      <label for=2plugin[desc]" class="block">Plugin Description:</label>
      <input type="text" name="plugin[desc]" value="" class="edit ajax__edit" />
      <br />
      </div>

      <label for="plugin[date]" class="block">Date:</label>
      <input type="text" name="plugin[date]" value="<?php echo strftime('%Y-%m-%d', time())?>" class="edit ajax__edit" />
      <br />

      <label for="plugin[license]" class="block">License:</label>
      <select name="plugin[license]">
        <option value=""></option>
        <option value="gpl">GPL</option>
      </select>
      <br />

      <label for="plugin[use_config]" class="block">Use Configuration:</label>
      <input type="checkbox" name="plugin[use_config]" id="ajax__has_config" />
      <div id="ajax__plugin_config"></div>
      <br />

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

      <input type="submit" name="plugin_wiz_reset" value="reset" id="ajax__btn_reset" />
      <input type="submit" name="plugin_wiz_create" value="create" id="ajax__btn_create" />
    <form>
    <?php } ?>

  </div>
</body>
</html>
