<?php
/*
Plugin Name: Plugin Info to CSV
Plugin URI: https://www.pixelmattic.com
Description: A simple plugin that extracts all the meta information of the plugins installed on your WordPress (like Plugin version number, Last date of update, compatibility with WordPress versions, author name and plugin homepage) and exports it as a CSV file. You can also display this information on your website using a shortcode [plugininfo]
Version: 1.0.1
Author: Pixelmattic, Abrar Ahmed
Text Domain: Plugin-Info-to-CSV
*/

if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

function create_plugins_detailed_array ()
{

$plugins_list = get_plugins();
$active_plugins_list = get_option('active_plugins');

$plugins_array = array();

$plugins_array[]  = array("Name" => "Plugin Name", "PluginURI" => "Plugin URI", "Version" => "Current Version", "Description" => "Plugin Description", "Author" => "Author", "AuthorURI" => "Author URI", "AuthorName" => "Author Name", "available_version" => "WP Available Version", "required_version" => "WP Required Version", "tested_version" => "WP Tested Version", "last_updated" => "Last Updated", "status" => "Plugin Status",);


foreach ($plugins_list as $Key => $plugins)
	{
	
	$slug_name = explode('/',$Key);
	//$plugins[slugname] = $slug_name[0];
	
	unset($plugins[Title], $plugins[TextDomain], $plugins[DomainPath], $plugins[Network]);

	$api_url = "https://api.wordpress.org/plugins/info/1.0/".$slug_name[0].".json";

	$plugin_ext_data = file_get_contents($api_url);
	$plugin_ext_data = json_decode($plugin_ext_data);

	@$plugins[available_version] = $plugin_ext_data->version;
	@$plugins[required_version] = $plugin_ext_data->requires;
	@$plugins[tested_version] = $plugin_ext_data->tested;
	@$plugins[last_updated] = $plugin_ext_data->last_updated;
	
	
	foreach ($active_plugins_list as $active_plugins)
		{
			if($active_plugins == $Key)
			{ @$plugins[status] = "InActive"; } else
			{ @$plugins[status] = "Active"; }
		}
	$plugins_array[] = $plugins;
	}

return $plugins_array;

}

function px_plugininfo()
{
$pxm_pluginsdata = create_plugins_detailed_array ();

echo "<table border='1'>";

foreach ($pxm_pluginsdata as $fields) {
	echo "<tr>";
	
	foreach ($fields as $value)
		{
			echo "<td>$value</td>";
		}
	echo "</tr>";
	}
echo "</table>";

}

function pluginsdata_options_page()
{

$pxm_pluginsdata = create_plugins_detailed_array ();

$file = plugin_dir_path( __FILE__ ); 
$file = $file."plugininfo.csv";
$fp = fopen($file, "w") or die("Unable to open file!"); 

foreach ($pxm_pluginsdata as $fields) {
    fputcsv($fp, $fields);
}

$basename = get_bloginfo('wpurl');
$basename = $basename."/wp-content/plugins/plugin-info-to-csv/plugininfo.csv";
?>
<table>
<tr>
	<td>
	<p><b>Download plugin data in CSV file</b></p>
	<p>Extracts all the meta information of the plugins installed on your WordPress (like Plugin version number, Last date of update, compatibility with WordPress versions, author name and plugin homepage) and exports it as a CSV file.</p>
	</td>
</tr>
<tr>
	<td><a href="<?php echo $basename; ?>" download="plugininfo.csv">Download</a></td>
</tr>
</table>

<?php
}

function pluginsdata_register_settings() {
   add_option( 'pluginsdata_option_name', 'This plugins data option page.');
   register_setting( 'pluginsdata_options_group', 'pluginsdata_option_name', 'pluginsdata_callback' );
}

add_action( 'admin_init', 'pluginsdata_register_settings' );
function pluginsdata_register_options_page() {
  add_plugins_page('Page Title', 'Plugin Info to CSV', 'manage_options', 'plugininfocsv', 'pluginsdata_options_page');
}

add_action('admin_menu', 'pluginsdata_register_options_page');
add_shortcode( 'plugininfo', 'px_plugininfo' );
?>