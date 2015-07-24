<?php
/*
// Change the hook this is triggered on with a bit of custom code. Copy and paste into your theme functions.php or a new plugin.
add_filter('fms_callback_trigger', 'fms_callback_trigger');
function fms_callback_trigger(){
    return 'wp_head'; //plugins_loaded, after_setup_theme, wp_loaded, wp_head
}
*/
namespace fitMySidebar;
define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');

global $fms_plugin; //what is this for again?
$fms_plugin = new fmsPlugin(); //NOTE: this is good for encapsulation

global $relevad_plugins;
if (!is_array($relevad_plugins)) {
    $relevad_plugins = array();
}
$relevad_plugins[] = array(
'url'  => admin_url('admin.php?page=fms_admin_config'),
'name' => 'Fit My Sidebar'
);

define('FMS_BASE_ROWS', 2);

//include WP_CONTENT_DIR . '/plugins/fit-my-sidebar/relevad_plugin_utils.php';
include plugin_dir_path(__FILE__) . 'relevad_plugin_utils.php';

class fmsPlugin{
       
    function __construct() {

        register_activation_hook( __FILE__, array(&$this, 'fms_activate') );
        
        // change the hook that triggers widget check
        $hook = apply_filters('fms_callback_trigger', 'wp_loaded');

        add_filter($hook,                     array(&$this, 'trigger_widget_checks'));
        add_action('in_widget_form',          array(&$this, 'hidden_widget_options'), 10, 3);
        add_filter('widget_update_callback',  array(&$this, 'update_widget_options'), 10, 3);
        add_action('wp_ajax_fms_show_widget', array(&$this, 'show_widget_options'));
        add_action('admin_footer',            array(&$this, 'load_js'));


        if ( is_admin() ) {
            // Add the code for the admin-sidebar config-page link
            add_action('admin_menu', array(&$this, 'fms_admin_actions'));
        }
    }
    
    function fms_activate() {
        $row_configs = array( //these are cutoff caps
            'short'  => 10,  //0-10
            'medium' => 30,  //11-30
            'long'   => 45,  //31-45
            'longer' => 60  //46-60
            );
            
        add_option('fms_row_configs',   $row_configs); //add_option won't overwrite
        add_option('fms_rows_per_img',  10);
        add_option('fms_px_per_row',    12);
        add_option('fms_extr_for_feat', 10);
        add_option('fms_chars_per_row', 75);
    }
    
    
    function trigger_widget_checks() {
        add_filter('sidebars_widgets', array(&$this, 'sidebars_widgets'));
    }
    
    //####### section for admin page and configuration ##########
    
    function fms_admin_actions() {
        
        relevad_plugin_add_menu_section(); //imported from relevad_plugin_utils.php
        
        //$hook = add_options_page('FitMySidebar', 'FitMySidebar', 'manage_options', 'fms_admin_config', array(&$this, 'fms_admin_config_page')); //wrapper for add_submenu_page specifically into "settings"
        $hook = add_submenu_page('relevad_plugins', 'FitMySidebar', 'FitMySidebar', 'manage_options', 'fms_admin_config', array(&$this, 'fms_admin_config_page'));
        //add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $menu_slug, $function ); // do not use __FILE__ for menu_slug
    }
    
    function fms_admin_config_page() {
        if ( !is_admin() ) {return;} //this shouldn't ever happen, but just in case.
        
        $rows_per_img  = get_option('fms_rows_per_img', 10);
        $px_per_row    = get_option('fms_px_per_row',   12);
        $extr_for_feat = get_option('fms_extr_for_feat',10);
        $chars_per_row = get_option('fms_chars_per_row',75);
        $row_configs   = get_option('fms_row_configs');
        $update_msg = "";
        
        if (isset($_POST['save'])) {
            //list($row_configs, $rows_per_img, $chars_per_row) = self::fms_update_options($row_configs, $rows_per_img, $chars_per_row);
            list($row_configs, $rows_per_img, $px_per_row, $extr_for_feat, $chars_per_row) = self::fms_update_options($row_configs, $rows_per_img, $px_per_row, $extr_for_feat, $chars_per_row);
            //save some database access
            $update_msg = "<script type='text/javascript'>setTimeout(savePop,3000);</script><span id='save_msg'>Changes Saved.</span>";
        }

        //Should the form action='/' ?
        echo <<<HEREDOC
<style type="text/css">

.fms_config input {
   width: 100%;
   font-size: 14px;
   text-align: center;
}
.fms-config-page ul {
   list-style: initial;
   margin-left: 30px;
}
h3.hndle {
    margin: 0 10px;
    padding: 2px 0;
}

input.readonly {
    background-color: #EEEEEE;
}

code {
  background-color: #DDDDDD;
  }

.tooltip span {
    display:none;
    padding:5px 10px;
    margin-left:20px;
    width:260px;
    line-height:16px;
    border-radius:5px;
}

a.tooltip {
    text-decoration:none;
    color:gray;
}

.tooltip:hover span {
    display:inline;
    position:absolute;
    color:#111;
    border:1px solid #DCA;
    background:#fffAF0;
}

#save_msg {
  border: 2px solid;
  border-radius: 6px;
  border-color: #55FF55;
  background-color:#55FF55;
  transition: 0.7s;
  }
  
table.fms_config {
    width: 350px;
}

</style>

<script type="text/javascript">
function savePop() {
    document.getElementById("save_msg").style.opacity=0;
}
</script>


<div class="fms-config-page" style="width:750px;">
    <h1>Fit My Sidebar</h1>
    <p>Fit My Sidebar lets you control which sidebar widgets show up based on the length of a specific post.</p>
    <p>The plugin estimates page length in terms of rows. Since each website is different, you'll need to fine-tune below.</p>
    <p>Choose which widgets appear on which length of a post in the Widgets section.</p>
    
    
    
    <div class="postbox-container">
      <div id="normal-sortables" class="meta-box-sortables ui-sortable">
         <div id="referrers" class="postbox">
            <h3 class="hndle"><span>Settings</span></h3>
                           
                                      
            <div class='inside'>
                <form action='' method='POST'>
                <div style="float:left;">
                    <h4>Configuration Options</h4>
                    <table class="fms_config">
                        <thead><tr><td style="width:250px;"></td><td style="width:50px;"></td><td></td>

                        </tr></thead>
                        <tbody>
                           <tr>
                                <td>
                                    Pixels Per Row
                                    <a class="tooltip" href="#">
                                        [?]
                                        <span> Ex: for font-size 12 and line-height 1.5em, set Pixels Per Row to 18 </span>
                                    </a>
        
                                </td>
                                <td>
                                    <input name="px_per_row" type="text" value="{$px_per_row}"/>
                                    </td>
                                </td>
                            </tr>

                            <tr>
                                <td>Average Characters Per Row
                                    <a class="tooltip" href="#">
                                        [?]
                                        <span>The number of characters in a row, including spaces</span>
                                    </a>
                                 </td>
                                <td><input name="chars_per_row" type="text" value="{$chars_per_row}"/></td>
                            </tr>

                            <tr>
                                <td>Average Rows Per Image
                                    <a class="tooltip" href="#">
                                        [?]
                                        <span>A default estimate for when the plugin can't grab an image's height</span>
                                    </a>
                                    </td>
                                <td><input name="rows_per_img"  type="text" value="{$rows_per_img}"/></td>
                              </tr>

                            <tr>
                                  <td>Extra Rows For Featured Image
                                    <a class="tooltip" href="#">
                                        [?]
                                        <span> The number of rows to add when a featured image is detected</span>
                                    </a>
                                    </td>
                                  <td><input name="extr_for_feat" type="text" value="{$extr_for_feat}"/></td>
                            </tr>


                        </tbody>
                    </table>
                </div>
                <div style="float:left;">
                    <h4>Classify Post Lengths (in Rows)</h4>
                    <table class="fms_config">
                        <tbody>
                            <thead><tr>
                                <td style="width:100px;"></td><td style="width:50px;"></td><td></td>
                            </tr></thead>
                            <tr>
                                <td>Short</td>
                                <td><input name="short"         type="text" value="{$row_configs['short']}"  /></td>
                            </tr>
                            <tr>
                                <td>Medium</td>
                                <td><input name="medium"        type="text" value="{$row_configs['medium']}" /></td>
                            </tr>
                            <tr>
                                <td>Long</td>
                                <td><input name="long"          type="text" value="{$row_configs['long']}"   /></td>
                            </tr>
                            <tr>
                                <td>Longer</td>
                                <td><input name="longer"        type="text" value="{$row_configs['longer']}" /></td>
                            </tr>
                            </tr>
                        </tbody>
                    </table>
                </div>
                    <br/>
                    <div class="clear"></div>
                    <input type='submit' name='save' value='Save' class='button-primary' />&nbsp;
                    {$update_msg}
                </form>
            </div>
         </div>
      </div>
    </div>
    
    <!-- UI content disabled until functionality is added

    <div class="postbox-container" style="width:900px;">
      <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div id="referrers" class="postbox">
          <h3 class="hndle"><span>Statistics</span></h3>
          <div class='inside'>
                <br/>
                    Check the number of posts of each length with last saved settings:
                    <input type='submit' name='PLACEHOLDER' value='Count' class='button-primary' />&nbsp;
                    <br/><br/>
                                        <table class="fms_config">
                        <thead>
                            <tr>
                                <th>Short</th>
                                <th>Medium</th>
                                <th>Long</th>
                                <th>Longer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input class ="readonly" name="short" type="text" readonly value="placeholder"  /></td>
                                <td><input class ="readonly" name="medium" type="text" readonly value="placeholder" /></td>
                                <td><input class ="readonly" name="long" type="text" readonly value="placeholder"   /></td>
                                <td><input class ="readonly" name="longer" type="text" readonly value="placeholder" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    
    END COMMENT -->
    <br/>   
    <p><b>Fine-tuning:</b> If you'd like to debug and see which widgets appear for which posts, add <code>[fitmysidebar-debug]</code> in a shortcode Widget or <code>&lt;?php echo do_shortcode('[fitmysidebar-debug]'); ?&gt;</code> in a PHP Code Widget. The debug information will only appear for users logged in as administrators.</p>

    
</div>

<br/>
HEREDOC;

    }
    
    //function fms_update_options($row_configs_old, $rows_per_img_old, $chars_per_row_old) {
    function fms_update_options($row_configs_old, $rows_per_img_old, $px_per_row_old, $extr_for_feat_old, $chars_per_row_old) {
        $large_number = 100000; //constant

        $row_configs_new = array();

        $row_configs_new['short']   = relevad_plugin_validate_integer($_POST['short'],  1,                          $large_number,   $row_configs_old['short']); //NOTE: any is 0, so short must be longer than any
        $row_configs_new['medium']  = relevad_plugin_validate_integer($_POST['medium'], $row_configs_new['short'] , $large_number,   $row_configs_old['medium']); //enforcing the smallest -> largest order
        $row_configs_new['long']    = relevad_plugin_validate_integer($_POST['long'],   $row_configs_new['medium'], $large_number,   $row_configs_old['long']);
        $row_configs_new['longer']  = relevad_plugin_validate_integer($_POST['longer'], $row_configs_new['long'],   $large_number,   $row_configs_old['longer']);

        $rows_per_img  = relevad_plugin_validate_integer($_POST['rows_per_img'],  3,  $large_number, $rows_per_img_old);  //3 seems like a reasonable minimum
        $px_per_row    = relevad_plugin_validate_integer($_POST['px_per_row'],    8,  $large_number, $px_per_row_old);    //8 seems like a reasonable minimum font size
        $extr_for_feat = relevad_plugin_validate_integer($_POST['extr_for_feat'], 0,  $large_number, $extr_for_feat_old); //0 means there is no featured image
        $chars_per_row = relevad_plugin_validate_integer($_POST['chars_per_row'], 30, $large_number, $chars_per_row_old); //30 seems like a reasonable minimum
        
        update_option('fms_row_configs',   $row_configs_new);
        update_option('fms_rows_per_img',  $rows_per_img);
        update_option('fms_px_per_row',    $px_per_row);
        update_option('fms_extr_for_feat', $extr_for_feat);
        update_option('fms_chars_per_row', $chars_per_row);
        //update_option('fms_thumb_img', array_key_exists('thumb-img-check', $_POST));
        
        //return array($row_configs_new, $rows_per_img, $chars_per_row);
        return array($row_configs_new, $rows_per_img, $px_per_row, $extr_for_feat, $chars_per_row);
    }
    
    //###########################################################


    function show_widget($instance) {
        //NOTE: $instance is the configs associated with this particular widget
        //$instance example:
        /*
        Array
            (
                [title] => 
                [text] => <div style="margin:10px auto; width:732px; text-align:center;">
        <div style="width:728px; height:90px; background: transparent url('<?php echo get_site_url(); ?>/wp-content/uploads/ph728x90btf.jpg')"><?php  include(DOCROOT . '/rele-ads/all-728x90.2.txt');  ?></div>

        <br />
        &copy; <?php echo date("Y") ?> Live In Health. All rights reserved.
              <a href="<?php echo get_site_url(); ?>/privacy-policy/">Privacy Policy</a>
              <a href="<?php echo get_site_url(); ?>/contact/">Contact Us</a><br /><br />
        <div>
                [filter] => 
                
                [fms_show] => 'medium'  //Added by this plugin  short/medium/long/longer/any
            )
        */
        
        $show = true; //defaults to show the widget eg fail safe.
        
        $rows_per_img  = get_option('fms_rows_per_img', 10);
        $px_per_row    = get_option('fms_px_per_row',   12);
        $extr_for_feat = get_option('fms_extr_for_feat',10);
        $chars_per_row = get_option('fms_chars_per_row',75);
        $row_configs   = get_option('fms_row_configs');     
        
        if ( is_single() && array_key_exists('fms_show', $instance) && array_key_exists($instance['fms_show'], $row_configs)) { //if the config exists, then we COULD possibly mark it as false.
            //NOTE: Any site specific sliders or extra content will need the operator to adjust the starting value

            //$post_cont       = get_the_content(); //NOTE: Must be used in a loop
            $cur_post        = get_post(); //gets current post by default
            $post_cont       = $cur_post->post_content;
            $cur_post_length = strlen($post_cont);
            
            //$cur_post_img_count = substr_count($post_cont, "<img ");
            
            //For any images that exist locally, we can get the actual image size in pixels for estimate
            $dumb_guess_images = 0 ;
            $image_height_pixels_total = 0;
            
            //reference http://codex.wordpress.org/Function_Reference/wp_upload_dir
            $upload_dir = wp_upload_dir();
            $uploads_path = substr($upload_dir['baseurl'], strlen(home_url())); //this should give us the actual uploads directory whatever it happens to be named

            $pattern = '/src=".*?(jpg|jpeg|gif|png)"/';
            $results = array();
            preg_match_all($pattern, $post_cont, $results);
            foreach ($results[0] as $img) { 
                //NOTE: we're not even gonna try for hotlinked urls at this time
                $startpos = strpos($img, $uploads_path);
                if ($startpos !== false) { //then we're in wordpress
                  
                  $imgurl = ABSPATH . substr($img, $startpos, -1); //get filesystem path
                  if (file_exists($imgurl)) {
                     $size = getimagesize($imgurl);
                     $image_height_pixels_total += $size[1];
                  }
                  else
                     $dumb_guess_images += 1;
                }
            }
            $image_bonus_rows = ( $dumb_guess_images * $rows_per_img ) + ceil($image_height_pixels_total / $px_per_row );
            if (has_post_thumbnail()) {$image_bonus_rows += $extr_for_feat;}
            
            //$cur_post_rows = ceil($cur_post_length / $chars_per_row) + ($cur_post_img_count * $rows_per_img) + FMS_BASE_ROWS;
            $cur_post_rows = ceil($cur_post_length / $chars_per_row) + $image_bonus_rows + FMS_BASE_ROWS;

            if ( $cur_post_rows < $row_configs[$instance['fms_show']] ) { //if the #rows we have is less than the row config for the class this widget was placed in, hide it
                $show = false;
            }
        }
        
        return $show;
    } //end of show_widget()
    
    
    
    //called by trigger_widget_checks() as a filter
    function sidebars_widgets($sidebars) { //NOTE: This is NOT for going through the admin page, only for the user front end
        if ( is_admin() ) {
            return $sidebars; //always return the sidebars un-altered on admin pages
        }

        global $wp_registered_widgets;

        foreach ( $sidebars as $s => $sidebar ) {
            if ( $s == 'wp_inactive_widgets' || strpos($s, 'orphaned_widgets') === 0 || empty($sidebar) ) { //skip the inactive widgets sidebar
                continue;
            }

            foreach ( $sidebar as $w => $widget ) {
                // $widget is the id of the widget
                if ( !isset($wp_registered_widgets[$widget]) ) {
                    continue;
                }

                if ( isset($this->checked[$widget]) ) { //how can the same widget id show up more than once? I guess if the same sidebar is shown multiple times on the same page?
                    $show = $this->checked[$widget];
                } else {
                    //guess, sidebars only contains the widget ids, the rest of the data comes from wp_options with a specific option per widget. So, what is inside of wp_registered_widgets?
                    $opts = $wp_registered_widgets[$widget];
                    $id_base = is_array($opts['callback']) ? $opts['callback'][0]->id_base : $opts['callback'];

                    if ( !$id_base ) {
                        continue;
                    }

                    $instance = get_option('widget_' . $id_base); //wp options is where all of the content for widgets live

                    if ( !$instance || !is_array($instance) ) { //if the wp options for that widget did not exist
                        continue;
                    }
                    
                    if ( isset($instance['_multiwidget']) && $instance['_multiwidget'] ) { //Question: What does multiwidget mean? Looks like all widgets are always "multi-widget"
                        $number = $opts['params'][0]['number'];  //NOTE: this number is the array number for this widgets contents within the wp_options section
                        if ( !isset($instance[$number]) ) { //if wp_options for that widget doesn't contain that particular array index, skip it
                            continue;
                        }

                        $instance = $instance[$number];
                        unset($number);
                    }

                    unset($opts);

                    $show = self::show_widget($instance);

                    //$this->checked[$widget] = $show ? true : false;

                    $this->checked[$widget] = $show; //basically the checked array is a form of caching
                }

                if ( !$show ) {
                    unset($sidebars[$s][$w]); //if the sidebar is not to be shown, remove it from the list
                }

                unset($widget);
            } //end sidebar -> widget forloop
            unset($sidebar);
        } //end sidebars -> sidebar forloop

        return $sidebars;
    } //end sidebars_widgets()


    //These are defaults that need to be placed into any new widget added to a sidebar. called on hook in_widget_form
    function hidden_widget_options($widget, $return, $instance) {
        if ( $_POST && isset($_POST['id_base']) && $_POST['id_base'] == $widget->id_base ) {
            // widget was just saved so it's open
            self::show_hide_widget_options($widget, $return, $instance);
            return;
        }

        $instance['fms_show'] = isset($instance['fms_show']) ? $instance['fms_show'] : 'any'; //any by default

        echo "<div class='fms_opts'>"; //this is necessary to get all the information into the widget pannel itself. when its added to a sidebar
        echo "<input type='hidden' name='" . $widget->get_field_name('fms_show') . "' id='" . $widget->get_field_id('fms_show') . "'value='" . $instance['fms_show'] . "' />";
        echo "</div>";
    }
    
    // called by wp_ajax_fms_show_widget hook, which is an auto generated hook by an ajax call from within dw_show_opts() javascript function
    function show_widget_options() {
        $instance = htmlspecialchars_decode(nl2br(stripslashes($_POST['opts'])));
        $instance = json_decode($instance, true);
        $this->id_base = $_POST['id_base'];
        $this->number = $_POST['widget_number'];

        $new_instance = array();
        $prefix = 'widget-'. $this->id_base .'['. $this->number .'][';
        foreach ( $instance as $k => $v ) {
            $n = str_replace( array( $prefix, ']'), '', $v['name']);
            $new_instance[$n] = $v['value'];
        }

        self::show_hide_widget_options($this, '', $new_instance);
        die();
    }
    
    //This function is to actually display the configuration options in the widget pannel on admin pages. (but only ever happens when you make one open/expand
    function show_hide_widget_options($widget, $return, $instance) {  //NOTE: return is unused
        
        $instance['fms_show'] = isset($instance['fms_show']) ? $instance['fms_show'] : 'any'; //any by default
        
        $row_configs = get_option('fms_row_configs');

        echo "<div style='background-color:#EEE; padding:5px; margin:5px 0px;'><p style='margin:0px;'> <label for='".$widget->get_field_id('fms_show')."'>Show widget only if content length >= </label><br/>";
        echo "<select name='".$widget->get_field_name('fms_show')."' id='".$widget->get_field_id('fms_show')."' class='widefat'>";
        echo "<option value='any' " . selected('any', $instance['fms_show']) . ">any length (0 rows or more)</option>";
        foreach ( $row_configs as $conf => $l) {
            echo "<option value='{$conf}' " . selected($conf, $instance['fms_show']) . ">{$conf} ({$l} rows or more)</option>";
        }
        echo "</select> </p></div>";
    }
    
    function update_widget_options($instance, $new_instance, $old_instance) {

        $instance['fms_show'] = ( isset($new_instance['fms_show']) && $new_instance['fms_show'] ) ? $new_instance['fms_show'] : 'any'; //any by default

        return $instance;
    }
    
    //two helper functions for lots of other places in the code
    function get_field_name($field_name) {
        return 'widget-' . $this->id_base . '[' . $this->number . '][' . $field_name . ']';
    }

    function get_field_id($field_name) {
        return 'widget-' . $this->id_base . '-' . $this->number . '-' . $field_name;
    }
        
        
    function load_js() {
        global $pagenow;

        if ( $pagenow != 'widgets.php' ) {
            //only load the js on the widgets page
            return;
        }

        $a_url = admin_url( "admin-ajax.php" );    
        echo <<<HEREDOC
<script type="text/javascript">
/*<![CDATA[*/
jQuery(document.body).bind('click.widgets-toggle', fms_show_opts);

function fms_show_opts(e) {
    var target = jQuery(e.target);
    var widget = target.closest('div.widget');
        var inside = widget.children('.widget-inside');
        var opts = inside.find('.fms_opts');
        if(opts.length == 0) {
            return;
        }
        
        inside.find('.spinner').show();
    
    jQuery.ajax({
                type:'POST',url:'{$a_url}',
                data:{
                    'action':'fms_show_widget',
                    'opts':JSON.stringify(opts.children('input').serializeArray()),
                    'id_base':inside.find('input.id_base').val(),
                    'widget_number':(inside.find('input.multi_number').val() == '') ? inside.find('input.widget_number').val() : inside.find('input.multi_number').val()
                },
                success:function(html){ opts.replaceWith(html); inside.find('.spinner').hide(); }
        });
}
/*]]>*/
</script>
HEREDOC;

    } //end of load_js()

} //end class definition

add_shortcode('fitmysidebar-debug', NS.'fitmysidebar_debug');

function fitmysidebar_debug($atts) { //atts should be empty anyways
    
    if (! current_user_can('manage_options') || !is_single()) { return ""; } //if we're not an admin or on a single page, hide this debug entirely
    
    $output = <<<HEREDOC
    <div id="draggable" style="z-index:10; background-color:white; border:1px solid black; position:absolute; top:40px; left:10px; padding:5px;">
    <h5>Click and drag me around</h5>
    <h2>Current config from admin page</h2>
HEREDOC;
    
    $rows_per_img  = get_option('fms_rows_per_img', 10);
    $output .= "rows per image: {$rows_per_img}<br/>";
    $px_per_row = get_option('fms_px_per_row',      12);
    $output .= "pixels per row: {$px_per_row}<br/>";
    $extr_for_feat = get_option('fms_extr_for_feat',10);
    $output .= "extra rows for featured image: {$extr_for_feat}<br/>"; //NOTE: we also use this value for and random hotlinked images that are found
    $chars_per_row = get_option('fms_chars_per_row',75);
    $output .= "chars per row: {$chars_per_row}<br/>";
    $row_configs   = get_option('fms_row_configs');
    
    $output .= "baseline rows: " . FMS_BASE_ROWS . "<br/>";

    //$post_cont       = get_the_content(); //this works for some reason though
    $cur_post        = get_post();
    $post_cont       = $cur_post->post_content;
    
    $cur_post_length = strlen($post_cont);
    $output .= "content length: {$cur_post_length}<br/>";
    
    //$cur_post_img_count = substr_count($post_cont, "<img ");
    
    //----------------experimental--------------
    $dumb_guess_images = 0 ;
    $image_height_pixels_total = 0;
    
    //reference http://codex.wordpress.org/Function_Reference/wp_upload_dir
    $upload_dir = wp_upload_dir();
    $uploads_path = substr($upload_dir['baseurl'], strlen(home_url())); //this should give us the actual uploads directory whatever it happens to be named

    $pattern = '/src=".*?(jpg|jpeg|gif|png)"/';
    $results = array();
    preg_match_all($pattern, $post_cont, $results);
    foreach ($results[0] as $img) { 
        //NOTE: we're not even gonna try for hotlinked urls at this time
        $startpos = strpos($img, $uploads_path);
        if ($startpos !== false) { //then we're in wordpress
          
          $imgurl = ABSPATH . substr($img, $startpos, -1); //get filesystem path
          if (file_exists($imgurl)) {
             $size = getimagesize($imgurl);
             $image_height_pixels_total += $size[1];
          }
          else
             $dumb_guess_images += 1;
        }
    }
    $output .= "total images found: ".count($results[0])."<br/>";
    $output .= "non local images (no size guess): {$dumb_guess_images}<br/>";
    
    //NOTE: Cant get the dimensions of the thumbnail without being linked directly into the theme, so we'll just have the opperator estimate it
    //$image_bonus_rows = ($dumb_guess_images * $rows_per_img) + ($image_height_pixels_total / 12 ); //best guess font size 12px
    //////----------------------/////
    
    //$output .= "current post image count: {$cur_post_img_count}<br/>";
    $image_bonus_rows = ( $dumb_guess_images * $rows_per_img ) + ceil($image_height_pixels_total / $px_per_row );
    if (has_post_thumbnail()) {$image_bonus_rows += $extr_for_feat;}
    
    $output .= "total image pixels: {$image_height_pixels_total}<br/>";
    $output .= "estimate image rows: {$image_bonus_rows}<br/>";
    
    //$cur_post_rows = ceil($cur_post_length / $chars_per_row) + ($cur_post_img_count * $rows_per_img) + FMS_BASE_ROWS;
    $cur_post_rows = ceil($cur_post_length / $chars_per_row) + $image_bonus_rows + FMS_BASE_ROWS;
    $output .= "current row estimate: {$cur_post_rows}<br/>";
    //$output .= "estimate image rows old: ".($cur_post_img_count * $rows_per_img)."<br/>";
    

    $max_class = "";
    foreach ($row_configs as $k => $v) {
        $output .= "<div class='fixed'>{$k}</div> : <div class='fixed'>{$v}</div>";
        if ($cur_post_rows >= $v) { //if the current post has enough lines to fit into the current classification
            $output .= "&nbsp;<div class='green fixed'>SHOWN</div>"; 
            $max_class = "{$k}"; //this assumes they are in ascending order
        }
        else {
            $output .= "&nbsp;<div class='red fixed'>HIDDEN</div>";
        }
        
        $output .= "<br/>";
    }
        
    wp_enqueue_script('jquery-ui-draggable');
    //this is for dynamic user initated calculations to try to fiddle around with the plugin config on the fly
    $tmp = FMS_BASE_ROWS; //for use in the string
    $output .= <<<HEREDOC
<br/><br/>
<h2>Based on below configs</h2>
Current Pg Row Estimate: <span id="row_est">{$cur_post_rows}</span><br/>
Maximum Class Activated: <span id="actv_class">{$max_class}</span><br/>

<table id="fms_config">
    <tbody>
        <tr>
            <td class="line-eq">Short</td>  <td><input id="d_short"         type="text" value="{$row_configs['short']}"  /></td>
        </tr>
        <tr>
            <td class="line-eq">Medium</td> <td><input id="d_medium"        type="text" value="{$row_configs['medium']}" /></td>
        </tr>
        <tr>
            <td class="line-eq">Long</td>   <td><input id="d_long"          type="text" value="{$row_configs['long']}"   /></td>
        </tr>
        <tr>
            <td class="line-eq">Longer</td> <td><input id="d_longer"        type="text" value="{$row_configs['longer']}" /></td>
        </tr>
        <tr>
            <td>Chars per Row</td>          <td><input id="d_chars_per_row" type="text" value="{$chars_per_row}"         /></td>
        </tr>
        <tr>
            <td>Pixels per Row</td>         <td><input id="d_px_per_row"    type="text" value="{$px_per_row}"            /></td>
        </tr>
        <tr>
            <td>Extra for Featured</td>     <td><input id="d_extr_for_feat" type="text" value="{$extr_for_feat}"         /></td>
        </tr>
        <tr>
            <td>Rows per Image</td>         <td><input id="d_rows_per_img"  type="text" value="{$rows_per_img}"          /></td>
        </tr>
    </tbody>
</table>
<input type='button' id='update' value='Update' onclick="update_debug();" /> 
<br/><sup>NOTE: does not save config only updates debug data</sup>

</div>

<style type='text/css'>
.green {
color: green;
font-weight: bold;
}
.fixed {
display: inline-block;
width: 55px;
}
.red {
color: red;
font-weight: bold;
}
td.line-eq {
background-color: #ECC;
}
</style>

<script type='text/javascript'>

jQuery(function() {
    jQuery( "#draggable" ).draggable();
});

function update_debug() {
    var base = {$tmp};

    var short  = document.getElementById('d_short').value;
    var medium = document.getElementById('d_medium').value;
    var long   = document.getElementById('d_long').value;
    var longer = document.getElementById('d_longer').value;
    var rows_per_img   = Number(document.getElementById('d_rows_per_img').value);
    var extr_for_feat  = Number(document.getElementById('d_extr_for_feat').value);
    var px_per_row     = Number(document.getElementById('d_px_per_row').value);
    var chars_per_row  = Number(document.getElementById('d_chars_per_row').value);
    
    var dumb_img_count = {$dumb_guess_images}; //these are constants
    var total_img_px   = {$image_height_pixels_total};
    var char_count     = {$cur_post_length};
    
    var cur_post_rows = Math.ceil(char_count / chars_per_row) + extr_for_feat + ( dumb_img_count * rows_per_img ) + Math.ceil(total_img_px / px_per_row ) + base;
    var actv_class = "all";
    if      (cur_post_rows >= longer) { actv_class = 'longer'; }
    else if (cur_post_rows >= long)   { actv_class = 'long';   }
    else if (cur_post_rows >= medium) { actv_class = 'medium'; }
    else if (cur_post_rows >= short)  { actv_class = 'short';  }
    
    document.getElementById('row_est').innerHTML    = cur_post_rows;
    document.getElementById('actv_class').innerHTML = actv_class;
}
</script>

HEREDOC;
    
    return $output;

}



?>
