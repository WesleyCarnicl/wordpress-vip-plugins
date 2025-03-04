<?php
require_once(LFAPPS__PLUGIN_PATH . "/libs/php/LFAPPS_JWT.php");

$network_name = get_option('livefyre_apps-livefyre_domain_name', 'livefyre.com');
$delegate_auth_url = 'https://admin.' . $network_name;
$site_id = get_option('livefyre_apps-livefyre_site_id');
$article_id = get_the_ID();
$site_key = get_option('livefyre_apps-livefyre_site_key');

$collection_meta = array(
    'title'=>  apply_filters('livefyre_collection_title', get_the_title(get_the_ID())),
    'url'=> apply_filters('livefyre_collection_url', get_permalink(get_the_ID())),
    'articleId'=> apply_filters('livefyre_article_id', get_the_ID()),
    'type'=>'sidenotes'
);
$jwtString = LFAPPS_JWT::encode($collection_meta, $site_key);
        
$conv_config = array(
    'siteId'=>$site_id,
    'articleId'=>$article_id,
    'collectionMeta'=>$jwtString,
    'network'=>$network_name,
    'selectors'=>get_option('livefyre_apps-livefyre_sidenotes_selectors'),
);
$strings = apply_filters( 'livefyre_custom_sidenotes_strings', null );
$conv_config_str = Livefyre_Apps::json_encode_wrap($conv_config);
?>
<script type="text/javascript">
Livefyre.require([<?php echo LFAPPS_Sidenotes::get_package_reference(); ?>], function (Sidenotes) {
    load_livefyre_auth();
    var convConfigSidenotes = <?php echo $conv_config_str; ?>;
    convConfigSidenotes['network'] = <?php echo Livefyre_Apps::json_encode_wrap($network_name); ?>;
    <?php echo isset( $strings ) ? "convConfigSidenotes['strings'] = " . json_encode($strings) . ';' : ''; ?>
    if(typeof(livefyreSidenotesConfig) !== 'undefined') {
        convConfigSidenotes = Livefyre.LFAPPS.lfExtend(convConfigSidenotes, livefyreSidenotesConfig);
    }
    var sidenotesApp = new Sidenotes(convConfigSidenotes);
    var sidenotesListeners = Livefyre.LFAPPS.getAppEventListeners('sidenotes');
    if(sidenotesListeners.length > 0) {
        for(var i=0; i<sidenotesListeners.length; i++) {
            var sidenotesListener = sidenotesListeners[i];
            sidenotesApp.once(sidenotesListener.eventName, sidenotesListener.callback);
        }
    }
});
</script>
